<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Str;

class DlsFarmEquipmentSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Solar Water Pumps',
                'slug' => 'solar-water-pumps',
                'type' => 'dls_farm_equipment',
                'referral_category_code' => 'SOLPUMP',
                'referral_eligible' => true,
                'commission_percentage' => 10.00,
                'description' => 'High efficiency AC/DC submersible solar agricultural pumps with MPPT controller',
                'image' => 'https://images.unsplash.com/photo-1509391365360-2e959784a276?w=800&auto=format&fit=crop&q=80',
                'sort_order' => 1,
            ],
            [
                'name' => 'Power Tillers & Cultivators',
                'slug' => 'power-tillers-cultivators',
                'type' => 'dls_farm_equipment',
                'referral_category_code' => 'TILLER',
                'referral_eligible' => true,
                'commission_percentage' => 12.00,
                'description' => 'Heavy duty diesel and electric power tillers for dryland and wetland farming',
                'image' => 'https://images.unsplash.com/photo-1592982537447-7440770cbfc9?w=800&auto=format&fit=crop&q=80',
                'sort_order' => 2,
            ],
            [
                'name' => 'Sprayers & Agricultural Drones',
                'slug' => 'sprayers-agri-drones',
                'type' => 'dls_farm_equipment',
                'referral_category_code' => 'DRONE',
                'referral_eligible' => true,
                'commission_percentage' => 15.00,
                'description' => 'Battery-operated high precision crop sprayers and automated agricultural payload drones',
                'image' => 'https://images.unsplash.com/photo-1508614589041-895b88991e3e?w=800&auto=format&fit=crop&q=80',
                'sort_order' => 3,
            ],
        ];

        foreach ($categories as $catData) {
            $cat = Category::updateOrCreate(
                ['slug' => $catData['slug']],
                $catData
            );

            if ($cat->slug === 'solar-water-pumps') {
                $mrp = 85000.00;
                $booking = round($mrp * 0.20, 2);
                Product::updateOrCreate(
                    ['slug' => 'dls-5hp-solar-submersible-pump'],
                    [
                        'category_id' => $cat->id,
                        'name' => 'DLS 5HP Solar Submersible Water Pump',
                        'model_code' => 'DLS-SOL-5000',
                        'sku' => 'SKU-FARM-PUMP-01',
                        'mrp' => $mrp,
                        'booking_percentage' => 20,
                        'booking_amount' => $booking,
                        'balance_amount' => round($mrp - $booking, 2),
                        'eligible_referral_value' => $mrp,
                        'referral_eligible' => true,
                        'self_dealer_eligible' => true,
                        'stock' => 12,
                        'offer_text' => 'Govt Subsidy Assistance Available',
                        'main_image' => 'https://images.unsplash.com/photo-1509391365360-2e959784a276?w=800&auto=format&fit=crop&q=80',
                        'warranty_info' => '5 Years Manufacturer Warranty on Pump & Controller',
                        'installation_info' => 'Free On-site Farm Installation & Borehole Testing',
                        'is_featured' => true,
                        'status' => 'active',
                    ]
                );
            } elseif ($cat->slug === 'power-tillers-cultivators') {
                $mrp = 115000.00;
                $booking = round($mrp * 0.20, 2);
                Product::updateOrCreate(
                    ['slug' => 'dls-power-tiller-12hp-diesel'],
                    [
                        'category_id' => $cat->id,
                        'name' => 'DLS 12HP Heavy Duty Power Tiller',
                        'model_code' => 'DLS-PT-1200',
                        'sku' => 'SKU-FARM-TILL-01',
                        'mrp' => $mrp,
                        'booking_percentage' => 20,
                        'booking_amount' => $booking,
                        'balance_amount' => round($mrp - $booking, 2),
                        'eligible_referral_value' => $mrp,
                        'referral_eligible' => true,
                        'self_dealer_eligible' => true,
                        'stock' => 8,
                        'offer_text' => 'Special Farm Festival Discount ₹5,000 Off',
                        'main_image' => 'https://images.unsplash.com/photo-1592982537447-7440770cbfc9?w=800&auto=format&fit=crop&q=80',
                        'warranty_info' => '2 Years Engine & Gearbox Comprehensive Warranty',
                        'installation_info' => 'On-field Demonstration and Blade Setting Included',
                        'is_featured' => true,
                        'status' => 'active',
                    ]
                );
            }
        }
    }
}
