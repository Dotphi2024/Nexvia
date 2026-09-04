<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerCategoryProgress extends Model
{
    use HasFactory;

    protected $table = 'customer_category_progress';

    protected $fillable = [
        'user_id',
        'category_id',
        'referral_count',
        'current_tier_percentage',
    ];

    protected $casts = [
        'current_tier_percentage' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(Customer::class, 'user_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
