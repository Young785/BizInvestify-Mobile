<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    protected $fillable = [
        'name',
        'display_name',
        'description',
        'level',
        'is_active',
        'is_default',
        'guard_name'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'level' => 'integer',
    ];

    /**
     * Get the number of users with this role
     */
    public function getUsersCountAttribute()
    {
        return $this->users()->count();
    }

    /**
     * Scope for active roles
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for default roles
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }
}