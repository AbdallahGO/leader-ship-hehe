<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * User Model
 * 
 * Represents a user in the system with authentication support.
 * Relationships: notifications, activityLogs, sessions, roles, permissions
 */
class User extends Authenticatable
{
    use HasFactory, HasApiTokens, Notifiable;

    /**
     * The table associated with the model.
     */
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'role',
        'is_banned',
        'is_suspended',
        'banned_at',
        'suspended_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'banned_at' => 'datetime',
        'suspended_at' => 'datetime',
        'is_banned' => 'boolean',
        'is_suspended' => 'boolean',
    ];

    /**
     * Get the user's notifications
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Get the user's activity logs
     */
    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    /**
     * Get the user's sessions
     */
    public function sessions()
    {
        return $this->hasMany(Session::class);
    }

    /**
     * Get roles assigned to this user
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            Role::class,
            'user_roles',
            'user_id',
            'role_id'
        )->withTimestamps();
    }

    /**
     * Get direct permissions assigned to this user
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(
            Permission::class,
            'user_permissions',
            'user_id',
            'permission_id'
        )->withTimestamps();
    }

    /**
     * Get all permissions through roles and direct permissions
     */
    public function getAllPermissions(): BelongsToMany
    {
        // Get permissions through roles
        $rolePermissions = Permission::whereHas('roles', function ($query) {
            $query->whereIn('role_id', $this->roles()->pluck('role_id'));
        });

        // Get direct permissions
        $directPermissions = $this->permissions();

        // Merge results and remove duplicates
        $allPermissions = $rolePermissions->union($directPermissions)->distinct();

        return $allPermissions;
    }

    /**
     * Check if user has a specific role
     */
    public function hasRole(string $roleName): bool
    {
        return $this->roles()
            ->where('name', $roleName)
            ->orWhere('slug', $roleName)
            ->exists();
    }

    /**
     * Check if user has any of given roles
     */
    public function hasAnyRole(array $roleNames): bool
    {
        return $this->roles()
            ->whereIn('name', $roleNames)
            ->orWhereIn('slug', $roleNames)
            ->exists();
    }

    /**
     * Check if user has all given roles
     */
    public function hasAllRoles(array $roleNames): bool
    {
        $count = $this->roles()
            ->whereIn('name', $roleNames)
            ->orWhereIn('slug', $roleNames)
            ->count();

        return $count === count($roleNames);
    }

    /**
     * Check if user has a specific permission
     */
    public function hasPermission(string $permissionSlug): bool
    {
        // Check direct permissions
        if ($this->permissions()
            ->where('slug', $permissionSlug)
            ->exists()
        ) {
            return true;
        }

        // Check through roles
        return $this->roles()
            ->whereHas('permissions', function ($query) use ($permissionSlug) {
                $query->where('slug', $permissionSlug);
            })
            ->exists();
    }

    /**
     * Check if user has any of given permissions
     */
    public function hasAnyPermission(array $permissionSlugs): bool
    {
        // Check direct permissions
        if ($this->permissions()
            ->whereIn('slug', $permissionSlugs)
            ->exists()
        ) {
            return true;
        }

        // Check through roles
        return $this->roles()
            ->whereHas('permissions', function ($query) use ($permissionSlugs) {
                $query->whereIn('slug', $permissionSlugs);
            })
            ->exists();
    }

    /**
     * Check if user has all given permissions
     */
    public function hasAllPermissions(array $permissionSlugs): bool
    {
        // Count direct permissions
        $directCount = $this->permissions()
            ->whereIn('slug', $permissionSlugs)
            ->count();

        if ($directCount === count($permissionSlugs)) {
            return true;
        }

        // Check through roles
        foreach ($permissionSlugs as $slug) {
            if (!$this->hasPermission($slug)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Assign a role to user
     */
    public function assignRole(string $roleName): void
    {
        $role = Role::where('name', $roleName)
            ->orWhere('slug', $roleName)
            ->first();

        if ($role && !$this->hasRole($roleName)) {
            $this->roles()->attach($role->id);
        }
    }

    /**
     * Remove a role from user
     */
    public function removeRole(string $roleName): void
    {
        $role = Role::where('name', $roleName)
            ->orWhere('slug', $roleName)
            ->first();

        if ($role) {
            $this->roles()->detach($role->id);
        }
    }

    /**
     * Sync roles (replace all roles)
     */
    public function syncRoles(array $roleNames): void
    {
        $roles = Role::whereIn('name', $roleNames)
            ->orWhereIn('slug', $roleNames)
            ->pluck('id');

        $this->roles()->sync($roles);
    }

    /**
     * Check if user is an admin
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin') || $this->role === 'admin';
    }

    /**
     * Check if user is a moderator
     */
    public function isModerator(): bool
    {
        return $this->hasRole('moderator') || $this->role === 'moderator';
    }

    /**
     * Check if user is a regular user
     */
    public function isUser(): bool
    {
        return $this->hasRole('user') || $this->role === 'user';
    }

    /**
     * Check if user is banned
     */
    public function isBanned(): bool
    {
        return $this->is_banned === true;
    }

    /**
     * Check if user is suspended
     */
    public function isSuspended(): bool
    {
        return $this->is_suspended === true;
    }

    /**
     * Check if user is active (not banned or suspended)
     */
    public function isActive(): bool
    {
        return !$this->is_banned && !$this->is_suspended;
    }

    /**
     * Ban user
     */
    public function ban(string $reason = null): void
    {
        $this->update([
            'is_banned' => true,
            'banned_at' => now(),
        ]);

        // Log activity
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => "User {$this->id} was banned" . ($reason ? ": $reason" : ''),
        ]);
    }

    /**
     * Unban user
     */
    public function unban(): void
    {
        $this->update([
            'is_banned' => false,
            'banned_at' => null,
        ]);

        // Log activity
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => "User {$this->id} was unbanned",
        ]);
    }

    /**
     * Suspend user
     */
    public function suspend(string $reason = null): void
    {
        $this->update([
            'is_suspended' => true,
            'suspended_at' => now(),
        ]);

        // Log activity
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => "User {$this->id} was suspended" . ($reason ? ": $reason" : ''),
        ]);
    }

    /**
     * Unsuspend user
     */
    public function unsuspend(): void
    {
        $this->update([
            'is_suspended' => false,
            'suspended_at' => null,
        ]);

        // Log activity
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => "User {$this->id} was unsuspended",
        ]);
    }
}
