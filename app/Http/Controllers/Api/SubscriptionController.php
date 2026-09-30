<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;
use Stripe\StripeClient;
use Stripe\Webhook;
use UnexpectedValueException;

class SubscriptionController extends Controller
{
    private function stripe(): StripeClient
    {
        $secret = config('services.stripe.secret');

        abort_if(blank($secret), 503, 'Stripe is not configured.');

        return new StripeClient($secret);
    }

    /**
     * Create (or reuse) the Stripe customer for this user, then open a
     * Checkout Session for the Pro plan.
     */
    public function createCheckoutSession(Request $request)
    {
        $user = $request->user();
        $stripe = $this->stripe();
        $priceId = config('services.stripe.price_id');

        abort_if(blank($priceId), 503, 'Stripe price is not configured.');

        if (blank($user->stripe_customer_id)) {
            $customer = $stripe->customers->create([
                'email' => $user->email,
                'name' => $user->name,
                'metadata' => ['user_id' => (string) $user->id],
            ]);

            $user->forceFill(['stripe_customer_id' => $customer->id])->save();
        }

        $session = $stripe->checkout->sessions->create([
            'mode' => 'subscription',
            'customer' => $user->stripe_customer_id,
            'line_items' => [[
                'price' => $priceId,
                'quantity' => 1,
            ]],
            'success_url' => config('services.stripe.success_url'),
            'cancel_url' => config('services.stripe.cancel_url'),
            // client_reference_id survives the redirect, so the webhook can
            // still identify the user if the customer lookup ever misses.
            'client_reference_id' => (string) $user->id,
            'metadata' => ['user_id' => (string) $user->id],
        ]);

        return response()->json(['url' => $session->url]);
    }

    /**
     * Open the Stripe-hosted billing portal so the user can update their
     * card or cancel.
     */
    public function customerPortal(Request $request)
    {
        $user = $request->user();

        abort_if(
            blank($user->stripe_customer_id),
            422,
            'No billing account yet. Start a subscription first.'
        );

        $session = $this->stripe()->billingPortal->sessions->create([
            'customer' => $user->stripe_customer_id,
            'return_url' => config('services.stripe.success_url'),
        ]);

        return response()->json(['url' => $session->url]);
    }

    /**
     * Stripe webhook receiver. Public route — the signature is the auth.
     */
    public function webhook(Request $request)
    {
        $secret = config('services.stripe.webhook_secret');

        abort_if(blank($secret), 503, 'Stripe webhook is not configured.');

        try {
            // Signature verification needs the raw body, not the parsed array.
            $event = Webhook::constructEvent(
                $request->getContent(),
                $request->header('Stripe-Signature', ''),
                $secret
            );
        } catch (SignatureVerificationException|UnexpectedValueException $e) {
            Log::warning('Stripe webhook rejected: '.$e->getMessage());

            return response()->json(['message' => 'Invalid signature.'], 400);
        }

        $object = $event->data->object;

        match ($event->type) {
            'checkout.session.completed' => $this->handleCheckoutCompleted($object),
            'customer.subscription.deleted' => $this->setStatusByCustomer($object->customer ?? null, 'free', true),
            'invoice.payment_failed' => $this->setStatusByCustomer($object->customer ?? null, 'inactive'),
            'invoice.payment_succeeded' => $this->setStatusByCustomer($object->customer ?? null, 'pro'),
            // Anything else is acknowledged so Stripe stops retrying.
            default => null,
        };

        return response()->json(['received' => true]);
    }

    private function handleCheckoutCompleted(object $session): void
    {
        $user = $this->resolveUser(
            $session->customer ?? null,
            $session->client_reference_id ?? null
        );

        if (! $user) {
            Log::warning('Stripe checkout.session.completed for unknown user', [
                'customer' => $session->customer ?? null,
            ]);

            return;
        }

        $user->forceFill([
            'subscription_status' => 'pro',
            'stripe_customer_id' => $session->customer ?? $user->stripe_customer_id,
            'stripe_subscription_id' => $session->subscription ?? null,
        ])->save();
    }

    private function setStatusByCustomer(?string $customerId, string $status, bool $clearSubscription = false): void
    {
        $user = $this->resolveUser($customerId);

        if (! $user) {
            Log::warning('Stripe webhook for unknown customer', ['customer' => $customerId]);

            return;
        }

        $attributes = ['subscription_status' => $status];

        if ($clearSubscription) {
            $attributes['stripe_subscription_id'] = null;
        }

        $user->forceFill($attributes)->save();
    }

    private function resolveUser(?string $customerId, ?string $userId = null): ?User
    {
        if (filled($customerId)) {
            $user = User::where('stripe_customer_id', $customerId)->first();

            if ($user) {
                return $user;
            }
        }

        return filled($userId) ? User::find($userId) : null;
    }
}
