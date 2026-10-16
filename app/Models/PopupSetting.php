<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PopupSetting extends Model
{
    use HasFactory;

    protected $table = 'popup_settings';

    protected $fillable = [
        'is_enabled',
        'delay_seconds',
        'title',
        'subtitle',
        'button_text',
        'success_message',
        'reappear_hours',
    ];

    protected $casts = [
        'is_enabled'     => 'boolean',
        'delay_seconds'  => 'integer',
        'reappear_hours' => 'integer',
    ];

    /**
     * Get or create global popup configuration singleton
     */
    public static function getSettings(): self
    {
        return self::firstOrCreate(
            ['id' => 1],
            [
                'is_enabled'      => true,
                'delay_seconds'   => 7, // 5 to 10 seconds range
                'title'           => 'Get Exclusive Offers & Instant Callback',
                'subtitle'        => 'Enter your name and mobile number. Our product specialist will call you with exclusive pricing and full product details.',
                'button_text'     => 'Request Callback Now',
                'success_message' => 'Thank you! We have received your request. Our specialist will call you shortly.',
                'reappear_hours'  => 24,
            ]
        );
    }
}
