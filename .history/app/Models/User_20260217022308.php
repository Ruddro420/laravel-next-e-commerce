<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name','email','password','is_active',
    ];

    protected $hidden = [
        'password','remember_token',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Many-to-many: users <-> roles
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_user');
    }

    public function hasRole(string $roleName): bool
    {
        return $this->roles()->where('name', $roleName)->exists();
    }

    /**
     * ✅ Correct permission check (uses permissions.name)
     * Example: canPerm('products.view')
     */
    public function canPerm(string $permName): bool
    {
        // admin bypass
        if ($this->hasRole('admin')) return true;

        return $this->roles()
            ->whereHas('permissions', function ($q) use ($permName) {
                $q->where('name', $permName); // ✅ NOT "key"
            })
            ->exists();
    }
}
