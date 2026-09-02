<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Str;

class NexviaSeeder extends Seeder
{
    public function run(): void
    {
        // Categories
        $categories = [
            [
                'name' => 'Electric Scooters',
                'slug' => 'electric-scooters',
                'type' => 'electric_mobility',
                'icon' => 'solar:electric-scooter-bold-duotone',
                'description' => 'Eco-friendly high performance electric mobility by NEXVIA',
                'sort_order' => 1,
            ],
            [
                'name' => 'Smart LED TV',
                'slug' => 'smart-led-tv',
                'type' => 'electronics',
                'icon' => 'solar:tv-bold-duotone',
                'description' => 'Ultra HD 4K Smart TVs with immersive audio',
                'sort_order' => 2,
            ],
            [
                'name' => 'Refrigerator',
                'slug' => 'refrigerator',
                'type' => 'electronics',
                'icon' => 'solar:fridge-bold-duotone',
                'description' => 'Energy efficient double and single door refrigerators',
                'sort_order' => 3,
            ],
            [
                'name' => 'Air Conditioner',
                'slug' => 'air-conditioner',
                'type' => 'electronics',
                'icon' => 'solar:snowflake-bold-duotone',
                'description' => 'High cooling 5-star inverter split air conditioners',
                'sort_order' => 4,
            ],
            [
                'name' => 'Washing Machine',
                'slug' => 'washing-machine',
                'type' => 'electronics',
                'icon' => 'solar:washing-machine-bold-duotone',
                'description' => 'Front load and top load fully automatic washing machines',
                'sort_order' => 5,
            ],
            [
                'name' => 'Kitchen Appliances',
                'slug' => 'kitchen-appliances',
                'type' => 'electronics',
                'icon' => 'solar:chef-hat-bold-duotone',
                'description' => 'Mixer Grinder, Chimney, Gas Stove, Microwave Oven',
                'sort_order' => 6,
            ],
            [
                'name' => 'Home Appliances',
                'slug' => 'home-appliances',
                'type' => 'electronics',
                'icon' => 'solar:home-bold-duotone',
                'description' => 'Garment Iron, Karaoke Speakers, Air Purifiers',
                'sort_order' => 7,
            ],
        ];

        foreach ($categories as $catData) {
            $cat = Category::updateOrCreate(['slug' => $catData['slug']], $catData);

            if ($cat->slug === 'electric-scooters') {
                $products = [
                    [
                        'name' => 'NEXVIA E4 Electric Scooter',
                        'model_code' => 'NEX-E4-2026',
                        'mrp' => 85000.00,
                        'key_features' => ['120 km Range', '75 km/h Top Speed', 'Fast Charging (3 hrs)', 'Digital Touch Cluster', 'GPS & App Connectivity'],
                        'specs' => ['Motor' => '3000W BLDC', 'Battery' => '3.2 kWh Lithium-ion', 'Brakes' => 'Front & Rear Disc', 'Tyres' => '12-inch Tubeless'],
                        'warranty_info' => '3 Years Comprehensive Battery & Motor Warranty',
                        'is_featured' => true,
                    ],
                    [
                        'name' => 'NEXVIA CS1 City Scooter',
                        'model_code' => 'NEX-CS1-2026',
                        'mrp' => 68000.00,
                        'key_features' => ['95 km Range', '60 km/h Top Speed', 'Removable Battery', 'Keyless Start', 'Anti-theft Alarm'],
                        'specs' => ['Motor' => '2000W Hub Motor', 'Battery' => '2.5 kWh Lithium-ion', 'Brakes' => 'CBS Disc Brakes'],
                        'warranty_info' => '3 Years Battery Warranty',
                        'is_featured' => true,
                    ],
                    [
                        'name' => 'NEXVIA Retro Round Light Edition',
                        'model_code' => 'NEX-RETRO-R',
                        'mrp' => 92000.00,
                        'key_features' => ['Classic Vintage Design', '110 km Range', 'Round LED Headlamp', 'Chrome Finish Details', 'Dual Riding Modes'],
                        'specs' => ['Motor' => '2800W Peak Power', 'Battery' => '3.0 kWh Swappable Battery'],
                        'warranty_info' => '3 Years Brand Warranty',
                        'is_featured' => true,
                    ],
                ];
                foreach ($products as $pData) {
                    $mrp = $pData['mrp'];
                    $bookingPct = 20;
                    $bookingAmt = $mrp * ($bookingPct / 100);
                    $balanceAmt = $mrp - $bookingAmt;

                    Product::updateOrCreate(['slug' => Str::slug($pData['name'])], array_merge($pData, [
                        'category_id' => $cat->id,
                        'slug' => Str::slug($pData['name']),
                        'booking_percentage' => $bookingPct,
                        'booking_amount' => $bookingAmt,
                        'balance_amount' => $balanceAmt,
                        'stock' => 25,
                        'status' => 'active',
                    ]));
                }
            }

            if ($cat->slug === 'smart-led-tv') {
                $products = [
                    [
                        'name' => 'NEXVIA 55-inch Ultra HD 4K Smart LED TV',
                        'model_code' => 'NEX-TV55-4K',
                        'mrp' => 45000.00,
                        'key_features' => ['4K Ultra HD Display', 'Dolby Vision & Atmos', 'Google TV OS', 'Hands-free Voice Control', 'Dual Band Wi-Fi'],
                        'specs' => ['Screen Size' => '55 inches', 'Resolution' => '3840 x 2160', 'Refresh Rate' => '60 Hz', 'Sound Output' => '30W Dolby Audio'],
                        'warranty_info' => '2 Years Full Panel Warranty',
                        'is_featured' => true,
                    ],
                    [
                        'name' => 'NEXVIA 43-inch Full HD Smart Android TV',
                        'model_code' => 'NEX-TV43-FHD',
                        'mrp' => 28000.00,
                        'key_features' => ['Bezel-less Frameless Design', 'HDR 10 Support', 'Pre-installed Netflix/Prime/YouTube', 'Chromecast Built-in'],
                        'specs' => ['Screen Size' => '43 inches', 'Resolution' => '1920 x 1080', 'RAM/ROM' => '1.5GB / 8GB'],
                        'warranty_info' => '1 Year Brand Warranty',
                        'is_featured' => false,
                    ],
                ];
                foreach ($products as $pData) {
                    $mrp = $pData['mrp'];
                    $bookingPct = 20;
                    $bookingAmt = $mrp * ($bookingPct / 100);
                    $balanceAmt = $mrp - $bookingAmt;

                    Product::updateOrCreate(['slug' => Str::slug($pData['name'])], array_merge($pData, [
                        'category_id' => $cat->id,
                        'slug' => Str::slug($pData['name']),
                        'booking_percentage' => $bookingPct,
                        'booking_amount' => $bookingAmt,
                        'balance_amount' => $balanceAmt,
                        'stock' => 40,
                        'status' => 'active',
                    ]));
                }
            }

            if ($cat->slug === 'refrigerator') {
                $pData = [
                    'name' => 'NEXVIA 340L Double Door Frost-Free Refrigerator',
                    'model_code' => 'NEX-REF340-FF',
                    'mrp' => 38000.00,
                    'key_features' => ['Convertible 5-in-1 Modes', 'Digital Inverter Compressor', 'Smart Connect Auto Express Cooling', 'Toughened Glass Shelves'],
                    'specs' => ['Capacity' => '340 Litres', 'Energy Rating' => '3 Star', 'Defrost System' => 'Frost Free'],
                    'warranty_info' => '1 Year Product & 10 Years Compressor Warranty',
                    'is_featured' => true,
                ];
                $mrp = $pData['mrp'];
                $bookingAmt = $mrp * 0.20;
                $balanceAmt = $mrp - $bookingAmt;
                Product::updateOrCreate(['slug' => Str::slug($pData['name'])], array_merge($pData, [
                    'category_id' => $cat->id,
                    'slug' => Str::slug($pData['name']),
                    'booking_percentage' => 20,
                    'booking_amount' => $bookingAmt,
                    'balance_amount' => $balanceAmt,
                    'stock' => 30,
                    'status' => 'active',
                ]));
            }

            if ($cat->slug === 'air-conditioner') {
                $pData = [
                    'name' => 'NEXVIA 1.5 Ton 5-Star Inverter Split AC',
                    'model_code' => 'NEX-AC15-5S',
                    'mrp' => 42000.00,
                    'key_features' => ['100% Copper Condenser', 'Convertible 4-in-1 Cooling', 'Anti-Bacterial PM 2.5 Filter', 'Stabilizer Free Operation'],
                    'specs' => ['Cooling Capacity' => '1.5 Ton', 'Energy Rating' => '5 Star', 'Refrigerant' => 'R32 Eco-Friendly'],
                    'warranty_info' => '1 Year Comprehensive & 10 Years Compressor Warranty',
                    'is_featured' => true,
                ];
                $mrp = $pData['mrp'];
                $bookingAmt = $mrp * 0.20;
                $balanceAmt = $mrp - $bookingAmt;
                Product::updateOrCreate(['slug' => Str::slug($pData['name'])], array_merge($pData, [
                    'category_id' => $cat->id,
                    'slug' => Str::slug($pData['name']),
                    'booking_percentage' => 20,
                    'booking_amount' => $bookingAmt,
                    'balance_amount' => $balanceAmt,
                    'stock' => 20,
                    'status' => 'active',
                ]));
            }

            if ($cat->slug === 'washing-machine') {
                $pData = [
                    'name' => 'NEXVIA 8kg Front Load Fully Automatic Washing Machine',
                    'model_code' => 'NEX-WM8-FL',
                    'mrp' => 34000.00,
                    'key_features' => ['Inverter Direct Drive Motor', 'Steam Hygiene Wash', '14 Wash Programs', 'Child Lock & Tub Clean'],
                    'specs' => ['Capacity' => '8 kg', 'Spin Speed' => '1200 RPM', 'Energy Rating' => '5 Star'],
                    'warranty_info' => '2 Years Product & 10 Years Motor Warranty',
                    'is_featured' => false,
                ];
                $mrp = $pData['mrp'];
                $bookingAmt = $mrp * 0.20;
                $balanceAmt = $mrp - $bookingAmt;
                Product::updateOrCreate(['slug' => Str::slug($pData['name'])], array_merge($pData, [
                    'category_id' => $cat->id,
                    'slug' => Str::slug($pData['name']),
                    'booking_percentage' => 20,
                    'booking_amount' => $bookingAmt,
                    'balance_amount' => $balanceAmt,
                    'stock' => 15,
                    'status' => 'active',
                ]));
            }

            if ($cat->slug === 'kitchen-appliances') {
                $items = [
                    ['name' => 'NEXVIA 750W Heavy Duty Mixer Grinder', 'mrp' => 4800.00],
                    ['name' => 'NEXVIA Auto-Clean Kitchen Chimney 60cm', 'mrp' => 14500.00],
                    ['name' => 'NEXVIA 3-Burner Toughened Glass Gas Stove', 'mrp' => 6200.00],
                    ['name' => 'NEXVIA 28L Convection Microwave Oven', 'mrp' => 12800.00],
                ];
                foreach ($items as $item) {
                    $mrp = $item['mrp'];
                    $bAmt = $mrp * 0.20;
                    Product::updateOrCreate(['slug' => Str::slug($item['name'])], [
                        'category_id' => $cat->id,
                        'name' => $item['name'],
                        'model_code' => 'NEX-KIT-' . rand(100, 999),
                        'slug' => Str::slug($item['name']),
                        'mrp' => $mrp,
                        'booking_percentage' => 20,
                        'booking_amount' => $bAmt,
                        'balance_amount' => $mrp - $bAmt,
                        'stock' => 50,
                        'key_features' => ['High Efficiency Operation', 'Premium Build Quality', 'Compact Modern Ergonomics'],
                        'specs' => ['Warranty' => '1 Year Comprehensive Warranty'],
                        'warranty_info' => '1 Year Warranty',
                        'status' => 'active',
                    ]);
                }
            }

            if ($cat->slug === 'home-appliances') {
                $items = [
                    ['name' => 'NEXVIA Garment Steam Iron 2000W', 'mrp' => 2400.00],
                    ['name' => 'NEXVIA Party Blast Wireless Karaoke Speaker 100W', 'mrp' => 8900.00],
                ];
                foreach ($items as $item) {
                    $mrp = $item['mrp'];
                    $bAmt = $mrp * 0.20;
                    Product::updateOrCreate(['slug' => Str::slug($item['name'])], [
                        'category_id' => $cat->id,
                        'name' => $item['name'],
                        'model_code' => 'NEX-HOME-' . rand(100, 999),
                        'slug' => Str::slug($item['name']),
                        'mrp' => $mrp,
                        'booking_percentage' => 20,
                        'booking_amount' => $bAmt,
                        'balance_amount' => $mrp - $bAmt,
                        'stock' => 50,
                        'key_features' => ['Sleek Portable Design', 'Top Grade Performance', 'Rich Sound Output'],
                        'specs' => ['Warranty' => '1 Year Brand Warranty'],
                        'warranty_info' => '1 Year Warranty',
                        'status' => 'active',
                    ]);
                }
            }
        }
    }
}
