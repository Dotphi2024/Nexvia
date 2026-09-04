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
    protected $guard_name = 'web';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'pincode',
        'city',
        'state',
        'dob',
        'gst_number',
        'wallet_balance',
        'referral_code',
        'referred_by_id',
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
            'dob'                => 'date',
            'password'           => 'hashed',
            'wallet_balance'     => 'decimal:2',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($customer) {
            if (empty($customer->referral_code)) {
                $customer->referral_code = 'NEX-' . strtoupper(Str::random(6));
            }
        });
    }

    public function generateOtp(): string
    {
        $otp = str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);

        $this->update([
            'otp'            => $otp,
            'otp_expires_at' => now()->addMinutes(10),
        ]);

        return $otp;
    }

    public function generateApiToken(): string
    {
        $token = Str::random(60);
        $this->update(['api_token' => $token]);
        return $token;
    }

    public function isOtpValid(string $otp): bool
    {
        return $this->otp === $otp
            && $this->otp_expires_at
            && $this->otp_expires_at->isFuture();
    }

    public function markPhoneVerified(): void
    {
        $this->update([
            'phone_verified_at' => now(),
            'otp'               => null,
            'otp_expires_at'    => null,
        ]);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'user_id');
    }

    public function referredBy()
    {
        return $this->belongsTo(Customer::class, 'referred_by_id');
    }

    public function referrals()
    {
        return $this->hasMany(Customer::class, 'referred_by_id');
    }

    public function walletTransactions()
    {
        return $this->hasMany(WalletTransaction::class, 'user_id');
    }

    public function addresses()
    {
        return $this->hasMany(UserAddress::class, 'user_id');
    }
}
