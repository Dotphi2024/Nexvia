<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Services\FcmService;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class SendCustomerPushNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notify:customer {target : Customer ID, email, or name} {title? : Notification Title} {message? : Notification Message}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send an in-app and FCM push notification to a specific customer';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $target = $this->argument('target');

        $customer = is_numeric($target)
            ? Customer::find($target)
            : Customer::where('email', 'LIKE', "%{$target}%")
                ->orWhere('name', 'LIKE', "%{$target}%")
                ->orWhere('phone', 'LIKE', "%{$target}%")
                ->first();

        if (!$customer) {
            $this->error("Customer not found matching '{$target}'.");
            return 1;
        }

        $title = $this->argument('title') ?? "🎉 Test Notification for {$customer->name}";
        $message = $this->argument('message') ?? "Hello {$customer->name}! Your Nexvia notifications and order alerts are now active.";

        $this->info("Customer matched: #{$customer->id} - {$customer->name} ({$customer->email})");

        // 1. Create In-App Notification in DB
        $notificationId = (string) Str::uuid();
        \DB::table('notifications')->insert([
            'id'              => $notificationId,
            'type'            => 'App\Notifications\CustomerAlertNotification',
            'notifiable_type' => Customer::class,
            'notifiable_id'   => $customer->id,
            'data'            => json_encode([
                'title'   => $title,
                'message' => $message,
                'type'    => 'order',
                'route'   => '/notifications',
            ]),
            'read_at'         => null,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        $this->info("✅ In-app database notification saved (ID: {$notificationId}).");

        // 2. Push Notification via FCM if token is present
        if (!empty($customer->fcm_token)) {
            $this->info("Found FCM Token: " . substr($customer->fcm_token, 0, 20) . "...");
            $sent = FcmService::sendNotification(
                $customer->fcm_token,
                $title,
                $message,
                ['type' => 'order', 'route' => '/notifications']
            );

            if ($sent) {
                $this->info("🚀 Push notification dispatched successfully via FCM v1 API.");
            } else {
                $this->warn("⚠️ Push notification dispatch returned false (check storage/logs/laravel.log).");
            }
        } else {
            $this->warn("ℹ️ Customer does not have an FCM device token registered yet.");
            $this->line("   When the user logs in to the mobile app, their device token is automatically registered.");
        }

        return 0;
    }
}
