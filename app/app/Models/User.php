<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens, HasFactory, Notifiable, \App\Traits\Auditable, HasRoles;

    /**
     * Determine if the user can access the given Filament panel.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        if ($panel->getId() === 'admin') {
            return $this->isSuperAdmin() || 
                   $this->isOperatorKecamatan() || 
                   $this->isVerifikator() || 
                   $this->isModuleAdmin();
        }

        if ($panel->getId() === 'desa') {
            return $this->isOperatorDesa() || $this->isSuperAdmin();
        }

        return false;
    }

    // Always load role with user to prevent N+1 queries
    protected $with = ['role'];

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
    const ROLE_ADMIN_PELAYANAN = 'pelayanan_admin';
    const ROLE_TRANTIBUM_ADMIN = 'trantibum_admin';
    const ROLE_UMKM_ADMIN = 'umkm_admin';
    const ROLE_LOKER_ADMIN = 'loker_admin';

    public function isModuleAdmin()
    {
        return in_array($this->role->nama_role ?? '', [
            self::ROLE_ADMIN_PELAYANAN,
            self::ROLE_TRANTIBUM_ADMIN,
            self::ROLE_UMKM_ADMIN,
            self::ROLE_LOKER_ADMIN,
            'Admin Pelayanan'
        ]);
    }

    public function isAdminPelayanan()
    {
        return in_array($this->role->nama_role ?? '', ['pelayanan_admin', 'Admin Pelayanan']);
    }

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'last_login' => 'datetime',
    ];

    // Cache role name to avoid repeated lookups
    protected $appends = ['role_name'];

    public function getRoleNameAttribute()
    {
        return $this->role->name ?? $this->role->nama_role ?? null;
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function desa()
    {
        return $this->belongsTo(Desa::class);
    }

    // Helper to check role
    public function hasRole($roleName, $guard = null)
    {
        // Try Spatie's hasRole first if trait is used
        if (method_exists($this, 'hasPermissionTo')) {
            try {
                if ($this->roles()->where('name', $roleName)->exists()) {
                    return true;
                }
            } catch (\Exception $e) {}
        }

        // Fallback to legacy role_id check
        return ($this->role->name ?? $this->role->nama_role ?? null) === $roleName;
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
