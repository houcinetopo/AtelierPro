<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExpertEmail extends Model
{
    protected $fillable = [
        'expert_id', 'email', 'label', 'is_primary',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
        ];
    }

    public function expert()
    {
        return $this->belongsTo(Expert::class);
    }

    public function getLabelDisplayAttribute(): string
    {
        return $this->label ?? ($this->is_primary ? 'Principal' : 'Secondaire');
    }

    protected static function booted(): void
    {
        // Si on marque un email comme principal, démarquer les autres
        static::saving(function (self $email) {
            if ($email->is_primary && $email->expert_id) {
                self::where('expert_id', $email->expert_id)
                    ->where('id', '!=', $email->id ?? 0)
                    ->update(['is_primary' => false]);
            }
        });
    }
}
