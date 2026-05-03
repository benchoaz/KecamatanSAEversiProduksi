<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiMemory extends Model
{
    protected $fillable = ['phone_number', 'user_name', 'context', 'metadata'];
    
    protected $casts = [
        'metadata' => 'array',
    ];
}
