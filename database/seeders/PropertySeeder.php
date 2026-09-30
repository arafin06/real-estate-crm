<?php

namespace Database\Seeders;

use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Seeder;

class PropertySeeder extends Seeder
{
    public function run(): void
    {
        $sarah = User::where('email', 'sarah@recrm.demo')->first();
        $marcus = User::where('email', 'marcus@recrm.demo')->first();

        $properties = [
            // Sarah's listings
            [
                'user_id' => $sarah->id,
                'title' => 'Modern Craftsman in South Congress',
                'description' => 'Stunning 3-bedroom craftsman bungalow in the heart of South Congress. Completely renovated with quartz countertops, hardwood floors, and a chef\'s kitchen. Private backyard with deck — perfect for entertaining. Walking distance to top-rated restaurants and shops.',
                'type' => 'residential',
                'listing_type' => 'sale',
                'price' => 589000,
                'address' => '2847 S Congress Ave',
                'city' => 'Austin',
                'state' => 'TX',
                'zip' => '78704',
                'bedrooms' => 3,
                'bathrooms' => 2,
                'area_sqft' => 1850,
                'status' => 'active',
            ],
            [
                'user_id' => $sarah->id,
                'title' => 'Luxury High-Rise Condo — Downtown Austin',
                'description' => 'Floor-to-ceiling windows with panoramic views of Lady Bird Lake. This 2-bed, 2-bath corner unit features a wrap-around balcony, premium finishes, and access to resort-style amenities including rooftop pool and concierge service.',
                'type' => 'residential',
                'listing_type' => 'sale',
                'price' => 875000,
                'address' => '360 Nueces St',
                'city' => 'Austin',
                'state' => 'TX',
                'zip' => '78701',
                'bedrooms' => 2,
                'bathrooms' => 2,
                'area_sqft' => 1420,
                'status' => 'under_contract',
            ],
            [
                'user_id' => $sarah->id,
                'title' => 'East Austin Duplex — Investment Opportunity',
                'description' => 'Prime East Austin duplex generating strong rental income. Both units fully leased with long-term tenants. Each unit has 2 beds, 1 bath, private entrance, and dedicated parking. Cap rate 6.2%. Major upside potential in one of Austin\'s hottest corridors.',
                'type' => 'residential',
                'listing_type' => 'sale',
                'price' => 725000,
                'address' => '1203 E 6th St',
                'city' => 'Austin',
                'state' => 'TX',
                'zip' => '78702',
                'bedrooms' => 4,
                'bathrooms' => 2,
                'area_sqft' => 2200,
                'status' => 'active',
            ],
            [
                'user_id' => $sarah->id,
                'title' => 'Retail Space — The Domain',
                'description' => 'Premium retail corner unit in The Domain, Austin\'s premier mixed-use shopping district. High foot traffic, excellent visibility, and neighboring national brands. Ideal for boutique, restaurant, or service business.',
                'type' => 'commercial',
                'listing_type' => 'rent',
                'price' => 8500,
                'address' => '11601 Domain Dr',
                'city' => 'Austin',
                'state' => 'TX',
                'zip' => '78758',
                'bedrooms' => null,
                'bathrooms' => 2,
                'area_sqft' => 2800,
                'status' => 'active',
            ],
            [
                'user_id' => $sarah->id,
                'title' => 'Mueller Community Home — Sold',
                'description' => 'Beautiful 4-bedroom home in the sought-after Mueller neighborhood. Open floor plan, energy-efficient construction, solar panels, and a 2-car garage. Close to Aldrich Street and Mueller Lake Park.',
                'type' => 'residential',
                'listing_type' => 'sale',
                'price' => 648000,
                'address' => '4800 Berkman Dr',
                'city' => 'Austin',
                'state' => 'TX',
                'zip' => '78723',
                'bedrooms' => 4,
                'bathrooms' => 3,
                'area_sqft' => 2650,
                'status' => 'sold',
            ],

            // Marcus's listings
            [
                'user_id' => $marcus->id,
                'title' => 'Germantown Victorian — Nashville',
                'description' => 'Meticulously restored 1890s Victorian in Germantown, Nashville\'s most walkable neighborhood. Original hardwood floors, exposed brick, updated kitchen and bathrooms. Covered front porch and private garden courtyard.',
                'type' => 'residential',
                'listing_type' => 'sale',
                'price' => 695000,
                'address' => '1142 4th Ave N',
                'city' => 'Nashville',
                'state' => 'TN',
                'zip' => '37208',
                'bedrooms' => 3,
                'bathrooms' => 2,
                'area_sqft' => 2100,
                'status' => 'active',
            ],
            [
                'user_id' => $marcus->id,
                'title' => 'Green Hills Luxury Family Home',
                'description' => 'Exceptional 5-bedroom estate in prestigious Green Hills. Chef\'s kitchen with Wolf appliances, primary suite with spa bath, dedicated home office, and a finished basement with media room. 3-car garage and professionally landscaped half-acre lot.',
                'type' => 'residential',
                'listing_type' => 'sale',
                'price' => 1250000,
                'address' => '4521 Lone Oak Rd',
                'city' => 'Nashville',
                'state' => 'TN',
                'zip' => '37215',
                'bedrooms' => 5,
                'bathrooms' => 4,
                'area_sqft' => 4800,
                'status' => 'active',
            ],
            [
                'user_id' => $marcus->id,
                'title' => 'The Gulch — Luxury Apartment',
                'description' => 'Premium 1-bedroom apartment in The Gulch with skyline views. Stainless appliances, quartz counters, hardwood floors. Building amenities include rooftop terrace, fitness center, and secure parking. Utilities included.',
                'type' => 'residential',
                'listing_type' => 'rent',
                'price' => 2650,
                'address' => '600 12th Ave S',
                'city' => 'Nashville',
                'state' => 'TN',
                'zip' => '37203',
                'bedrooms' => 1,
                'bathrooms' => 1,
                'area_sqft' => 820,
                'status' => 'active',
            ],
            [
                'user_id' => $marcus->id,
                'title' => 'Class A Office Space — Midtown',
                'description' => 'Turnkey Class A office suite in Midtown Nashville. Fully furnished, fiber internet included. Private conference rooms, reception area, and kitchen. Flexible lease terms. Ideal for growing professional services firms.',
                'type' => 'commercial',
                'listing_type' => 'rent',
                'price' => 6200,
                'address' => '2525 West End Ave',
                'city' => 'Nashville',
                'state' => 'TN',
                'zip' => '37203',
                'bedrooms' => null,
                'bathrooms' => 3,
                'area_sqft' => 3400,
                'status' => 'under_contract',
            ],
        ];

        foreach ($properties as $data) {
            Property::firstOrCreate(
                [
                    'user_id' => $data['user_id'],
                    'title' => $data['title'],
                ],
                $data
            );
        }
    }
}
