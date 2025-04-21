<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            [
                'name' => 'Smartphone X',
                'description' => 'Latest smartphone with high-end features and 128GB storage.',
                'price' => 699.99,
                'image_url' => 'https://via.placeholder.com/600x400?text=Smartphone+X',
                'stock' => 20
            ],
            [
                'name' => 'Laptop Pro',
                'description' => 'Powerful laptop with 16GB RAM and 512GB SSD for professional use.',
                'price' => 1299.99,
                'image_url' => 'https://via.placeholder.com/600x400?text=Laptop+Pro',
                'stock' => 15
            ],
            [
                'name' => 'Wireless Headphones',
                'description' => 'Noise-canceling wireless headphones with 20 hours battery life.',
                'price' => 149.99,
                'image_url' => 'https://via.placeholder.com/600x400?text=Wireless+Headphones',
                'stock' => 30
            ],
            [
                'name' => 'Smart Watch',
                'description' => 'Fitness tracker with heart rate monitor and sleep tracking.',
                'price' => 199.99,
                'image_url' => 'https://via.placeholder.com/600x400?text=Smart+Watch',
                'stock' => 25
            ],
            [
                'name' => 'Bluetooth Speaker',
                'description' => 'Portable waterproof speaker with deep bass and 12 hours playback.',
                'price' => 79.99,
                'image_url' => 'https://via.placeholder.com/600x400?text=Bluetooth+Speaker',
                'stock' => 40
            ],
            [
                'name' => 'Tablet Mini',
                'description' => 'Compact tablet with 10-inch display, perfect for reading and browsing.',
                'price' => 329.99,
                'image_url' => 'https://via.placeholder.com/600x400?text=Tablet+Mini',
                'stock' => 18
            ],
            [
                'name' => 'Gaming Console',
                'description' => 'Next-gen gaming console with 1TB storage and 4K gaming support.',
                'price' => 499.99,
                'image_url' => 'https://via.placeholder.com/600x400?text=Gaming+Console',
                'stock' => 10
            ],
            [
                'name' => 'Wireless Earbuds',
                'description' => 'True wireless earbuds with touch controls and charging case.',
                'price' => 89.99,
                'image_url' => 'https://via.placeholder.com/600x400?text=Wireless+Earbuds',
                'stock' => 35
            ]
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
} 