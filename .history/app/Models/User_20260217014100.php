<?php 

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name','email','password','is_active',
    ];

    protected $hidden = [
        'password','remember_token',
    ];

    // ✅ Relationship: user roles (pivot role_user)
    public function roles()
    {
        return $this->belongsToMany(\App\Models\Role::class, 'role_user');
    }

    // ✅ Check role
    public function hasRole(string $roleName): bool
    {
        return $this->roles()->where('name', $roleName)->exists();
    }

    // ✅ Check permission (your DB uses permissions.name, not key)
    public function canPerm(string $permissionKey): bool
    {
        // Admin bypass
        if ($this->hasRole('admin')) return true;

        return $this->roles()
            ->whereHas('permissions', function ($q) use ($permissionKey) {
                $q->where('name', $permissionKey); // ✅ correct column
            })
            ->exists();
    }
}
