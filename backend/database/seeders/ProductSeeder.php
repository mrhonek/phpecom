<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing products
        DB::table('products')->truncate();
        
        $products = [
            [
                'name' => 'Smartphone XS Pro',
                'description' => 'Latest flagship smartphone with advanced camera system and all-day battery life.',
                'price' => 899.99,
                'image_url' => 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=800',
                'stock' => 25,
            ],
            [
                'name' => 'Ultra HD Smart TV 55"',
                'description' => 'Crystal clear 4K display with smart features and voice control.',
                'price' => 649.99,
                'image_url' => 'https://images.unsplash.com/photo-1593359677879-a4bb92f829d1?w=800',
                'stock' => 15,
            ],
            [
                'name' => 'Wireless Noise-Cancelling Headphones',
                'description' => 'Premium over-ear headphones with active noise cancellation and 30-hour battery life.',
                'price' => 249.99,
                'image_url' => 'https://images.unsplash.com/photo-1546435770-a3e736e9ae14?w=800',
                'stock' => 40,
            ],
            [
                'name' => 'Professional Digital Camera',
                'description' => 'High-resolution DSLR camera with interchangeable lenses and 4K video recording.',
                'price' => 1299.99,
                'image_url' => 'https://images.unsplash.com/photo-1516035069371-29a1b244cc32?w=800',
                'stock' => 10,
            ],
            [
                'name' => 'Portable Bluetooth Speaker',
                'description' => 'Waterproof, rugged speaker with 360° sound and 20-hour battery life.',
                'price' => 129.99,
                'image_url' => 'https://images.unsplash.com/photo-1589003077984-894e133dabab?w=800',
                'stock' => 30,
            ],
            [
                'name' => 'Fitness Smartwatch',
                'description' => 'Track your workouts, heart rate, sleep, and more with this advanced fitness watch.',
                'price' => 199.99,
                'image_url' => 'https://images.unsplash.com/photo-1579586337278-3befd40fd17a?w=800',
                'stock' => 20,
            ],
            [
                'name' => 'Electric Coffee Grinder',
                'description' => 'Consistent, adjustable grind size for the perfect coffee every time.',
                'price' => 49.99,
                'image_url' => 'https://images.unsplash.com/photo-1519338381761-c7523edc1f46?w=800',
                'stock' => 35,
            ],
            [
                'name' => 'Mechanical Keyboard',
                'description' => 'Tactile, responsive typing experience with customizable RGB lighting.',
                'price' => 139.99,
                'image_url' => 'https://images.unsplash.com/photo-1618384887929-16ec33fab9ef?w=800',
                'stock' => 25,
            ],
            [
                'name' => 'Ergonomic Office Chair',
                'description' => 'Adjustable, comfortable chair designed for long hours of work.',
                'price' => 259.99,
                'image_url' => 'https://images.unsplash.com/photo-1611269154421-4e27233ac5c7?w=800',
                'stock' => 10,
            ],
            [
                'name' => 'Smart Home Hub',
                'description' => 'Control all your smart devices from one central hub with voice commands.',
                'price' => 129.99,
                'image_url' => 'https://images.unsplash.com/photo-1558002038-1055e2e91ddb?w=800',
                'stock' => 15,
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
} 