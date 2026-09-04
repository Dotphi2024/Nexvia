<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryStock extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'sku',
        'serial_number',
        'warehouse_name',
        'stock_qty',
        'reserved_qty',
        'sold_qty',
        'low_stock_threshold',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
