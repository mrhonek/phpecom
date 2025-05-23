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
                'name' => 'Smartphone XS Pro',
                'description' => 'Latest flagship smartphone with advanced camera system and all-day battery life.',
                'price' => 899.99,
                'image_url' => 'https://images.unsplash.com/photo-1598327105666-5b89351aff97?q=80&w=2942&auto=format&fit=crop',
                'stock' => 25,
            ],
            [
                'name' => 'Ultra HD Smart TV 55"',
                'description' => 'Crystal clear 4K display with smart features and voice control.',
                'price' => 649.99,
                'image_url' => 'https://images.unsplash.com/photo-1593359677879-a4bb92f829d1?q=80&w=2940&auto=format&fit=crop',
                'stock' => 15,
            ],
            [
                'name' => 'Wireless Noise-Cancelling Headphones',
                'description' => 'Premium over-ear headphones with active noise cancellation and 30-hour battery life.',
                'price' => 249.99,
                'image_url' => 'https://images.unsplash.com/photo-1546435770-a3e736e9ae14?q=80&w=2946&auto=format&fit=crop',
                'stock' => 40,
            ],
            [
                'name' => 'Professional Digital Camera',
                'description' => 'High-resolution DSLR camera with interchangeable lenses and 4K video recording.',
                'price' => 1299.99,
                'image_url' => 'https://images.unsplash.com/photo-1516035069371-29a1b244cc32?q=80&w=3164&auto=format&fit=crop',
                'stock' => 10,
            ],
            [
                'name' => 'Portable Bluetooth Speaker',
                'description' => 'Waterproof, rugged speaker with 360° sound and 20-hour battery life.',
                'price' => 129.99,
                'image_url' => 'https://images.unsplash.com/photo-1608043152269-423dbba4e7e1?q=80&w=2936&auto=format&fit=crop',
                'stock' => 30,
            ],
            [
                'name' => 'Fitness Smartwatch',
                'description' => 'Track your workouts, heart rate, sleep, and more with this advanced fitness watch.',
                'price' => 199.99,
                'image_url' => 'https://images.unsplash.com/photo-1579586337278-3befd40fd17a?q=80&w=2972&auto=format&fit=crop',
                'stock' => 20,
            ],
            [
                'name' => 'Electric Coffee Grinder',
                'description' => 'Consistent, adjustable grind size for the perfect coffee every time.',
                'price' => 49.99,
                'image_url' => 'https://images.unsplash.com/photo-1564538989862-e9301f908f01?q=80&w=2970&auto=format&fit=crop',
                'stock' => 35,
            ],
            [
                'name' => 'Mechanical Keyboard',
                'description' => 'Tactile, responsive typing experience with customizable RGB lighting.',
                'price' => 139.99,
                'image_url' => 'https://images.unsplash.com/photo-1618384887929-16ec33fab9ef?q=80&w=2940&auto=format&fit=crop',
                'stock' => 25,
            ],
            [
                'name' => 'Ergonomic Office Chair',
                'description' => 'Adjustable, comfortable chair designed for long hours of work.',
                'price' => 259.99,
                'image_url' => 'https://images.unsplash.com/photo-1611269154421-4e27233ac5c7?q=80&w=2865&auto=format&fit=crop',
                'stock' => 10,
            ],
            [
                'name' => 'Smart Home Hub',
                'description' => 'Control all your smart devices from one central hub with voice commands.',
                'price' => 129.99,
                'image_url' => 'https://images.unsplash.com/photo-1563013544-824ae1b704d3?q=80&w=2940&auto=format&fit=crop',
                'stock' => 15,
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
} 