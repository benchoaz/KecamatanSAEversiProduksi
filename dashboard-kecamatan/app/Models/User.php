<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, \App\Traits\Auditable;

    protected $fillable = [
        'nama_lengkap',
        'username',
        'password',
        'role_id',
        'desa_id',
        'status',
        'last_login',
        'foto',
        'no_hp',
    ];

    const STATUS_AKTIF = 'aktif';
    const STATUS_NONAKTIF = 'nonaktif';

    // Role Constants
    const ROLE_SUPER_ADMIN = 'Super Admin';
    const ROLE_OPERATOR_KECAMATAN = 'Operator Kecamatan';
    const ROLE_OPERATOR_DESA = 'Operator Desa';
    const ROLE_VERIFIKATOR = 'Verifikator';
    const ROLE_AUDITOR = 'Auditor';

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'last_login' => 'datetime',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function desa()
    {
        return $this->belongsTo(Desa::class);
    }

    // Helper to check role
    public function hasRole($roleName)
    {
        return $this->role && $this->role->nama_role === $roleName;
    }

    public function isSuperAdmin()
    {
        return $this->hasRole('Super Admin');
    }

    public function isOperatorKecamatan()
    {
        return $this->hasRole('Operator Kecamatan');
    }

    public function isOperatorDesa()
    {
        return $this->hasRole(self::ROLE_OPERATOR_DESA);
    }

    public function isVerifikator()
    {
        return $this->hasRole(self::ROLE_VERIFIKATOR);
    }

    public function isAuditor()
    {
        return $this->hasRole(self::ROLE_AUDITOR);
    }

    public function submissions()
    {
        return $this->hasMany(Submission::class, 'submitted_by');
    }

    // Relationships to listing owners
    public function umkms()
    {
        return $this->hasMany(Umkm::class, 'owner_user_id');
    }

    public function lokers()
    {
        return $this->hasMany(Loker::class, 'owner_user_id');
    }

    public function jasas()
    {
        return $this->hasMany(UmkmLocal::class, 'owner_user_id')->where('module', UmkmLocal::MODULE_JASA);
    }

    public function umkmLocals()
    {
        return $this->hasMany(UmkmLocal::class, 'owner_user_id');
    }
}
