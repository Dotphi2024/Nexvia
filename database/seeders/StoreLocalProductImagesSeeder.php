<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Str;

class StoreLocalProductImagesSeeder extends Seeder
{
    public function run()
    {
        // 1. Ensure target folders exist in public/
        $catDir = public_path('uploads/categories');
        $prodDir = public_path('uploads/products');
        $defaultDir = public_path('images');

        if (!file_exists($catDir)) {
            mkdir($catDir, 0755, true);
        }
        if (!file_exists($prodDir)) {
            mkdir($prodDir, 0755, true);
        }
        if (!file_exists($defaultDir)) {
            mkdir($defaultDir, 0755, true);
        }

        // 2. Generate clean no-image.png placeholder via GD
        $pngPath = public_path('images/no-image.png');
        if (!file_exists($pngPath) || filesize($pngPath) < 100) {
            $w = 600;
            $h = 450;
            $im = imagecreatetruecolor($w, $h);
            $bg = imagecolorallocate($im, 243, 244, 246); // Light slate #f3f4f6
            imagefilledrectangle($im, 0, 0, $w, $h, $bg);

            $border = imagecolorallocate($im, 209, 213, 219); // #d1d5db
            imagerectangle($im, 10, 10, $w - 11, $h - 11, $border);

            // Placeholder box
            $box = imagecolorallocate($im, 229, 231, 235); // #e5e7eb
            imagefilledrectangle($im, 220, 130, 380, 240, $box);

            // Placeholder circle
            $circle = imagecolorallocate($im, 156, 163, 175); // #9ca3af
            imagefilledellipse($im, 260, 165, 30, 30, $circle);

            // Texts
            $titleColor = imagecolorallocate($im, 55, 65, 81); // #374151
            imagestring($im, 5, 215, 270, "NO IMAGE AVAILABLE", $titleColor);

            $subColor = imagecolorallocate($im, 107, 114, 128); // #6b7280
            imagestring($im, 3, 210, 295, "NEXVIA SMART CATALOG", $subColor);

            imagepng($im, $pngPath);
            imagedestroy($im);
            echo "Created public/images/no-image.png\n";
        }

        // 3. Category image mapping
        $catImages = [
            'smart-led-tv' => 'https://images.unsplash.com/photo-1593359677879-a4bb92f829d1?w=800&auto=format&fit=crop&q=80',
            'inverter-split-ac' => 'https://images.unsplash.com/photo-1621905251189-08b45d6a269e?w=800&auto=format&fit=crop&q=80',
            'refrigerators' => 'https://images.unsplash.com/photo-1571175443880-49e1d25b2bc5?w=800&auto=format&fit=crop&q=80',
            'washing-machines' => 'https://images.unsplash.com/photo-1626806787461-102c1bfaaea1?w=800&auto=format&fit=crop&q=80',
            'electric-vehicles' => 'https://images.unsplash.com/photo-1558981806-ec527fa84c39?w=800&auto=format&fit=crop&q=80',
        ];

        foreach ($catImages as $slug => $remoteUrl) {
            $cat = Category::where('slug', $slug)->first();
            if (!$cat) continue;

            $fileName = $slug . '.jpg';
            $localRelPath = 'uploads/categories/' . $fileName;
            $localFullPath = public_path($localRelPath);

            if (!file_exists($localFullPath) || filesize($localFullPath) < 1000) {
                echo "Downloading category image for {$slug}...\n";
                $content = @file_get_contents($remoteUrl);
                if ($content) {
                    file_put_contents($localFullPath, $content);
                }
            }

            if (file_exists($localFullPath)) {
                $cat->image = $localRelPath;
                $cat->save();
            }
        }

        // 4. Product images and galleries mapping
        $productImages = [
            'nexvia-55-inch-ultra-hd-4k-smart-led-tv' => [
                'main' => 'https://images.unsplash.com/photo-1593359677879-a4bb92f829d1?w=800&auto=format&fit=crop&q=80',
                'gallery' => [
                    'https://images.unsplash.com/photo-1593359677879-a4bb92f829d1?w=800&auto=format&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1461151304267-38535e780c79?w=800&auto=format&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1509281373149-e957c6296406?w=800&auto=format&fit=crop&q=80',
                ]
            ],
            'nexvia-65-inch-qled-4k-google-tv-pro' => [
                'main' => 'https://images.unsplash.com/photo-1509281373149-e957c6296406?w=800&auto=format&fit=crop&q=80',
                'gallery' => [
                    'https://images.unsplash.com/photo-1509281373149-e957c6296406?w=800&auto=format&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1593784991095-a205069470b6?w=800&auto=format&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1522869635100-9f4c5e86aa37?w=800&auto=format&fit=crop&q=80',
                ]
            ],
            'nexvia-43-inch-full-hd-frameless-android-tv' => [
                'main' => 'https://images.unsplash.com/photo-1461151304267-38535e780c79?w=800&auto=format&fit=crop&q=80',
                'gallery' => [
                    'https://images.unsplash.com/photo-1461151304267-38535e780c79?w=800&auto=format&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1593359677879-a4bb92f829d1?w=800&auto=format&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1574375927938-d5a98e8ffe85?w=800&auto=format&fit=crop&q=80',
                ]
            ],
            'nexvia-1-5-ton-5-star-inverter-split-ac' => [
                'main' => 'https://images.unsplash.com/photo-1621905251189-08b45d6a269e?w=800&auto=format&fit=crop&q=80',
                'gallery' => [
                    'https://images.unsplash.com/photo-1621905251189-08b45d6a269e?w=800&auto=format&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1585771724684-38269d6639fd?w=800&auto=format&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1614633833026-072049d5c41a?w=800&auto=format&fit=crop&q=80',
                ]
            ],
            'nexvia-2-0-ton-5-star-heavy-duty-inverter-ac' => [
                'main' => 'https://images.unsplash.com/photo-1585771724684-38269d6639fd?w=800&auto=format&fit=crop&q=80',
                'gallery' => [
                    'https://images.unsplash.com/photo-1585771724684-38269d6639fd?w=800&auto=format&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1621905251189-08b45d6a269e?w=800&auto=format&fit=crop&q=80',
                ]
            ],
            'nexvia-340l-frost-free-double-door-refrigerator' => [
                'main' => 'https://images.unsplash.com/photo-1571175443880-49e1d25b2bc5?w=800&auto=format&fit=crop&q=80',
                'gallery' => [
                    'https://images.unsplash.com/photo-1571175443880-49e1d25b2bc5?w=800&auto=format&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1584568694244-14fbdf83bd30?w=800&auto=format&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1584269600464-37b1b58a9fe7?w=800&auto=format&fit=crop&q=80',
                ]
            ],
            'nexvia-520l-side-by-side-inverter-refrigerator' => [
                'main' => 'https://images.unsplash.com/photo-1584568694244-14fbdf83bd30?w=800&auto=format&fit=crop&q=80',
                'gallery' => [
                    'https://images.unsplash.com/photo-1584568694244-14fbdf83bd30?w=800&auto=format&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1571175443880-49e1d25b2bc5?w=800&auto=format&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1584269600464-37b1b58a9fe7?w=800&auto=format&fit=crop&q=80',
                ]
            ],
            'nexvia-8-5-kg-front-load-ai-smart-washing-machine' => [
                'main' => 'https://images.unsplash.com/photo-1626806787461-102c1bfaaea1?w=800&auto=format&fit=crop&q=80',
                'gallery' => [
                    'https://images.unsplash.com/photo-1626806787461-102c1bfaaea1?w=800&auto=format&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1582735689369-4fe89db7114c?w=800&auto=format&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1517677208171-0bc6725a3e60?w=800&auto=format&fit=crop&q=80',
                ]
            ],
            'nexvia-7-5-kg-top-load-fully-automatic-washer' => [
                'main' => 'https://images.unsplash.com/photo-1582735689369-4fe89db7114c?w=800&auto=format&fit=crop&q=80',
                'gallery' => [
                    'https://images.unsplash.com/photo-1582735689369-4fe89db7114c?w=800&auto=format&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1626806787461-102c1bfaaea1?w=800&auto=format&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1517677208171-0bc6725a3e60?w=800&auto=format&fit=crop&q=80',
                ]
            ],
            'nexvia-nx-1-high-speed-smart-electric-scooter' => [
                'main' => 'https://images.unsplash.com/photo-1558981806-ec527fa84c39?w=800&auto=format&fit=crop&q=80',
                'gallery' => [
                    'https://images.unsplash.com/photo-1558981806-ec527fa84c39?w=800&auto=format&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1568772585407-9361f9bf3a87?w=800&auto=format&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1508974239320-0a029497e820?w=800&auto=format&fit=crop&q=80',
                ]
            ],
        ];

        foreach ($productImages as $slug => $data) {
            $product = Product::where('slug', $slug)->first();
            if (!$product) continue;

            $slugPrefix = str_replace(['-', ' '], '_', $slug);
            $slugPrefix = substr($slugPrefix, 0, 30);

            // Main image
            $mainFileName = $slugPrefix . '_main.jpg';
            $mainRelPath = 'uploads/products/' . $mainFileName;
            $mainFullPath = public_path($mainRelPath);

            if (!file_exists($mainFullPath) || filesize($mainFullPath) < 1000) {
                echo "Downloading main image for {$slug}...\n";
                $content = @file_get_contents($data['main']);
                if ($content) {
                    file_put_contents($mainFullPath, $content);
                }
            }

            if (file_exists($mainFullPath)) {
                $product->main_image = $mainRelPath;
            }

            // Gallery images
            $localGallery = [];
            foreach ($data['gallery'] as $idx => $gUrl) {
                $gFileName = $slugPrefix . '_g' . ($idx + 1) . '.jpg';
                $gRelPath = 'uploads/products/' . $gFileName;
                $gFullPath = public_path($gRelPath);

                if (!file_exists($gFullPath) || filesize($gFullPath) < 1000) {
                    $content = @file_get_contents($gUrl);
                    if ($content) {
                        file_put_contents($gFullPath, $content);
                    }
                }

                if (file_exists($gFullPath)) {
                    $localGallery[] = $gRelPath;
                }
            }

            if (!empty($localGallery)) {
                $product->gallery = $localGallery;
            }

            $product->save();
        }

        echo "Local image storage and database sync complete!\n";
    }
}
