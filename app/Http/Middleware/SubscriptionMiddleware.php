<?php

namespace App\Http\Middleware;

use App\Models\Property;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SubscriptionMiddleware
{
    /** Listings a free account may own. */
    private const FREE_LISTING_LIMIT = 5;

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Admins are not metered; only free accounts are.
        if (! $user || $user->isSuperAdmin() || $user->subscription_status !== 'free') {
            return $next($request);
        }

        $owned = Property::where('user_id', $user->id)->count();

        if ($owned >= self::FREE_LISTING_LIMIT) {
            return response()->json([
                'message' => 'Upgrade to Pro',
                'upgrade' => true,
                'limit' => self::FREE_LISTING_LIMIT,
                'current' => $owned,
            ], 402);
        }

        return $next($request);
    }
}
