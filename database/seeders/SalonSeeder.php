<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SalonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = \App\Models\User::first();

        $salons = [
            [
                'user_id' => $user->id,
                'name' => 'Golden Touch Cairo',
                'name_ar' => 'جولدن تاتش كايرو',
                'description' => 'A luxury salon in the heart of New Cairo offering world-class beauty services.',
                'description_ar' => 'صالون فاخر في قلب القاهرة الجديدة يقدم خدمات تجميل عالمية المستوى وبلمسة ملكية.',
                'phone' => '01234567890',
                'email' => 'info@goldentouch.com',
                'address' => 'شارع التسعين، التجمع الخامس',
                'city' => 'القاهرة الجديدة',
                'governorate' => 'القاهرة',
                'status' => 'active',
                'is_featured' => true,
                'rating' => 4.9,
                'total_reviews' => 245,
                'whatsapp_number' => '201234567890',
                'instagram_url' => 'https://instagram.com/goldentouch',
                'facebook_url' => 'https://facebook.com/goldentouch',
                'gallery' => ['salon1_1.jpg', 'salon1_2.jpg'],
            ],
            [
                'user_id' => $user->id,
                'name' => 'Elite Beauty Hub',
                'name_ar' => 'إيليت بيوتي هب',
                'description' => 'The ultimate destination for hair and skin care in Mohandessin.',
                'description_ar' => 'الوجهة الأمثل للعناية بالشعر والبشرة في المهندسين، نجمع بين الخبرة والأناقة.',
                'phone' => '01112223334',
                'email' => 'contact@elitebeauty.com',
                'address' => 'شارع جامعة الدول العربية',
                'city' => 'المهندسين',
                'governorate' => 'الجيزة',
                'status' => 'active',
                'is_featured' => true,
                'rating' => 4.8,
                'total_reviews' => 180,
                'whatsapp_number' => '201112223334',
                'gallery' => ['salon2_1.jpg', 'salon2_2.jpg'],
            ],
            [
                'user_id' => $user->id,
                'name' => 'Modern Egyptian Muse',
                'name_ar' => 'مودرن إيجيبشيان ميوز',
                'description' => 'Experience beauty like never before with our modern techniques.',
                'description_ar' => 'اختبري الجمال كما لم تعهديه من قبل مع تقنياتنا الحديثة وروحنا المصرية.',
                'phone' => '01009988776',
                'email' => 'hello@egyptianmuse.com',
                'address' => 'بيفرلي هيلز، الشيخ زايد',
                'city' => 'الشيخ زايد',
                'governorate' => 'الجيزة',
                'status' => 'active',
                'is_featured' => false,
                'rating' => 5.0,
                'total_reviews' => 89,
                'whatsapp_number' => '201009988776',
                'gallery' => ['salon3_1.jpg', 'salon3_2.jpg'],
            ],
        ];

        foreach ($salons as $salon) {
            \App\Models\Salon::updateOrCreate(
                ['name' => $salon['name']],
                $salon
            );
        }
    }
}
