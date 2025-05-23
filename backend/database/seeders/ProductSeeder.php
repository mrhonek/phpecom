<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\CartItem;
use App\Models\OrderItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check for existing related records
        $hasCartItems = CartItem::count() > 0;
        $hasOrderItems = OrderItem::count() > 0;
        
        if ($hasCartItems || $hasOrderItems) {
            // If there are related records, we'll update existing products instead of truncating
            $this->updateExistingProducts();
        } else {
            // Clear existing products if no related records exist
            Schema::disableForeignKeyConstraints();
            DB::table('products')->truncate();
            Schema::enableForeignKeyConstraints();
            
            $this->createNewProducts();
        }
    }
    
    /**
     * Create new products from scratch
     */
    private function createNewProducts(): void
    {
        $products = $this->getProductsData();
        
        foreach ($products as $product) {
            Product::create($product);
        }
    }
    
    /**
     * Update existing products without truncating
     */
    private function updateExistingProducts(): void
    {
        $products = $this->getProductsData();
        
        // Get all existing products
        $existingProducts = Product::all();
        
        // Only process up to the number of existing products
        for ($i = 0; $i < min(count($products), count($existingProducts)); $i++) {
            // Update existing product with new data
            $existingProducts[$i]->update($products[$i]);
        }
        
        // If we have more products in our seed data than exist in the database,
        // create the additional products
        if (count($products) > count($existingProducts)) {
            $additionalProducts = array_slice($products, count($existingProducts));
            foreach ($additionalProducts as $product) {
                Product::create($product);
            }
        }
    }
    
    /**
     * Get the products data array
     */
    private function getProductsData(): array
    {
        return [
            [
                'name' => 'Smartphone XS Pro',
                'description' => 'Latest flagship smartphone with advanced camera system and all-day battery life.',
                'price' => 899.99,
                'image_url' => 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=800',
                'image_filename' => 'smartphone-xs-pro.jpg',
                'image_path' => 'images/products',
                'image_alt' => 'Smartphone XS Pro on a wooden surface',
                'image_thumbnail' => 'smartphone-xs-pro-thumb.jpg',
                'stock' => 25,
            ],
            [
                'name' => 'Ultra HD Smart TV 55"',
                'description' => 'Crystal clear 4K display with smart features and voice control.',
                'price' => 649.99,
                'image_url' => 'https://images.unsplash.com/photo-1593359677879-a4bb92f829d1?w=800',
                'image_filename' => 'ultra-hd-tv.jpg',
                'image_path' => 'images/products',
                'image_alt' => 'Ultra HD Smart TV in modern living room',
                'image_thumbnail' => 'ultra-hd-tv-thumb.jpg',
                'stock' => 15,
            ],
            [
                'name' => 'Wireless Noise-Cancelling Headphones',
                'description' => 'Premium over-ear headphones with active noise cancellation and 30-hour battery life.',
                'price' => 249.99,
                'image_url' => 'https://images.unsplash.com/photo-1546435770-a3e736e9ae14?w=800',
                'image_filename' => 'wireless-headphones.jpg',
                'image_path' => 'images/products',
                'image_alt' => 'Wireless noise-cancelling headphones in black',
                'image_thumbnail' => 'wireless-headphones-thumb.jpg',
                'stock' => 40,
            ],
            [
                'name' => 'Professional Digital Camera',
                'description' => 'High-resolution DSLR camera with interchangeable lenses and 4K video recording.',
                'price' => 1299.99,
                'image_url' => 'https://images.unsplash.com/photo-1516035069371-29a1b244cc32?w=800',
                'image_filename' => 'digital-camera.jpg',
                'image_path' => 'images/products',
                'image_alt' => 'Professional digital camera with lens',
                'image_thumbnail' => 'digital-camera-thumb.jpg',
                'stock' => 10,
            ],
            [
                'name' => 'Portable Bluetooth Speaker',
                'description' => 'Waterproof, rugged speaker with 360° sound and 20-hour battery life.',
                'price' => 129.99,
                'image_url' => 'https://images.unsplash.com/photo-1589003077984-894e133dabab?w=800',
                'image_filename' => 'bluetooth-speaker.jpg',
                'image_path' => 'images/products',
                'image_alt' => 'Portable bluetooth speaker in blue color',
                'image_thumbnail' => 'bluetooth-speaker-thumb.jpg',
                'stock' => 30,
            ],
            [
                'name' => 'Fitness Smartwatch',
                'description' => 'Track your workouts, heart rate, sleep, and more with this advanced fitness watch.',
                'price' => 199.99,
                'image_url' => 'https://images.unsplash.com/photo-1579586337278-3befd40fd17a?w=800',
                'image_filename' => 'fitness-smartwatch.jpg',
                'image_path' => 'images/products',
                'image_alt' => 'Fitness smartwatch showing heart rate',
                'image_thumbnail' => 'fitness-smartwatch-thumb.jpg',
                'stock' => 20,
            ],
            [
                'name' => 'Electric Coffee Grinder',
                'description' => 'Consistent, adjustable grind size for the perfect coffee every time.',
                'price' => 49.99,
                'image_url' => 'https://images.unsplash.com/photo-1519338381761-c7523edc1f46?w=800',
                'image_filename' => 'coffee-grinder.jpg',
                'image_path' => 'images/products',
                'image_alt' => 'Electric coffee grinder with coffee beans',
                'image_thumbnail' => 'coffee-grinder-thumb.jpg',
                'stock' => 35,
            ],
            [
                'name' => 'Mechanical Keyboard',
                'description' => 'Tactile, responsive typing experience with customizable RGB lighting.',
                'price' => 139.99,
                'image_url' => 'https://images.unsplash.com/photo-1618384887929-16ec33fab9ef?w=800',
                'image_filename' => 'mechanical-keyboard.jpg',
                'image_path' => 'images/products',
                'image_alt' => 'Mechanical keyboard with RGB lighting',
                'image_thumbnail' => 'mechanical-keyboard-thumb.jpg',
                'stock' => 25,
            ],
            [
                'name' => 'Ergonomic Office Chair',
                'description' => 'Adjustable, comfortable chair designed for long hours of work.',
                'price' => 259.99,
                'image_url' => 'https://images.unsplash.com/photo-1611269154421-4e27233ac5c7?w=800',
                'image_filename' => 'office-chair.jpg',
                'image_path' => 'images/products',
                'image_alt' => 'Ergonomic office chair in black',
                'image_thumbnail' => 'office-chair-thumb.jpg',
                'stock' => 10,
            ],
            [
                'name' => 'Smart Home Hub',
                'description' => 'Control all your smart devices from one central hub with voice commands.',
                'price' => 129.99,
                'image_url' => 'https://images.unsplash.com/photo-1558002038-1055e2e91ddb?w=800',
                'image_filename' => 'smart-home-hub.jpg',
                'image_path' => 'images/products',
                'image_alt' => 'Smart home hub on a coffee table',
                'image_thumbnail' => 'smart-home-hub-thumb.jpg',
                'stock' => 15,
            ],
        ];
    }
} 