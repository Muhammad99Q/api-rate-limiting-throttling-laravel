<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // --- Seed Users ---

        // Normal user (rate limit: 6 requests/minute)
        User::create([
            'name'     => 'Normal User',
            'email'    => 'user@example.com',
            'password' => Hash::make('password123'),
            'role'     => 'user',
        ]);

        // VIP user (rate limit: 20 requests/minute)
        User::create([
            'name'     => 'VIP User',
            'email'    => 'vip@example.com',
            'password' => Hash::make('password123'),
            'role'     => 'vip',
        ]);

        // --- Seed 20 Realistic Tech/Electronics Products ---
        $products = [
            ['name' => 'iPhone 15 Pro',          'brand' => 'Apple',     'price' => 999.99,  'stock' => 50],
            ['name' => 'Galaxy S24 Ultra',       'brand' => 'Samsung',   'price' => 1199.99, 'stock' => 35],
            ['name' => 'MacBook Air M3',         'brand' => 'Apple',     'price' => 1099.00, 'stock' => 25],
            ['name' => 'ThinkPad X1 Carbon',     'brand' => 'Lenovo',    'price' => 1399.00, 'stock' => 20],
            ['name' => 'iPad Pro 12.9"',         'brand' => 'Apple',     'price' => 1099.00, 'stock' => 40],
            ['name' => 'Galaxy Tab S9',          'brand' => 'Samsung',   'price' => 799.99,  'stock' => 30],
            ['name' => 'Sony WH-1000XM5',       'brand' => 'Sony',      'price' => 349.99,  'stock' => 100],
            ['name' => 'AirPods Pro 2',          'brand' => 'Apple',     'price' => 249.00,  'stock' => 150],
            ['name' => 'Dell XPS 15',            'brand' => 'Dell',      'price' => 1299.99, 'stock' => 15],
            ['name' => 'HP Spectre x360',        'brand' => 'HP',        'price' => 1249.99, 'stock' => 18],
            ['name' => 'LG OLED C3 55"',         'brand' => 'LG',       'price' => 1499.99, 'stock' => 12],
            ['name' => 'Samsung QLED 65"',       'brand' => 'Samsung',   'price' => 1299.00, 'stock' => 10],
            ['name' => 'PlayStation 5',          'brand' => 'Sony',      'price' => 499.99,  'stock' => 60],
            ['name' => 'Xbox Series X',          'brand' => 'Microsoft', 'price' => 499.99,  'stock' => 55],
            ['name' => 'Nintendo Switch OLED',   'brand' => 'Nintendo',  'price' => 349.99,  'stock' => 70],
            ['name' => 'Logitech MX Master 3S',  'brand' => 'Logitech',  'price' => 99.99,   'stock' => 200],
            ['name' => 'Razer BlackWidow V4',    'brand' => 'Razer',     'price' => 169.99,  'stock' => 80],
            ['name' => 'Canon EOS R6 Mark II',   'brand' => 'Canon',     'price' => 2499.00, 'stock' => 8],
            ['name' => 'GoPro Hero 12',          'brand' => 'GoPro',     'price' => 399.99,  'stock' => 45],
            ['name' => 'DJI Mini 4 Pro',         'brand' => 'DJI',       'price' => 759.00,  'stock' => 22],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
