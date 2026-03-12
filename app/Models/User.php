<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'avatar',
        'is_active',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    // ──────────────────────────────────────
    // Rôles
    // ──────────────────────────────────────

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isGestionnaire(): bool
    {
        return $this->role === 'gestionnaire';
    }

    public function isComptable(): bool
    {
        return $this->role === 'comptable';
    }

    public function isTechnicien(): bool
    {
        return $this->role === 'technicien';
    }

    public function hasRole(string|array $roles): bool
    {
        if (is_string($roles)) {
            return $this->role === $roles;
        }

        return in_array($this->role, $roles);
    }

    public function hasAnyRole(array $roles): bool
    {
        return $this->hasRole($roles);
    }

    /**
     * Vérifie si l'utilisateur a accès à un module donné
     */
    public function canAccess(string $module): bool
    {
        $permissions = config('roles.permissions');

        return isset($permissions[$this->role]) &&
               in_array($module, $permissions[$this->role]);
    }

    // ──────────────────────────────────────
    // Relations
    // ──────────────────────────────────────

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    // ──────────────────────────────────────
    // Scopes
    // ──────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByRole($query, string $role)
    {
        return $query->where('role', $role);
    }

    // ──────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────

    public function getRoleLabelAttribute(): string
    {
        return match($this->role) {
            'admin' => 'Administrateur',
            'gestionnaire' => 'Gestionnaire',
            'comptable' => 'Comptable',
            'technicien' => 'Technicien',
            default => $this->role,
        };
    }

    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar) {
            return asset('storage/' . $this->avatar);
        }

        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=1e40af&color=fff';
    }
}
