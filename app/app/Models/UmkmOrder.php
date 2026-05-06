<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UmkmOrder extends Model
{
    protected $fillable = [
        'umkm_id',
        'customer_name',
        'customer_whatsapp',
        'total_price',
        'shipping_address',
        'shipping_method',
        'status',
        'tracking_number',
        'notes',
        'payment_status',
        'payment_method'
    ];

    public function umkm()
    {
        return $this->belongsTo(Umkm::class);
    }

    public function items()
    {
        return $this->hasMany(UmkmOrderItem::class, 'order_id');
    }
}
