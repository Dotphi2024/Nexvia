<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'subtitle',
        'badge_text',
        'image',
        'banner_type',
        'target_type',
        'target_id',
        'link_url',
        'button_text',
        'sort_order',
        'is_active',
        'clicks_count',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'is_active'    => 'boolean',
        'sort_order'   => 'integer',
        'clicks_count' => 'integer',
        'start_date'   => 'date',
        'end_date'     => 'date',
    ];

    protected $appends = [
        'image_url',
    ];

    /**
     * Get full image URL accessor
     */
    public function getImageUrlAttribute()
    {
        if (empty($this->image)) {
            return null;
        }

        if (str_starts_with($this->image, 'http://') || str_starts_with($this->image, 'https://')) {
            return $this->image;
        }

        return asset($this->image);
    }

    /**
     * Associated category (if target_type == 'category')
     */
    public function category()
    {
        return $this->belongsTo(Category::class, 'target_id');
    }

    /**
     * Associated product (if target_type == 'product')
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'target_id');
    }

    /**
     * Scope for active banners
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope ordered by sort order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc')->orderBy('id', 'desc');
    }
}
