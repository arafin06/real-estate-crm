<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Note;
use App\Models\User;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    public function run(): void
    {
        $sarah = User::where('email', 'sarah@recrm.demo')->first();
        $marcus = User::where('email', 'marcus@recrm.demo')->first();

        $clients = [
            // Sarah's clients
            [
                'user_id' => $sarah->id,
                'name' => 'Robert & Linda Chen',
                'email' => 'rchen@email.com',
                'type' => 'buyer',
                'source' => 'Referral',
                'budget_min' => 550000,
                'budget_max' => 650000,
                'preferred_contact' => 'email',
                'timeline' => 'asap',
                'pre_approval_amount' => 600000,
                'phones' => [
                    ['type' => 'mobile', 'phone' => '5125554201'],
                    ['type' => 'home', 'phone' => '5125554211'],
                ],
                'notes' => [
                    'Initial consultation completed. Looking for 3-4BR in 78704 or 78702. Budget $550K-$650K. Must have home office space. Pre-approved with Chase.',
                    'Showed 3 properties on Saturday. Loved the S Congress Craftsman — said the kitchen was exactly what they wanted. Scheduling a second showing.',
                    'Second showing done. They want to make an offer on 2847 S Congress. Drafting offer at $572,000 with 3% down. Closing in 45 days.',
                ],
            ],
            [
                'user_id' => $sarah->id,
                'name' => 'David Okonkwo',
                'email' => 'dokonkwo@ventures.com',
                'type' => 'buyer',
                'source' => 'Website',
                'budget_min' => 700000,
                'budget_max' => 900000,
                'preferred_contact' => 'phone',
                'timeline' => '3_6_months',
                'phones' => [
                    ['type' => 'mobile', 'phone' => '5125554202'],
                    ['type' => 'work', 'phone' => '5125554212'],
                ],
                'notes' => [
                    'Investor looking for multi-family properties in East Austin. Budget $700K-$900K. Cash buyer — no financing contingency. Wants properties with strong rental history.',
                    'Sent analysis on the E 6th St duplex. Cap rate aligns with his targets. He\'s reviewing and will respond by Friday.',
                ],
            ],
            [
                'user_id' => $sarah->id,
                'name' => 'Patricia Nguyen',
                'email' => 'pnguyen@example.com',
                'type' => 'seller',
                'source' => 'Open House',
                'phones' => [
                    ['type' => 'mobile', 'phone' => '5125554203'],
                ],
                'notes' => [
                    'Met at the Mueller open house. Has a home in Hyde Park she\'s looking to list in Q1. Wants to discuss pricing strategy and timeline.',
                    'CMA completed. Recommended listing at $485K. She\'s interviewing two other agents. Follow up next week.',
                    'She signed the listing agreement. Targeting March 1 for going live. Scheduling professional photography for Feb 20.',
                ],
            ],
            [
                'user_id' => $sarah->id,
                'name' => 'James & Emily Foster',
                'email' => 'jfoster@example.com',
                'type' => 'both',
                'source' => 'Zillow',
                'budget_min' => 650000,
                'budget_max' => 750000,
                'preferred_contact' => 'email',
                'timeline' => '1_3_months',
                'pre_approval_amount' => 750000,
                'phones' => [
                    ['type' => 'mobile', 'phone' => '5125554204'],
                    ['type' => 'other', 'phone' => '3125554214'],
                ],
                'notes' => [
                    'Relocating from Chicago. Need to sell their condo and buy a family home in Austin. Timeline is 60-90 days. Kids start school in August.',
                    'Pre-approval in hand for $750K. Interested in Mueller, Tarrytown, and Barton Hills neighborhoods.',
                ],
            ],
            [
                'user_id' => $sarah->id,
                'name' => 'Monica Delgado',
                'email' => 'mdelgado@example.com',
                'type' => 'buyer',
                'source' => 'Realtor.com',
                'phones' => [
                    ['type' => 'mobile', 'phone' => '5125554205'],
                ],
                'notes' => [
                    'First-time buyer. Looking for a condo under $400K. Very interested in the downtown area. FHA loan — needs low HOA fees.',
                ],
            ],

            // Marcus's clients
            [
                'user_id' => $marcus->id,
                'name' => 'Thomas & Grace Whitfield',
                'email' => 'twhitfield@example.com',
                'type' => 'buyer',
                'source' => 'Referral',
                'budget_min' => 1100000,
                'budget_max' => 1400000,
                'preferred_contact' => 'phone',
                'timeline' => 'asap',
                'pre_approval_amount' => 1300000,
                'phones' => [
                    ['type' => 'mobile', 'phone' => '6155554301'],
                    ['type' => 'home', 'phone' => '6155554311'],
                ],
                'notes' => [
                    'Referral from the Hendersons. Looking for a luxury home in Green Hills or Belle Meade. Budget $1.1M-$1.4M. Picky about school district — must be Hillsboro High zone.',
                    'Toured the Lone Oak Rd property. Thomas loved the basement media room. Grace concerned about the kitchen layout. Discussing potential renovation budget.',
                    'They\'ve decided to make an offer. $1.22M, conventional financing, 30-day close. Sending offer tonight.',
                ],
            ],
            [
                'user_id' => $marcus->id,
                'name' => 'Brandon Hayes',
                'email' => 'bhayes@nashventures.com',
                'type' => 'buyer',
                'source' => 'Cold Call',
                'phones' => [
                    ['type' => 'work', 'phone' => '6155554302'],
                ],
                'notes' => [
                    'Commercial investor expanding portfolio in Nashville. Looking for office or retail in Midtown or The Gulch. Budget $2M-$5M.',
                    'Sent overview of the West End Ave office listing. He liked the tenant profile. Requesting financials.',
                ],
            ],
            [
                'user_id' => $marcus->id,
                'name' => 'Aisha Thompson',
                'email' => 'athompson@example.com',
                'type' => 'seller',
                'source' => 'Social Media',
                'phones' => [
                    ['type' => 'mobile', 'phone' => '6155554303'],
                ],
                'notes' => [
                    'Reached out via Instagram. Has a 2BR in The Nations she wants to sell. Moving to Denver for work. Needs to close in 60 days.',
                    'Listed at $389K. Already have 4 showings scheduled this weekend. Expect multiple offers.',
                ],
            ],
            [
                'user_id' => $marcus->id,
                'name' => 'Kevin & Stephanie Park',
                'email' => 'kpark@example.com',
                'type' => 'buyer',
                'source' => 'Open House',
                'budget_min' => 650000,
                'budget_max' => 750000,
                'preferred_contact' => 'email',
                'timeline' => '1_3_months',
                'pre_approval_amount' => 750000,
                'phones' => [
                    ['type' => 'mobile', 'phone' => '6155554304'],
                    ['type' => 'mobile', 'phone' => '6155554314'],
                ],
                'notes' => [
                    'Met at the Germantown Victorian open house. Young couple, first home. Pre-approved for $750K. Love historic neighborhoods.',
                    'Very interested in the Victorian. Asking about the roof age and HVAC. Ordered inspection report.',
                ],
            ],
        ];

        foreach ($clients as $clientData) {
            $notes = $clientData['notes'];
            $phones = $clientData['phones'];
            unset($clientData['notes'], $clientData['phones']);

            $client = Client::firstOrCreate(
                [
                    'user_id' => $clientData['user_id'],
                    'email' => $clientData['email'],
                ],
                $clientData
            );

            // firstOrCreate leaves existing rows alone, so clients seeded before
            // the buyer fields existed would never get them. Backfill only the
            // ones still empty — never overwrite a value someone has edited.
            $backfill = array_filter(
                array_intersect_key($clientData, array_flip([
                    'budget_min', 'budget_max', 'preferred_contact',
                    'timeline', 'pre_approval_amount',
                ])),
                fn ($key) => $client->{$key} === null,
                ARRAY_FILTER_USE_KEY
            );

            if ($backfill) {
                $client->update($backfill);
            }

            // Phone numbers live in client_phones, not on the client row.
            if ($client->phones()->count() === 0) {
                foreach ($phones as $phone) {
                    $client->phones()->create($phone);
                }
            }

            // Add notes only if none exist
            if ($client->notes()->count() === 0) {
                foreach ($notes as $i => $content) {
                    $timestamp = now()
                        ->subDays(count($notes) - $i)
                        ->subHours(rand(1, 8));

                    $note = new Note([
                        'notable_type' => Client::class,
                        'notable_id' => $client->id,
                        'user_id' => $clientData['user_id'],
                        'content' => $content,
                    ]);

                    // Timestamps are not mass assignable, so set them directly
                    // or the backdated history collapses onto "now".
                    $note->created_at = $timestamp;
                    $note->updated_at = $timestamp;
                    $note->save();
                }
            }
        }
    }
}
