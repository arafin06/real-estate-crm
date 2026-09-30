<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $demo = [
            [
                'email' => 'admin@recrm.demo',
                'name' => 'Alex Morgan',
                'role' => 'super_admin',
                'phone' => '5125550100',
                'subscription_status' => 'pro',
            ],
            [
                'email' => 'sarah@recrm.demo',
                'name' => 'Sarah Johnson',
                'role' => 'agent',
                'phone' => '5125550101',
                'subscription_status' => 'pro',
            ],
            [
                'email' => 'marcus@recrm.demo',
                'name' => 'Marcus Williams',
                'role' => 'agent',
                'phone' => '6155550102',
                'subscription_status' => 'free',
            ],
            [
                'email' => 'client@recrm.demo',
                'name' => 'Jennifer Davis',
                'role' => 'client',
                'phone' => '5125550103',
                'subscription_status' => 'free',
            ],
        ];

        foreach ($demo as $user) {
            User::firstOrCreate(
                ['email' => $user['email']],
                [...$user, 'password' => Hash::make('Demo1234!')]
            );
        }

        // Carried over from the original DatabaseSeeder so that
        // `migrate:fresh --seed` does not silently drop the accounts used
        // for manual testing. Safe to delete if you only want demo data.
        $legacy = [
            ['email' => 'admin@test.com', 'name' => 'Super Admin', 'role' => 'super_admin'],
            ['email' => 'agent@test.com', 'name' => 'Test Agent', 'role' => 'agent'],
            ['email' => 'client@test.com', 'name' => 'Test Client', 'role' => 'client'],
        ];

        foreach ($legacy as $user) {
            User::firstOrCreate(
                ['email' => $user['email']],
                [...$user, 'password' => Hash::make('password123')]
            );
        }
    }
}
