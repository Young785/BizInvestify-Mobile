<?php

namespace Database\Seeders;

use App\Models\ProductCategory;
use Illuminate\Database\Seeder;

class ProductCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Electronics', 'description' => 'Electronics related products and services'],
            ['name' => 'Fashion', 'description' => 'Fashion related products and services'],
            ['name' => 'Food & Beverage', 'description' => 'Food & Beverage related products and services'],
            ['name' => 'Technology', 'description' => 'Technology related products and services'],
        ];

        foreach ($categories as $category) {
            ProductCategory::firstOrCreate(
                ['slug' => ProductCategory::generateUniqueSlug($category['name'])],
                [
                    'name' => $category['name'],
                    'description' => $category['description'],
                    'status' => 'active',
                    'seller_id' => null,
                ]
            );
        }
    }
}
