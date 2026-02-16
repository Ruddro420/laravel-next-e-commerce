<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'group'
    ];

    /**
     * Roles relationship
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'permission_role')
                    ->withTimestamps();
    }

    /**
     * Get permissions grouped for UI
     */
    public static function getGrouped()
    {
        return self::all()->groupBy('group');
    }
}