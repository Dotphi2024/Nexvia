<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class Customer extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table    = 'users';
    protected $guard_name = 'web'; // uses the 'users' provider

    protected $fillable = [
        'name',
        'email',
        'phone',
        'otp',
        'otp_expires_at',
        'phone_verified_at',
        'api_token',
        'fcm_token',
        'current_latitude',
        'current_longitude',
        'password',
        'profile_pic',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'otp',
        'otp_expires_at',
        'phone_verified_at',
        'email_verified_at',
        'api_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at'  => 'datetime',
            'phone_verified_at'  => 'datetime',
            'otp_expires_at'     => 'datetime',
            'password'           => 'hashed',
        ];
    }

    /**
     * Generate a fresh 6-digit OTP and set expiry (10 minutes).
     */
    public function generateOtp(): string
    {
        $otp = str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);

        $this->update([
            'otp'            => $otp,
            'otp_expires_at' => now()->addMinutes(10),
        ]);

        return $otp;
    }

    /**
     * Generate a fresh API token and persist it.
     */
    public function generateApiToken(): string
    {
        $token = Str::random(60);
        $this->update(['api_token' => $token]);
        return $token;
    }

    /**
     * Check if the given OTP is valid and not expired.
     */
    public function isOtpValid(string $otp): bool
    {
        return $this->otp === $otp
            && $this->otp_expires_at
            && $this->otp_expires_at->isFuture();
    }

    /**
     * Mark phone as verified and clear OTP.
     */
    public function markPhoneVerified(): void
    {
        $this->update([
            'phone_verified_at' => now(),
            'otp'               => null,
            'otp_expires_at'    => null,
        ]);
    }
}
