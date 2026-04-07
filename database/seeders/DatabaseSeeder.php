<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create or update a default admin user
        User::updateOrCreate(
            ['email' => 'williammahmoud989@gmail.com'],
            [
                'name' => 'O2OEG Admin',
                'password' => 'password',
                'is_admin' => true,
            ]
        );

        // 2. Run child seeders (Order matters: Salon -> Service -> Offer)
        $this->call([
            SalonSeeder::class,
            LuxurySalonSeeder::class,
            ServiceSeeder::class,
            OfferSeeder::class,
        ]);
    }
}
