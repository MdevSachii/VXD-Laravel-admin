<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TourSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Tour::create([
            'title' => 'City Tour',
            'description' => 'A guided tour through the city\'s most famous landmarks.',
            'price' => 100.00,
            'date' => '2025-09-01',
        ]);

        \App\Models\Tour::create([
            'title' => 'Beach Getaway',
            'description' => 'Relax and unwind on the beautiful sandy beaches.',
            'price' => 149.99,
            'date' => '2025-09-15',
        ]);

        \App\Models\Tour::create([
            'title' => 'Mountain Adventure',
            'description' => 'Experience the thrill of hiking in the mountains.',
            'price' => 199.99,
            'date' => '2025-09-30',
        ]);
    }
}
