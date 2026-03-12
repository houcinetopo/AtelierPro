<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'action',
        'model_type',
        'model_id',
        'description',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
        ];
    }

    // ──────────────────────────────────────
    // Relations
    // ──────────────────────────────────────

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function subject()
    {
        return $this->morphTo('model');
    }

    // ──────────────────────────────────────
    // Scopes
    // ──────────────────────────────────────

    public function scopeRecent($query, int $limit = 50)
    {
        return $query->orderBy('created_at', 'desc')->limit($limit);
    }

    public function scopeForModel($query, string $modelType, int $modelId)
    {
        return $query->where('model_type', $modelType)->where('model_id', $modelId);
    }

    public function scopeByAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    // ──────────────────────────────────────
    // Helper static : enregistrer une activité
    // ──────────────────────────────────────

    public static function log(
        string $action,
        string $description,
        ?Model $model = null,
        ?array $oldValues = null,
        ?array $newValues = null
    ): self {
        return static::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'model_type' => $model ? get_class($model) : null,
            'model_id' => $model?->id,
            'description' => $description,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    // ──────────────────────────────────────
    // Accessors
    // ──────────────────────────────────────

    public function getActionLabelAttribute(): string
    {
        return match($this->action) {
            'create' => 'Création',
            'update' => 'Modification',
            'delete' => 'Suppression',
            'login' => 'Connexion',
            'logout' => 'Déconnexion',
            'export' => 'Export',
            'print' => 'Impression',
            'status_change' => 'Changement de statut',
            default => ucfirst($this->action),
        };
    }

    public function getActionColorAttribute(): string
    {
        return match($this->action) {
            'create' => 'green',
            'update' => 'blue',
            'delete' => 'red',
            'login' => 'indigo',
            'logout' => 'gray',
            default => 'gray',
        };
    }
}
