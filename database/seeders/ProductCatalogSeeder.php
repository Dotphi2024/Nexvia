<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Str;

class ProductCatalogSeeder extends Seeder
{
    public function run()
    {
        // 1. Seed/Update Categories with Image & Icon
        $catTv = Category::updateOrCreate(
            ['slug' => 'smart-led-tv'],
            [
                'name' => 'Smart LED TV',
                'type' => 'electronics',
                'referral_category_code' => 'TV',
                'referral_eligible' => true,
                'commission_percentage' => 10.00,
                'description' => 'Ultra HD 4K & QLED Smart Televisions',
                'image' => 'https://images.unsplash.com/photo-1593359677879-a4bb92f829d1?w=800&auto=format&fit=crop&q=80',
                'icon' => 'solar:tv-bold-duotone',
                'is_active' => true,
                'sort_order' => 1,
            ]
        );

        $catAc = Category::updateOrCreate(
            ['slug' => 'inverter-split-ac'],
            [
                'name' => 'Inverter Split AC',
                'type' => 'appliances',
                'referral_category_code' => 'AC',
                'referral_eligible' => true,
                'commission_percentage' => 10.00,
                'description' => 'Energy efficient 5-star inverter split air conditioners',
                'image' => 'https://images.unsplash.com/photo-1621905251189-08b45d6a269e?w=800&auto=format&fit=crop&q=80',
                'icon' => 'solar:wind-bold-duotone',
                'is_active' => true,
                'sort_order' => 2,
            ]
        );

        $catRef = Category::updateOrCreate(
            ['slug' => 'refrigerators'],
            [
                'name' => 'Smart Refrigerators',
                'type' => 'appliances',
                'referral_category_code' => 'REF',
                'referral_eligible' => true,
                'commission_percentage' => 10.00,
                'description' => 'Double door and side-by-side frost free refrigerators',
                'image' => 'https://images.unsplash.com/photo-1571175443880-49e1d25b2bc5?w=800&auto=format&fit=crop&q=80',
                'icon' => 'solar:fridge-bold-duotone',
                'is_active' => true,
                'sort_order' => 3,
            ]
        );

        $catWm = Category::updateOrCreate(
            ['slug' => 'washing-machines'],
            [
                'name' => 'Washing Machines',
                'type' => 'appliances',
                'referral_category_code' => 'WM',
                'referral_eligible' => true,
                'commission_percentage' => 10.00,
                'description' => 'Front load and top load AI smart washing machines',
                'image' => 'https://images.unsplash.com/photo-1626806787461-102c1bfaaea1?w=800&auto=format&fit=crop&q=80',
                'icon' => 'solar:washing-machine-bold-duotone',
                'is_active' => true,
                'sort_order' => 4,
            ]
        );

        $catEv = Category::updateOrCreate(
            ['slug' => 'electric-vehicles'],
            [
                'name' => 'Electric Vehicles',
                'type' => 'vehicles',
                'referral_category_code' => 'EV',
                'referral_eligible' => true,
                'commission_percentage' => 10.00,
                'description' => 'High speed long-range smart electric scooters',
                'image' => 'https://images.unsplash.com/photo-1558981806-ec527fa84c39?w=800&auto=format&fit=crop&q=80',
                'icon' => 'solar:scooter-bold-duotone',
                'is_active' => true,
                'sort_order' => 5,
            ]
        );

        // 2. Define 10 Products with rich galleries and HD hero images
        $productsData = [
            [
                'name' => 'NEXVIA 55-inch Ultra HD 4K Smart LED TV',
                'category_id' => $catTv->id,
                'model_code' => 'NEX-TV55-4K',
                'sku' => 'SKU-TV-001',
                'slug' => 'nexvia-55-inch-ultra-hd-4k-smart-led-tv',
                'mrp' => 50000,
                'booking_percentage' => 20,
                'stock' => 25,
                'is_featured' => true,
                'offer_text' => '⚡ 15% Instant Booking Off | Free Installation',
                'main_image' => 'https://images.unsplash.com/photo-1593359677879-a4bb92f829d1?w=1000&auto=format&fit=crop&q=80',
                'gallery' => [
                    'https://images.unsplash.com/photo-1593359677879-a4bb92f829d1?w=1000&auto=format&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1461151304267-38535e780c79?w=1000&auto=format&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1509281373149-e957c6296406?w=1000&auto=format&fit=crop&q=80',
                ],
                'warranty_info' => '3 Years Comprehensive Warranty',
                'installation_info' => 'Free standard installation & wall mount demo within 48 hours',
            ],
            [
                'name' => 'NEXVIA 65-inch QLED 4K Google TV Pro',
                'category_id' => $catTv->id,
                'model_code' => 'NEX-TV65-QLED',
                'sku' => 'SKU-TV-002',
                'slug' => 'nexvia-65-inch-qled-4k-google-tv-pro',
                'mrp' => 74999,
                'booking_percentage' => 20,
                'stock' => 18,
                'is_featured' => true,
                'offer_text' => '🔥 Free 120W Dolby Soundbar on Booking',
                'main_image' => 'https://images.unsplash.com/photo-1509281373149-e957c6296406?w=1000&auto=format&fit=crop&q=80',
                'gallery' => [
                    'https://images.unsplash.com/photo-1509281373149-e957c6296406?w=1000&auto=format&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1593784991095-a205069470b6?w=1000&auto=format&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1522869635100-9f4c5e86aa37?w=1000&auto=format&fit=crop&q=80',
                ],
                'warranty_info' => '3 Years Panel Warranty + 1 Year Complete',
                'installation_info' => 'Free Premium Wall Mount Included',
            ],
            [
                'name' => 'NEXVIA 43-inch Full HD Frameless Android TV',
                'category_id' => $catTv->id,
                'model_code' => 'NEX-TV43-FHD',
                'sku' => 'SKU-TV-003',
                'slug' => 'nexvia-43-inch-full-hd-frameless-android-tv',
                'mrp' => 27999,
                'booking_percentage' => 20,
                'stock' => 30,
                'is_featured' => true,
                'offer_text' => '⚡ Best Seller | Extra ₹1,000 Referral Benefit',
                'main_image' => 'https://images.unsplash.com/photo-1461151304267-38535e780c79?w=1000&auto=format&fit=crop&q=80',
                'gallery' => [
                    'https://images.unsplash.com/photo-1461151304267-38535e780c79?w=1000&auto=format&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1593359677879-a4bb92f829d1?w=1000&auto=format&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1574375927938-d5a98e8ffe85?w=1000&auto=format&fit=crop&q=80',
                ],
                'warranty_info' => '2 Years Brand Warranty',
                'installation_info' => 'Free Table Mount & Demonstration',
            ],
            [
                'name' => 'NEXVIA 1.5 Ton 5-Star Inverter Split AC',
                'category_id' => $catAc->id,
                'model_code' => 'NEX-AC15-5S',
                'sku' => 'SKU-AC-001',
                'slug' => 'nexvia-1-5-ton-5-star-inverter-split-ac',
                'mrp' => 41999,
                'booking_percentage' => 20,
                'stock' => 20,
                'is_featured' => true,
                'offer_text' => '🔥 10-Year Compressor Warranty + Free Standard Install',
                'main_image' => 'https://images.unsplash.com/photo-1621905251189-08b45d6a269e?w=1000&auto=format&fit=crop&q=80',
                'gallery' => [
                    'https://images.unsplash.com/photo-1621905251189-08b45d6a269e?w=1000&auto=format&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1585771724684-38269d6639fd?w=1000&auto=format&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1614633833026-072049d5c41a?w=1000&auto=format&fit=crop&q=80',
                ],
                'warranty_info' => '10 Years Compressor & 5 Years PCB Warranty',
                'installation_info' => 'Free standard installation & copper piping',
            ],
            [
                'name' => 'NEXVIA 2.0 Ton 5-Star Heavy Duty Inverter AC',
                'category_id' => $catAc->id,
                'model_code' => 'NEX-AC20-5S',
                'sku' => 'SKU-AC-002',
                'slug' => 'nexvia-2-0-ton-5-star-heavy-duty-inverter-ac',
                'mrp' => 55999,
                'booking_percentage' => 20,
                'stock' => 12,
                'is_featured' => false,
                'offer_text' => '⚡ 100% Copper Condenser with Anti-Corrosion Gold Fin',
                'main_image' => 'https://images.unsplash.com/photo-1585771724684-38269d6639fd?w=1000&auto=format&fit=crop&q=80',
                'gallery' => [
                    'https://images.unsplash.com/photo-1585771724684-38269d6639fd?w=1000&auto=format&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1621905251189-08b45d6a269e?w=1000&auto=format&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1614633833026-072049d5c41a?w=1000&auto=format&fit=crop&q=80',
                ],
                'warranty_info' => '10 Years Compressor Warranty',
                'installation_info' => 'Standard Installation Included',
            ],
            [
                'name' => 'NEXVIA 340L Frost-Free Double Door Refrigerator',
                'category_id' => $catRef->id,
                'model_code' => 'NEX-REF340-DD',
                'sku' => 'SKU-REF-001',
                'slug' => 'nexvia-340l-frost-free-double-door-refrigerator',
                'mrp' => 37999,
                'booking_percentage' => 20,
                'stock' => 15,
                'is_featured' => true,
                'offer_text' => '🔥 Inverter Linear Cooling | Auto Smart Connect',
                'main_image' => 'https://images.unsplash.com/photo-1571175443880-49e1d25b2bc5?w=1000&auto=format&fit=crop&q=80',
                'gallery' => [
                    'https://images.unsplash.com/photo-1571175443880-49e1d25b2bc5?w=1000&auto=format&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1584568694244-14fbdf83bd30?w=1000&auto=format&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1584269600464-37b1b58a9fe7?w=1000&auto=format&fit=crop&q=80',
                ],
                'warranty_info' => '10 Years Compressor Warranty + 1 Year Comprehensive',
                'installation_info' => 'Doorstep Delivery & Leveling Setup Included',
            ],
            [
                'name' => 'NEXVIA 520L Side-by-Side Inverter Refrigerator',
                'category_id' => $catRef->id,
                'model_code' => 'NEX-REF520-SBS',
                'sku' => 'SKU-REF-002',
                'slug' => 'nexvia-520l-side-by-side-inverter-refrigerator',
                'mrp' => 67999,
                'booking_percentage' => 20,
                'stock' => 9,
                'is_featured' => true,
                'offer_text' => '⚡ Multi Air Flow & Smart Digital Temperature Touchscreen',
                'main_image' => 'https://images.unsplash.com/photo-1584568694244-14fbdf83bd30?w=1000&auto=format&fit=crop&q=80',
                'gallery' => [
                    'https://images.unsplash.com/photo-1584568694244-14fbdf83bd30?w=1000&auto=format&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1571175443880-49e1d25b2bc5?w=1000&auto=format&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1584269600464-37b1b58a9fe7?w=1000&auto=format&fit=crop&q=80',
                ],
                'warranty_info' => '10 Years Inverter Compressor Warranty',
                'installation_info' => 'Free Unpacking & Demo at Delivery',
            ],
            [
                'name' => 'NEXVIA 8.5 kg Front Load AI Smart Washing Machine',
                'category_id' => $catWm->id,
                'model_code' => 'NEX-WM85-FL',
                'sku' => 'SKU-WM-001',
                'slug' => 'nexvia-8-5-kg-front-load-ai-smart-washing-machine',
                'mrp' => 35999,
                'booking_percentage' => 20,
                'stock' => 14,
                'is_featured' => false,
                'offer_text' => '🔥 Built-in Heater & Allergy Steam Care',
                'main_image' => 'https://images.unsplash.com/photo-1626806787461-102c1bfaaea1?w=1000&auto=format&fit=crop&q=80',
                'gallery' => [
                    'https://images.unsplash.com/photo-1626806787461-102c1bfaaea1?w=1000&auto=format&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1582735689369-4fe89db7114c?w=1000&auto=format&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1517677208171-0bc6725a3e60?w=1000&auto=format&fit=crop&q=80',
                ],
                'warranty_info' => '10 Years Motor Warranty + 3 Years Machine',
                'installation_info' => 'Free In-Home Demo & Inlet Pipe Fitting',
            ],
            [
                'name' => 'NEXVIA 7.5 kg Top Load Fully Automatic Washer',
                'category_id' => $catWm->id,
                'model_code' => 'NEX-WM75-TL',
                'sku' => 'SKU-WM-002',
                'slug' => 'nexvia-7-5-kg-top-load-fully-automatic-washer',
                'mrp' => 21999,
                'booking_percentage' => 20,
                'stock' => 22,
                'is_featured' => false,
                'offer_text' => '⚡ Smart Inverter Turbo Drum | Soft Closing Glass Lid',
                'main_image' => 'https://images.unsplash.com/photo-1582735689369-4fe89db7114c?w=1000&auto=format&fit=crop&q=80',
                'gallery' => [
                    'https://images.unsplash.com/photo-1582735689369-4fe89db7114c?w=1000&auto=format&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1626806787461-102c1bfaaea1?w=1000&auto=format&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1517677208171-0bc6725a3e60?w=1000&auto=format&fit=crop&q=80',
                ],
                'warranty_info' => '10 Years Smart Inverter Motor Warranty',
                'installation_info' => 'Free Demonstration & Inlet/Drain Adapter Set',
            ],
            [
                'name' => 'NEXVIA NX-1 High-Speed Smart Electric Scooter',
                'category_id' => $catEv->id,
                'model_code' => 'NEX-EV-NX1',
                'sku' => 'SKU-EV-001',
                'slug' => 'nexvia-nx-1-high-speed-smart-electric-scooter',
                'mrp' => 97999,
                'booking_percentage' => 20,
                'stock' => 10,
                'is_featured' => true,
                'offer_text' => '⚡ 120 km Range | 3.5 kWh Battery + Portable Fast Charger',
                'main_image' => 'https://images.unsplash.com/photo-1558981806-ec527fa84c39?w=1000&auto=format&fit=crop&q=80',
                'gallery' => [
                    'https://images.unsplash.com/photo-1558981806-ec527fa84c39?w=1000&auto=format&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1568772585407-9361f9bf3a87?w=1000&auto=format&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1508974239320-0a029497e820?w=1000&auto=format&fit=crop&q=80',
                ],
                'warranty_info' => '3 Years Comprehensive Battery & Motor Warranty',
                'installation_info' => 'Free Home Delivery & Helmet + Fast Charger Included',
            ],
        ];

        foreach ($productsData as $p) {
            $mrp = $p['mrp'];
            $pct = $p['booking_percentage'];
            $booking = $mrp * ($pct / 100);
            $balance = $mrp - $booking;

            Product::updateOrCreate(
                ['slug' => $p['slug']],
                [
                    'category_id' => $p['category_id'],
                    'name' => $p['name'],
                    'model_code' => $p['model_code'],
                    'sku' => $p['sku'],
                    'mrp' => $mrp,
                    'booking_percentage' => $pct,
                    'booking_amount' => $booking,
                    'balance_amount' => $balance,
                    'eligible_referral_value' => $mrp,
                    'referral_eligible' => true,
                    'self_dealer_eligible' => true,
                    'stock' => $p['stock'],
                    'main_image' => $p['main_image'],
                    'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                    'offer_text' => $p['offer_text'],
                    'gallery' => $p['gallery'],
                    'key_features' => [
                        'Nexvia Smart AI Engine',
                        'Eco-Friendly Energy Saver',
                        'Instant Referral Points Eligible',
                        '60-Day Easy Balance Payment Window',
                    ],
                    'specs' => [
                        'Brand' => 'NEXVIA',
                        'Model' => $p['model_code'],
                        'Warranty' => $p['warranty_info'],
                        'Delivery' => 'Express 3-5 Working Days',
                    ],
                    'warranty_info' => $p['warranty_info'],
                    'installation_info' => $p['installation_info'],
                    'delivery_info' => 'Express 3-5 Working Days Delivery Across India',
                    'is_featured' => $p['is_featured'],
                    'status' => 'active',
                ]
            );
        }
    }
}
