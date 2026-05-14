<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UmkmProduct extends Model
{
    use HasFactory;

    protected $table = 'umkm_products';

    protected $fillable = [
        'umkm_id',
        'nama_produk',
        'harga',
        'satuan_harga',
        'deskripsi',
        'foto_produk',
        'is_available',
        'stock',
        'sku',
        'weight',
        'is_preorder',
        'discount_price',
        'discount_percentage',
        'variations',
    ];

    protected $casts = [
        'is_available' => 'boolean',
        'is_preorder' => 'boolean',
        'harga' => 'decimal:2',
        'discount_price' => 'decimal:2',
        'variations' => 'array',
    ];

    public function umkm()
    {
        return $this->belongsTo(Umkm::class, 'umkm_id');
    }
}
