<?php

namespace Database\Seeders;

use App\Models\Salon;
use App\Models\User;
use App\Models\Service;
use App\Models\Staff;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class GenZ2026Seeder extends Seeder
{
    public function run(): void
    {
        // 1. Create a Demo Owner
        $owner = User::firstOrCreate(
            ['email' => 'demo@o2oeg.com'],
            [
                'name' => 'جمال ذكي',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );

        // 2. Create Futuristic Salons
        $salons = [
            [
                'name' => 'Cyber Beauty Lab',
                'name_ar' => 'سايبر بيوتي لاب',
                'subdomain' => 'cyber-beauty',
                'city' => 'القاهرة',
                'governorate' => 'القاهرة',
                'address' => 'التجمع الخامس، شارع التسعين',
                'status' => 'active',
                'is_featured' => true,
                'description' => 'A high-tech beauty experience for the digital generation.',
                'description_ar' => 'تجربة تجميل عالية التقنية للجيل الرقمي.',
            ],
            [
                'name' => 'Indigo Glow Studio',
                'name_ar' => 'ستوديو إنديجو جلو',
                'subdomain' => 'indigo-glow',
                'city' => 'الإسكندرية',
                'governorate' => 'الإسكندرية',
                'address' => 'كفر عبده، شارع سوريا',
                'status' => 'active',
                'is_featured' => true,
                'description' => 'Luxury aesthetics meets Gen Z vibes.',
                'description_ar' => 'الجمال الفاخر يلتقي مع روح جيل Z.',
            ]
        ];

        foreach ($salons as $salonData) {
            $salonData['user_id'] = $owner->id;
            $salonData['slug'] = Str::slug($salonData['name']);
            $salon = Salon::updateOrCreate(['slug' => $salonData['slug']], $salonData);

            // 3. Create Services for each salon
            $services = [
                ['name' => 'AI Hair Analysis', 'name_ar' => 'تحليل الشعر بالذكاء الاصطناعي', 'price' => 500],
                ['name' => 'Neon Nail Art', 'name_ar' => 'فن الأظافر النيوني', 'price' => 300],
                ['name' => 'Digital Skin Scan', 'name_ar' => 'مسح البشرة الرقمي', 'price' => 450],
            ];

            foreach ($services as $serviceData) {
                Service::updateOrCreate(
                    ['salon_id' => $salon->id, 'name' => $serviceData['name']],
                    array_merge($serviceData, ['duration' => 45, 'is_active' => true])
                );
            }

            // 4. Create Staff
            $staffNames = [
                ['name' => 'أحمد العبقري', 'role' => 'Beauty Tech Specialist'],
                ['name' => 'سارة المستقبل', 'role' => 'AI Consultant'],
            ];

            foreach ($staffNames as $staffData) {
                Staff::updateOrCreate(
                    ['salon_id' => $salon->id, 'name' => $staffData['name']],
                    array_merge($staffData, ['status' => 'active'])
                );
            }
        }
    }
}
