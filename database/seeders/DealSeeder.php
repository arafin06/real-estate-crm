<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Deal;
use App\Models\DealActivity;
use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Seeder;

class DealSeeder extends Seeder
{
    public function run(): void
    {
        $sarah = User::where('email', 'sarah@recrm.demo')->first();
        $marcus = User::where('email', 'marcus@recrm.demo')->first();

        // Sarah's clients and properties
        $chen = Client::where('email', 'rchen@email.com')->first();
        $okonkwo = Client::where('email', 'dokonkwo@ventures.com')->first();
        $nguyen = Client::where('email', 'pnguyen@example.com')->first();
        $foster = Client::where('email', 'jfoster@example.com')->first();

        $congressProp = Property::where('title', 'Modern Craftsman in South Congress')->first();
        $duplexProp = Property::where('title', 'East Austin Duplex — Investment Opportunity')->first();
        $muellerProp = Property::where('title', 'Mueller Community Home — Sold')->first();

        // Marcus's clients and properties
        $whitfield = Client::where('email', 'twhitfield@example.com')->first();
        $hayes = Client::where('email', 'bhayes@nashventures.com')->first();
        $park = Client::where('email', 'kpark@example.com')->first();

        $victorianProp = Property::where('title', 'Germantown Victorian — Nashville')->first();
        $greenHillsProp = Property::where('title', 'Green Hills Luxury Family Home')->first();
        $officeProp = Property::where('title', 'Class A Office Space — Midtown')->first();

        $deals = [
            // Sarah's deals
            [
                'user_id' => $sarah->id,
                'client_id' => $chen->id,
                'property_id' => $congressProp?->id,
                'title' => 'Chen Family — S Congress Purchase',
                'stage' => 'under_contract',
                'deal_value' => 572000,
                'commission_rate' => 3.00,
                'commission_amount' => 17160,
                'expected_close_date' => now()->addDays(18)->toDateString(),
                'notes' => 'Offer accepted at $572K. Inspection completed with minor issues — seller agreed to $3K credit. Awaiting lender final approval.',
                'activities' => [
                    ['action' => 'created', 'description' => 'Deal created in stage: Lead', 'days_ago' => 21],
                    ['action' => 'stage_changed', 'description' => 'Stage changed from Lead to Prospect', 'days_ago' => 18],
                    ['action' => 'stage_changed', 'description' => 'Stage changed from Prospect to Showing', 'days_ago' => 14],
                    ['action' => 'stage_changed', 'description' => 'Stage changed from Showing to Offer Made', 'days_ago' => 7],
                    ['action' => 'stage_changed', 'description' => 'Stage changed from Offer Made to Under Contract', 'days_ago' => 4],
                ],
            ],
            [
                'user_id' => $sarah->id,
                'client_id' => $okonkwo->id,
                'property_id' => $duplexProp?->id,
                'title' => 'Okonkwo — E 6th St Duplex Investment',
                'stage' => 'prospect',
                'deal_value' => 725000,
                'commission_rate' => 2.50,
                'commission_amount' => 18125,
                'expected_close_date' => now()->addDays(45)->toDateString(),
                'notes' => 'Cash buyer. Reviewing financials. Strong fit with his investment criteria.',
                'activities' => [
                    ['action' => 'created', 'description' => 'Deal created in stage: Lead', 'days_ago' => 10],
                    ['action' => 'stage_changed', 'description' => 'Stage changed from Lead to Prospect', 'days_ago' => 5],
                ],
            ],
            [
                'user_id' => $sarah->id,
                'client_id' => $nguyen->id,
                'property_id' => null,
                'title' => 'Nguyen — Hyde Park Listing',
                'stage' => 'showing',
                'deal_value' => 485000,
                'commission_rate' => 3.00,
                'commission_amount' => 14550,
                'expected_close_date' => now()->addDays(55)->toDateString(),
                'notes' => 'Listing agreement signed. Photography scheduled. Pre-market interest from 2 buyers.',
                'activities' => [
                    ['action' => 'created', 'description' => 'Deal created in stage: Lead', 'days_ago' => 30],
                    ['action' => 'stage_changed', 'description' => 'Stage changed from Lead to Prospect', 'days_ago' => 22],
                    ['action' => 'stage_changed', 'description' => 'Stage changed from Prospect to Showing', 'days_ago' => 8],
                ],
            ],
            [
                'user_id' => $sarah->id,
                'client_id' => $foster->id,
                'property_id' => null,
                'title' => 'Foster Family — Austin Relocation',
                'stage' => 'lead',
                'deal_value' => 720000,
                'commission_rate' => 3.00,
                'commission_amount' => 21600,
                'expected_close_date' => now()->addDays(75)->toDateString(),
                'notes' => 'Chicago relocation. Dual transaction — sell and buy. Coordinating timeline carefully.',
                'activities' => [
                    ['action' => 'created', 'description' => 'Deal created in stage: Lead', 'days_ago' => 5],
                ],
            ],
            [
                'user_id' => $sarah->id,
                'client_id' => $chen->id,
                'property_id' => $muellerProp?->id,
                'title' => 'Mueller Home — Closed Deal',
                'stage' => 'closed',
                'deal_value' => 648000,
                'commission_rate' => 3.00,
                'commission_amount' => 19440,
                'expected_close_date' => now()->subDays(30)->toDateString(),
                'closed_at' => now()->subDays(30),
                'notes' => 'Smooth closing. Clients were thrilled. Great referral potential.',
                'activities' => [
                    ['action' => 'created', 'description' => 'Deal created in stage: Lead', 'days_ago' => 90],
                    ['action' => 'stage_changed', 'description' => 'Stage changed from Lead to Prospect', 'days_ago' => 80],
                    ['action' => 'stage_changed', 'description' => 'Stage changed from Prospect to Showing', 'days_ago' => 65],
                    ['action' => 'stage_changed', 'description' => 'Stage changed from Showing to Offer Made', 'days_ago' => 55],
                    ['action' => 'stage_changed', 'description' => 'Stage changed from Offer Made to Under Contract', 'days_ago' => 50],
                    ['action' => 'stage_changed', 'description' => 'Stage changed from Under Contract to Closed', 'days_ago' => 30],
                ],
            ],

            // Marcus's deals
            [
                'user_id' => $marcus->id,
                'client_id' => $whitfield->id,
                'property_id' => $greenHillsProp?->id,
                'title' => 'Whitfield — Green Hills Estate',
                'stage' => 'offer_made',
                'deal_value' => 1220000,
                'commission_rate' => 2.75,
                'commission_amount' => 33550,
                'expected_close_date' => now()->addDays(35)->toDateString(),
                'notes' => 'Offer submitted at $1.22M. Seller countered at $1.235M. Negotiating.',
                'activities' => [
                    ['action' => 'created', 'description' => 'Deal created in stage: Lead', 'days_ago' => 25],
                    ['action' => 'stage_changed', 'description' => 'Stage changed from Lead to Prospect', 'days_ago' => 20],
                    ['action' => 'stage_changed', 'description' => 'Stage changed from Prospect to Showing', 'days_ago' => 14],
                    ['action' => 'stage_changed', 'description' => 'Stage changed from Showing to Offer Made', 'days_ago' => 3],
                ],
            ],
            [
                'user_id' => $marcus->id,
                'client_id' => $hayes->id,
                'property_id' => $officeProp?->id,
                'title' => 'Hayes Ventures — Midtown Office',
                'stage' => 'showing',
                'deal_value' => 2800000,
                'commission_rate' => 2.00,
                'commission_amount' => 56000,
                'expected_close_date' => now()->addDays(90)->toDateString(),
                'notes' => 'Commercial investor reviewing all financials. Requested tenant lease abstracts.',
                'activities' => [
                    ['action' => 'created', 'description' => 'Deal created in stage: Lead', 'days_ago' => 15],
                    ['action' => 'stage_changed', 'description' => 'Stage changed from Lead to Prospect', 'days_ago' => 10],
                    ['action' => 'stage_changed', 'description' => 'Stage changed from Prospect to Showing', 'days_ago' => 4],
                ],
            ],
            [
                'user_id' => $marcus->id,
                'client_id' => $park->id,
                'property_id' => $victorianProp?->id,
                'title' => 'Park — Germantown Victorian',
                'stage' => 'prospect',
                'deal_value' => 695000,
                'commission_rate' => 3.00,
                'commission_amount' => 20850,
                'expected_close_date' => now()->addDays(50)->toDateString(),
                'notes' => 'Young couple, very motivated. Inspection report ordered. Expecting good results.',
                'activities' => [
                    ['action' => 'created', 'description' => 'Deal created in stage: Lead', 'days_ago' => 12],
                    ['action' => 'stage_changed', 'description' => 'Stage changed from Lead to Prospect', 'days_ago' => 6],
                ],
            ],
        ];

        foreach ($deals as $dealData) {
            $activities = $dealData['activities'];
            unset($dealData['activities']);

            $deal = Deal::firstOrCreate(
                [
                    'user_id' => $dealData['user_id'],
                    'title' => $dealData['title'],
                ],
                $dealData
            );

            if ($deal->activities()->count() === 0) {
                foreach ($activities as $act) {
                    $timestamp = now()->subDays($act['days_ago']);

                    $activity = new DealActivity([
                        'deal_id' => $deal->id,
                        'user_id' => $dealData['user_id'],
                        'action' => $act['action'],
                        'description' => $act['description'],
                    ]);

                    // Timestamps are not mass assignable, so set them directly
                    // or every activity lands on "now" and the timeline is flat.
                    $activity->created_at = $timestamp;
                    $activity->updated_at = $timestamp;
                    $activity->save();
                }
            }
        }
    }
}
