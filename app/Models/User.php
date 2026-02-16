<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = ['name','email','password','is_active'];
    protected $hidden = ['password','remember_token'];
    protected $casts = ['is_active' => 'boolean'];

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    public function hasRole(string $role): bool
    {
        return $this->roles()->where('name', $role)->exists();
    }

    public function permissions()
    {
        // permissions via roles
        return Permission::query()->whereHas('roles', function($q){
            $q->whereIn('roles.id', $this->roles()->pluck('roles.id'));
        });
    }

    public function canPerm(string $permKey): bool
    {
        return $this->roles()
            ->whereHas('permissions', fn($q) => $q->where('name', $permKey))
            ->exists();
    }
}
