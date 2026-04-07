<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Salon;
use App\Models\User;
use App\Models\Service;

class LuxurySalonSeeder extends Seeder
{
    public function run(): void
    {
        $owner = User::where('email', 'admin@o2oeg.com')->first() ?? User::first();

        $luxSalons = [
            [
                'user_id' => $owner->id,
                'name' => 'Rose Palace Spa & Beauty',
                'name_ar' => 'قصر الورود للتجميل والسبا',
                'description' => 'Indulge in a world of elegance and relaxation at Rose Palace. Our rose-themed treatments and expert stylists provide an unmatched luxury experience.',
                'description_ar' => 'انغمسي في عالم من الأناقة والاسترخاء في قصر الورد. علاجاتنا المستوحاة من الورد وخبراء التصفيف لدينا يقدمون تجربة فخامة لا تضاهى.',
                'phone' => '010011223344',
                'email' => 'booking@rosepalace.com',
                'address' => 'Villa 45, Street 90, New Cairo',
                'city' => 'القاهرة الجديدة',
                'governorate' => 'القاهرة',
                'logo' => 'salons/luxury_salon_rose_1.png',
                'cover_image' => 'salons/luxury_salon_rose_1.png',
                'status' => 'active',
                'is_featured' => true,
                'rating' => 5.0,
                'total_reviews' => 120,
                'requires_deposit' => true,
                'deposit_percentage' => 20,
                'vodafone_cash_number' => '01012345678',
                'instapay_id' => 'rosepalace@instapay',
                'deposit_days' => ['thursday', 'friday', 'saturday'],
                'whatsapp_number' => '2010011223344',
                'gallery' => ['salons/luxury_salon_rose_1.png', 'salons/luxury_salon_rose_2.png'],
                'payment_methods' => ['card', 'vodafone_cash', 'instapay', 'cash'],
                'slug' => 'rose-palace-spa',
            ],
            [
                'user_id' => $owner->id,
                'name' => 'Velvet Rose Studio',
                'name_ar' => 'ستوديو فيلفيت روز',
                'description' => 'Modern, chic, and sophisticated. Velvet Rose Studio is the destination for a high-contrast premium look and feel.',
                'description_ar' => 'عصري، شيك، ومتطور. ستوديو فيلفيت روز هو الوجهة لإطلالة وشعور متميز بلمسات راقية.',
                'phone' => '012233445566',
                'email' => 'hello@velvetrose.eg',
                'address' => '45 Nile View Towers, Zamalek',
                'city' => 'الزمالك',
                'governorate' => 'القاهرة',
                'logo' => 'salons/luxury_salon_rose_2.png',
                'cover_image' => 'salons/luxury_salon_rose_2.png',
                'status' => 'active',
                'is_featured' => true,
                'rating' => 4.9,
                'total_reviews' => 85,
                'requires_deposit' => false,
                'whatsapp_number' => '2012233445566',
                'gallery' => ['salons/luxury_salon_rose_2.png', 'salons/luxury_salon_rose_3.png'],
                'payment_methods' => ['card', 'wallet', 'cash'],
                'slug' => 'velvet-rose-studio',
            ],
            [
                'user_id' => $owner->id,
                'name' => 'Cosmo Royal Heritage',
                'name_ar' => 'O2OEG Cosmo رويال هيريتيج',
                'description' => 'Where heritage meets modern beauty. Cosmo Royal Heritage provides the ultimate luxury journey with a focus on details and premium products.',
                'description_ar' => 'حيث يلتقي التراث بجمال العصر. O2OEG Cosmo رويال هيريتيج يقدم رحلة الجمال الفاخرة مع التركيز على التفاصيل وأفضل المنتجات العالمية.',
                'phone' => '011144556677',
                'email' => 'info@cosmoroyal.com',
                'address' => 'District 1, Sheikh Zayed',
                'city' => 'الشيخ زايد',
                'governorate' => 'الجيزة',
                'logo' => 'salons/luxury_salon_rose_3.png',
                'cover_image' => 'salons/luxury_salon_rose_3.png',
                'status' => 'active',
                'is_featured' => true,
                'rating' => 5.0,
                'total_reviews' => 45,
                'requires_deposit' => true,
                'deposit_percentage' => 25,
                'vodafone_cash_number' => '01112223334',
                'deposit_days' => ['friday', 'saturday'],
                'whatsapp_number' => '2011144556677',
                'gallery' => ['salons/luxury_salon_rose_1.png', 'salons/luxury_salon_rose_3.png'],
                'payment_methods' => ['vodafone_cash', 'cash'],
                'slug' => 'cosmo-royal-heritage',
            ]
        ];

        foreach ($luxSalons as $salonData) {
            $salon = Salon::updateOrCreate(
                ['slug' => $salonData['slug']],
                $salonData
            );

            // Add some luxury services for each salon if they don't have them
            if ($salon->services()->count() === 0) {
                Service::create([
                    'salon_id' => $salon->id,
                    'name' => 'Signature Rose Facial',
                    'name_ar' => 'جلسة الوجه الملكية بالورد',
                    'description' => 'A deeply relaxing facial using natural rose essences.',
                    'price' => 750,
                    'duration_minutes' => 60,
                ]);
                
                Service::create([
                    'salon_id' => $salon->id,
                    'name' => 'Master Hair Styling',
                    'name_ar' => 'قص وتصفيف احترافي',
                    'description' => 'Expert hair cut and styling by our top stylists.',
                    'price' => 450,
                    'duration_minutes' => 45,
                ]);
            }
        }
    }
}
