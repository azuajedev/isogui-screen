<?php

namespace Database\Seeders;

use App\Models\Mockup;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MockupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $mockups = [
            [
                'name' => 'iPhone 14 Pro Frame',
                'category' => 'device-frames',
                'filename' => 'iphone-14-pro-frame.png',
                'thumbnail' => 'iphone-14-pro-frame-thumb.png',
                'width' => 1179,
                'height' => 2556,
                'is_active' => true,
            ],
            [
                'name' => 'MacBook Pro 16" Frame',
                'category' => 'device-frames',
                'filename' => 'macbook-pro-16-frame.png',
                'thumbnail' => 'macbook-pro-16-frame-thumb.png',
                'width' => 3456,
                'height' => 2234,
                'is_active' => true,
            ],
            [
                'name' => 'iPad Pro 12.9" Frame',
                'category' => 'device-frames',
                'filename' => 'ipad-pro-frame.png',
                'thumbnail' => 'ipad-pro-frame-thumb.png',
                'width' => 2048,
                'height' => 2732,
                'is_active' => true,
            ],
            [
                'name' => 'Gradient Background 1',
                'category' => 'backgrounds',
                'filename' => 'gradient-bg-1.png',
                'thumbnail' => 'gradient-bg-1-thumb.png',
                'width' => 1920,
                'height' => 1080,
                'is_active' => true,
            ],
            [
                'name' => 'Gradient Background 2',
                'category' => 'backgrounds',
                'filename' => 'gradient-bg-2.png',
                'thumbnail' => 'gradient-bg-2-thumb.png',
                'width' => 1920,
                'height' => 1080,
                'is_active' => true,
            ],
            [
                'name' => 'UI Element - Button Set',
                'category' => 'ui-elements',
                'filename' => 'button-set.png',
                'thumbnail' => 'button-set-thumb.png',
                'width' => 800,
                'height' => 600,
                'is_active' => true,
            ],
            [
                'name' => 'UI Element - Navigation Bar',
                'category' => 'ui-elements',
                'filename' => 'navbar.png',
                'thumbnail' => 'navbar-thumb.png',
                'width' => 1920,
                'height' => 80,
                'is_active' => true,
            ],
        ];

        foreach ($mockups as $mockup) {
            Mockup::create($mockup);
        }
    }
}
