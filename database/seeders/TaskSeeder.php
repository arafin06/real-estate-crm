<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Deal;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    public function run(): void
    {
        $sarah = User::where('email', 'sarah@recrm.demo')->first();
        $marcus = User::where('email', 'marcus@recrm.demo')->first();

        $chenDeal = Deal::where('title', 'Chen Family — S Congress Purchase')->first();
        $okonkwoDeal = Deal::where('title', 'Okonkwo — E 6th St Duplex Investment')->first();
        $whitDeal = Deal::where('title', 'Whitfield — Green Hills Estate')->first();
        $hayesDeal = Deal::where('title', 'Hayes Ventures — Midtown Office')->first();

        $chen = Client::where('email', 'rchen@email.com')->first();
        $nguyen = Client::where('email', 'pnguyen@example.com')->first();
        $whitfield = Client::where('email', 'twhitfield@example.com')->first();
        $park = Client::where('email', 'kpark@example.com')->first();

        $tasks = [
            // Sarah's tasks
            [
                'user_id' => $sarah->id,
                'deal_id' => $chenDeal?->id,
                'client_id' => $chen?->id,
                'title' => 'Coordinate final walkthrough with Chen family',
                'description' => 'Schedule 24-48 hrs before closing. Confirm all agreed repairs completed and appliances operational.',
                'due_date' => now()->addDays(2)->toDateString(),
                'priority' => 'high',
                'status' => 'in_progress',
                'subtasks' => [
                    ['title' => 'Confirm seller access with listing agent', 'due_date' => now()->addDays(1)->toDateString(), 'priority' => 'high', 'status' => 'complete'],
                    ['title' => 'Send walkthrough checklist to clients', 'due_date' => now()->addDays(1)->toDateString(), 'priority' => 'medium', 'status' => 'complete'],
                    ['title' => 'Verify $3K repair credit on closing docs', 'due_date' => now()->addDays(2)->toDateString(), 'priority' => 'high', 'status' => 'incomplete'],
                ],
            ],
            [
                'user_id' => $sarah->id,
                'deal_id' => $chenDeal?->id,
                'client_id' => null,
                'title' => 'Review closing disclosure with title company',
                'description' => 'Confirm all numbers match the original purchase contract. Flag any discrepancies immediately.',
                'due_date' => now()->toDateString(),
                'priority' => 'urgent',
                'status' => 'incomplete',
                'subtasks' => [],
            ],
            [
                'user_id' => $sarah->id,
                'deal_id' => null,
                'client_id' => $nguyen?->id,
                'title' => 'Schedule professional photography — Hyde Park listing',
                'description' => 'Book Golden Hour Photography. Need at least 25 photos + aerial drone shots. Property must be staged before shoot.',
                'due_date' => now()->addDays(5)->toDateString(),
                'priority' => 'medium',
                'status' => 'incomplete',
                'subtasks' => [
                    ['title' => 'Confirm stager availability Feb 18-19', 'due_date' => now()->addDays(3)->toDateString(), 'priority' => 'medium', 'status' => 'incomplete'],
                    ['title' => 'Book Golden Hour Photography', 'due_date' => now()->addDays(4)->toDateString(), 'priority' => 'high', 'status' => 'incomplete'],
                    ['title' => 'Create MLS listing draft', 'due_date' => now()->addDays(6)->toDateString(), 'priority' => 'medium', 'status' => 'incomplete'],
                ],
            ],
            [
                'user_id' => $sarah->id,
                'deal_id' => $okonkwoDeal?->id,
                'client_id' => null,
                'title' => 'Send duplex financials to Okonkwo',
                'description' => 'Compile rent rolls, expense reports, and lease agreements for last 12 months. Include cap rate analysis.',
                'due_date' => now()->subDays(1)->toDateString(), // overdue
                'priority' => 'high',
                'status' => 'incomplete',
                'subtasks' => [],
            ],
            [
                'user_id' => $sarah->id,
                'deal_id' => null,
                'client_id' => null,
                'title' => 'Weekly pipeline review',
                'description' => 'Review all active deals, update stages, log any changes. Send weekly update to broker.',
                'due_date' => now()->addDays(3)->toDateString(),
                'priority' => 'medium',
                'status' => 'incomplete',
                'is_recurring' => true,
                'recurrence_type' => 'weekly',
                'recurrence_interval' => 1,
                'subtasks' => [],
            ],
            [
                'user_id' => $sarah->id,
                'deal_id' => null,
                'client_id' => null,
                'title' => 'Follow up with Foster family on pre-approval',
                'description' => 'Check in on their mortgage pre-approval status. If ready, schedule initial property tours for next week.',
                'due_date' => now()->addDays(7)->toDateString(),
                'priority' => 'low',
                'status' => 'incomplete',
                'subtasks' => [],
            ],
            [
                'user_id' => $sarah->id,
                'deal_id' => $chenDeal?->id,
                'client_id' => $chen?->id,
                'title' => 'Prepare closing gift for Chen family',
                'description' => 'Order from Austin Artisan Baskets. Include local coffee, baked goods, and a personalized housewarming card.',
                'due_date' => now()->addDays(10)->toDateString(),
                'priority' => 'low',
                'status' => 'complete',
                'subtasks' => [],
            ],

            // Marcus's tasks
            [
                'user_id' => $marcus->id,
                'deal_id' => $whitDeal?->id,
                'client_id' => $whitfield?->id,
                'title' => 'Counter-offer response — Whitfield negotiation',
                'description' => 'Seller countered at $1.235M. Discuss with Thomas and Grace. Their ceiling is $1.23M. Prepare counter at $1.225M.',
                'due_date' => now()->toDateString(),
                'priority' => 'urgent',
                'status' => 'in_progress',
                'subtasks' => [
                    ['title' => 'Call Thomas Whitfield to discuss counter', 'due_date' => now()->toDateString(), 'priority' => 'high', 'status' => 'complete'],
                    ['title' => 'Prepare counter-offer at $1.225M', 'due_date' => now()->toDateString(), 'priority' => 'high', 'status' => 'incomplete'],
                    ['title' => 'Submit counter to listing agent by 5pm', 'due_date' => now()->toDateString(), 'priority' => 'urgent', 'status' => 'incomplete'],
                ],
            ],
            [
                'user_id' => $marcus->id,
                'deal_id' => $hayesDeal?->id,
                'client_id' => null,
                'title' => 'Compile commercial lease abstracts for Hayes',
                'description' => 'Pull all current tenant leases from the property management company. Create summary table with tenant, term, rent, and options.',
                'due_date' => now()->addDays(3)->toDateString(),
                'priority' => 'high',
                'status' => 'incomplete',
                'subtasks' => [],
            ],
            [
                'user_id' => $marcus->id,
                'deal_id' => null,
                'client_id' => $park?->id,
                'title' => 'Share Victorian inspection report with Parks',
                'description' => 'Inspector flagged minor issues with the rear gutters and one bathroom faucet. Walk Kevin and Stephanie through the report and recommend a $1,500 repair credit request.',
                'due_date' => now()->addDays(1)->toDateString(),
                'priority' => 'medium',
                'status' => 'incomplete',
                'subtasks' => [],
            ],
            [
                'user_id' => $marcus->id,
                'deal_id' => null,
                'client_id' => null,
                'title' => 'Post new listings to social media',
                'description' => 'Share new and updated listings on Instagram and LinkedIn. Include professional photos and key highlights. Tag neighborhood accounts.',
                'due_date' => now()->addDays(2)->toDateString(),
                'priority' => 'low',
                'status' => 'incomplete',
                'is_recurring' => true,
                'recurrence_type' => 'weekly',
                'recurrence_interval' => 1,
                'subtasks' => [],
            ],
            [
                'user_id' => $marcus->id,
                'deal_id' => null,
                'client_id' => $whitfield?->id,
                'title' => 'Send comparable sales report to Whitfields',
                'description' => 'Pull last 90 days comps in Green Hills for homes 4,500+ sqft. Supports our negotiating position.',
                'due_date' => now()->subDays(2)->toDateString(), // overdue
                'priority' => 'high',
                'status' => 'incomplete',
                'subtasks' => [],
            ],
        ];

        foreach ($tasks as $taskData) {
            $subtasks = $taskData['subtasks'];
            $isRecurring = $taskData['is_recurring'] ?? false;
            $recurrenceType = $taskData['recurrence_type'] ?? null;
            $recurrenceInt = $taskData['recurrence_interval'] ?? 1;

            unset(
                $taskData['subtasks'],
                $taskData['is_recurring'],
                $taskData['recurrence_type'],
                $taskData['recurrence_interval']
            );

            $task = Task::firstOrCreate(
                [
                    'user_id' => $taskData['user_id'],
                    'title' => $taskData['title'],
                ],
                array_merge($taskData, [
                    'is_recurring' => $isRecurring,
                    'recurrence_type' => $recurrenceType,
                    'recurrence_interval' => $recurrenceInt,
                ])
            );

            if ($task->subtasks()->count() === 0) {
                foreach ($subtasks as $sub) {
                    Task::create([
                        'user_id' => $taskData['user_id'],
                        'parent_id' => $task->id,
                        // Subtasks inherit the parent's links, matching what
                        // TaskController::storeSubtask() does for real ones.
                        'client_id' => $task->client_id,
                        'deal_id' => $task->deal_id,
                        'title' => $sub['title'],
                        'due_date' => $sub['due_date'],
                        'priority' => $sub['priority'],
                        'status' => $sub['status'],
                    ]);
                }
            }
        }
    }
}
