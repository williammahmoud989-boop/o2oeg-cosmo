<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $salons = \App\Models\Salon::all();
        
        $serviceTemplates = [
            ['name' => 'Hair Cut & Styling', 'name_ar' => 'قص وتصفيف شعر', 'price' => 350, 'duration' => 45, 'category' => 'شعر'],
            ['name' => 'Full Hair Coloring', 'name_ar' => 'صبغة شعر كاملة', 'price' => 1200, 'duration' => 120, 'category' => 'شعر'],
            ['name' => 'HydraFacial Treatment', 'name_ar' => 'هيدرافيشال للوجه', 'price' => 850, 'duration' => 60, 'category' => 'بشرة'],
            ['name' => 'Gel Nails', 'name_ar' => 'تركيب أظافر جل', 'price' => 450, 'duration' => 90, 'category' => 'أظافر'],
            ['name' => 'Professional Makeup', 'name_ar' => 'مكياج سواريه احترافي', 'price' => 1500, 'duration' => 75, 'category' => 'مكياج'],
        ];

        foreach ($salons as $salon) {
            foreach ($serviceTemplates as $template) {
                \App\Models\Service::updateOrCreate(
                    [
                        'salon_id' => $salon->id,
                        'name' => $template['name']
                    ],
                    [
                        'name_ar' => $template['name_ar'],
                        'description' => 'Professional ' . $template['name'] . ' service adapted to your needs.',
                        'description_ar' => 'خدمة ' . $template['name_ar'] . ' احترافية مصممة خصيصاً لتناسب احتياجاتك.',
                        'price' => $template['price'],
                        'duration_minutes' => $template['duration'],
                        'category' => $template['category'],
                        'is_active' => true,
                    ]
                );
            }
        }
    }
}
