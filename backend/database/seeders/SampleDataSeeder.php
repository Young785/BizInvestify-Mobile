<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Product;
use App\Models\Business;
use Illuminate\Support\Facades\Hash;

class SampleDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a test seller user (or get existing one)
        $seller = User::firstOrCreate(
            ['email' => 'seller@example.com'],
            [
                'name' => 'John Seller',
                'first_name' => 'John',
                'last_name' => 'Seller',
                'password' => Hash::make('password'),
                'phone' => '+1234567890',
                'role' => 'seller',
                'email_verified_at' => now(),
                'phone_verified_at' => now(),
            ]
        );

        // Clear existing products and businesses for this seller
        Product::where('seller_id', $seller->id)->delete();
        Business::where('seller_id', $seller->id)->delete();

        // Create sample products
        $products = [
            [
                'title' => 'Premium Smartphone',
                'description' => 'Latest smartphone with advanced features and high-quality camera',
                'price' => 999.99,
                'category' => 'Electronics',
                'status' => 'active',
                'featured' => true,
                'views_count' => 150,
                'seller_id' => $seller->id,
            ],
            [
                'title' => 'Organic Coffee Beans',
                'description' => 'Premium organic coffee beans from high-altitude farms',
                'price' => 24.99,
                'category' => 'Food & Beverage',
                'status' => 'active',
                'featured' => false,
                'views_count' => 75,
                'seller_id' => $seller->id,
            ],
            [
                'title' => 'Fitness Tracker',
                'description' => 'Advanced fitness tracker with heart rate monitoring',
                'price' => 199.99,
                'category' => 'Technology',
                'status' => 'active',
                'featured' => true,
                'views_count' => 200,
                'seller_id' => $seller->id,
            ],
            [
                'title' => 'Handmade Jewelry',
                'description' => 'Beautiful handmade jewelry crafted with precious metals',
                'price' => 89.99,
                'category' => 'Fashion',
                'status' => 'active',
                'featured' => false,
                'views_count' => 45,
                'seller_id' => $seller->id,
            ],
        ];

        foreach ($products as $productData) {
            Product::create($productData);
        }

        // Create sample businesses
        $businesses = [
            [
                'name' => 'TechStart Solutions',
                'description' => 'Innovative software solutions for modern businesses',
                'industry' => 'Technology',
                'location' => 'San Francisco, CA',
                'valuation' => 5000000,
                'funding_goal' => 1000000,
                'status' => 'active',
                'featured' => true,
                'views_count' => 300,
                'seller_id' => $seller->id,
            ],
            [
                'name' => 'Green Energy Co',
                'description' => 'Renewable energy solutions for sustainable future',
                'industry' => 'Energy',
                'location' => 'Austin, TX',
                'valuation' => 3000000,
                'funding_goal' => 750000,
                'status' => 'active',
                'featured' => false,
                'views_count' => 120,
                'seller_id' => $seller->id,
            ],
            [
                'name' => 'HealthTech Innovations',
                'description' => 'Advanced healthcare technology for better patient outcomes',
                'industry' => 'Healthcare',
                'location' => 'Boston, MA',
                'valuation' => 8000000,
                'funding_goal' => 2000000,
                'status' => 'active',
                'featured' => true,
                'views_count' => 450,
                'seller_id' => $seller->id,
            ],
        ];

        foreach ($businesses as $businessData) {
            Business::create($businessData);
        }

        $this->command->info('Sample data created successfully!');
    }
} 