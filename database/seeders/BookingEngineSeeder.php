<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BookingEngineSetting;

class BookingEngineSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['key' => 'default_booking_percentage', 'value' => '20', 'description' => 'Default Upfront Booking Percentage (%)'],
            ['key' => 'balance_period_days', 'value' => '60', 'description' => 'Default Balance Payment Window (Days)'],
            ['key' => 'transfer_allowed', 'value' => '1', 'description' => 'Allow Booking Receipt Ownership Transfer (1=Yes, 0=No)'],
            ['key' => 'product_eligibility', 'value' => 'all_active', 'description' => 'Product Eligibility Rules for Booking'],
            ['key' => 'reminder_schedule_days', 'value' => '30,15,7,3,0', 'description' => 'Automated Customer Reminder Schedule (Days Remaining)'],
            ['key' => 'expiry_handling', 'value' => 'cancel_and_credit', 'description' => 'Handling when 60-day deadline expires'],
            ['key' => 'booking_terms_version', 'value' => 'v1.2', 'description' => 'Current Active Booking Terms Declaration Version'],
        ];

        foreach ($settings as $setting) {
            BookingEngineSetting::updateOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
