<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationLog extends Model
{
    protected $fillable = [
        'notifiable_type', 'notifiable_id',
        'client_id', 'canal', 'destinataire',
        'sujet', 'message', 'statut', 'erreur',
    ];

    public function notifiable()
    {
        return $this->morphTo();
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    // ── Accessors ──

    public function getCanalBadgeAttribute(): string
    {
        $c = $this->canal === 'email' ? 'blue' : 'green';
        $icon = $this->canal === 'email' ? '✉' : '📱';
        return "<span class=\"inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium bg-{$c}-50 text-{$c}-700\">{$icon} " . ucfirst($this->canal) . "</span>";
    }

    public function getStatutBadgeAttribute(): string
    {
        $colors = [
            'envoye' => 'green',
            'echoue' => 'red',
            'en_attente' => 'amber',
        ];
        $labels = [
            'envoye' => 'Envoyé',
            'echoue' => 'Échoué',
            'en_attente' => 'En attente',
        ];
        $c = $colors[$this->statut] ?? 'gray';
        $label = $labels[$this->statut] ?? $this->statut;
        return "<span class=\"inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-{$c}-100 text-{$c}-700\">{$label}</span>";
    }
}
