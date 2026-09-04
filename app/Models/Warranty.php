<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Warranty extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_id',
        'booking_id',
        'serial_number',
        'purchase_date',
        'warranty_start',
        'warranty_end',
        'status',
        'warranty_document_path',
    ];

    protected $casts = [
        'purchase_date'  => 'date',
        'warranty_start' => 'date',
        'warranty_end'   => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(Customer::class, 'user_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}
