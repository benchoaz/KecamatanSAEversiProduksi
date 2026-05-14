<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UmkmOrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'price',
        'variation',
    ];

    public function order()
    {
        return $this->belongsTo(UmkmOrder::class, 'order_id');
    }

    public function product()
    {
        return $this->belongsTo(UmkmProduct::class, 'product_id');
    }
}
