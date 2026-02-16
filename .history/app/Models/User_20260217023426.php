<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'is_active'
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Roles relationship
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_user')
                    ->withTimestamps();
    }

    /**
     * Check if user has a specific role
     */
    public function hasRole(string $role): bool
    {
        return $this->roles()->where('name', $role)->exists();
    }

    /**
     * Check if user has any of the given roles
     */
    public function hasAnyRole(array $roles): bool
    {
        return $this->roles()->whereIn('name', $roles)->exists();
    }

    /**
     * Check if user has all given roles
     */
    public function hasAllRoles(array $roles): bool
    {
        $userRoles = $this->roles()->pluck('name')->toArray();
        return empty(array_diff($roles, $userRoles));
    }

    /**
     * Get all permissions from user's roles
     */
    public function getPermissionsAttribute()
    {
        return $this->roles->flatMap(function ($role) {
            return $role->permissions;
        })->unique('id');
    }

    /**
     * Check if user has a specific permission
     */
    public function hasPermission(string $permission): bool
    {
        return $this->permissions->contains('name', $permission);
    }

    /**
     * Alias for hasPermission (used in your code)
     */
    public function canPerm(string $permission): bool
    {
        return $this->hasPermission($permission);
    }

    /**
     * Check if user is admin (bypass all permissions)
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }
}