<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    use HasFactory;

    protected $fillable = ['name', 'guard_name'];

    /**
     * Compatibility accessor for legacy code expecting 'nama_role'.
     */
    public function getNamaRoleAttribute()
    {
        return $this->name;
    }
}
