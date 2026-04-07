<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OfferSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $salons = \App\Models\Salon::all();
        if ($salons->isEmpty()) return;

        foreach ($salons as $salon) {
            $service = $salon->services()->first();

            // Offer 1: Wedding Package
            \App\Models\Offer::updateOrCreate(
                [
                    'salon_id' => $salon->id,
                    'title' => 'Royal Bridal Package'
                ],
                [
                    'service_id' => $service ? $service->id : null,
                    'title_ar' => 'باقة العروسة الملكية',
                    'description_ar' => 'خصم خاص لفترة محدودة على باقة العروسة الشاملة (شعر، ميكب، وتنظيف بشرة).',
                    'description' => 'Special limited time discount on the full bridal package.',
                    'discount_percentage' => 20,
                    'original_price' => 5000,
                    'discounted_price' => 4000,
                    'expires_at' => now()->addMonths(2),
                    'is_active' => true,
                ]
            );

            // Offer 2: Flash Sale
            \App\Models\Offer::updateOrCreate(
                [
                    'salon_id' => $salon->id,
                    'title' => '48 Hour Flash Sale'
                ],
                [
                    'service_id' => $service ? $service->id : null,
                    'title_ar' => 'عرض الـ 48 ساعة',
                    'description_ar' => 'احصلي على خصم 50% على جميع خدمات العناية بالأظافر.',
                    'description' => 'Get 50% off on all nail care services.',
                    'discount_percentage' => 50,
                    'original_price' => 400,
                    'discounted_price' => 200,
                    'expires_at' => now()->addDays(2),
                    'is_active' => true,
                ]
            );
        }
    }
}
