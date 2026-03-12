<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashSession extends Model
{
    protected $fillable = [
        'date_session', 'opened_by', 'closed_by',
        'solde_ouverture', 'total_entrees', 'total_sorties', 'solde_theorique',
        'solde_reel', 'ecart',
        'notes_ouverture', 'notes_cloture',
        'statut', 'heure_ouverture', 'heure_cloture',
    ];

    protected function casts(): array
    {
        return [
            'date_session'     => 'date',
            'solde_ouverture'  => 'decimal:2',
            'total_entrees'    => 'decimal:2',
            'total_sorties'    => 'decimal:2',
            'solde_theorique'  => 'decimal:2',
            'solde_reel'       => 'decimal:2',
            'ecart'            => 'decimal:2',
            'heure_ouverture'  => 'datetime',
            'heure_cloture'    => 'datetime',
        ];
    }

    public const STATUTS = [
        'ouverte'  => 'Ouverte',
        'cloturee' => 'Clôturée',
    ];

    // ──────────────────────────────────────
    // Relations
    // ──────────────────────────────────────

    public function openedBy()
    {
        return $this->belongsTo(User::class, 'opened_by');
    }

    public function closedBy()
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function movements()
    {
        return $this->hasMany(CashMovement::class)->orderByDesc('created_at');
    }

    public function entrees()
    {
        return $this->hasMany(CashMovement::class)->where('type', 'entree');
    }

    public function sorties()
    {
        return $this->hasMany(CashMovement::class)->where('type', 'sortie');
    }

    // ──────────────────────────────────────
    // Accessors
    // ──────────────────────────────────────

    public function getIsOpenAttribute(): bool
    {
        return $this->statut === 'ouverte';
    }

    public function getStatutBadgeAttribute(): string
    {
        $c = $this->statut === 'ouverte' ? 'green' : 'gray';
        $label = self::STATUTS[$this->statut] ?? $this->statut;
        return "<span class=\"inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-{$c}-100 text-{$c}-700\"><span class=\"w-1.5 h-1.5 rounded-full bg-{$c}-500\"></span>{$label}</span>";
    }

    public function getEcartBadgeAttribute(): string
    {
        if ($this->ecart === null) return '';
        if ($this->ecart == 0) return '<span class="text-green-600 text-xs font-medium">Aucun écart</span>';
        $c = $this->ecart > 0 ? 'blue' : 'red';
        $sign = $this->ecart > 0 ? '+' : '';
        return "<span class=\"text-{$c}-600 text-xs font-semibold\">{$sign}" . number_format($this->ecart, 2, ',', ' ') . " DH</span>";
    }

    // ──────────────────────────────────────
    // Scopes
    // ──────────────────────────────────────

    public function scopeOuverte($query)
    {
        return $query->where('statut', 'ouverte');
    }

    // ──────────────────────────────────────
    // Recalcul
    // ──────────────────────────────────────

    public function recalculate(): void
    {
        $entrees = $this->entrees()->sum('montant');
        $sorties = $this->sorties()->sum('montant');
        $theorique = (float)$this->solde_ouverture + $entrees - $sorties;

        $data = [
            'total_entrees'   => $entrees,
            'total_sorties'   => $sorties,
            'solde_theorique' => round($theorique, 2),
        ];

        if ($this->solde_reel !== null) {
            $data['ecart'] = round((float)$this->solde_reel - $theorique, 2);
        }

        $this->update($data);
    }

    // ──────────────────────────────────────
    // Ouvrir / Clôturer
    // ──────────────────────────────────────

    public static function openToday(float $soldeOuverture = 0, ?string $notes = null): self
    {
        $existing = self::where('date_session', now()->toDateString())->first();
        if ($existing) return $existing;

        // Solde d'ouverture = solde théorique de la veille si existant
        $lastSession = self::orderByDesc('date_session')->first();
        if ($lastSession && $soldeOuverture == 0) {
            $soldeOuverture = (float) $lastSession->solde_theorique;
        }

        return self::create([
            'date_session'     => now()->toDateString(),
            'opened_by'        => auth()->id(),
            'solde_ouverture'  => $soldeOuverture,
            'total_entrees'    => 0,
            'total_sorties'    => 0,
            'solde_theorique'  => $soldeOuverture,
            'statut'           => 'ouverte',
            'heure_ouverture'  => now(),
            'notes_ouverture'  => $notes,
        ]);
    }

    public function close(float $soldeReel, ?string $notes = null): bool
    {
        if ($this->statut !== 'ouverte') return false;

        $this->recalculate();

        $this->update([
            'solde_reel'     => $soldeReel,
            'ecart'          => round($soldeReel - (float)$this->solde_theorique, 2),
            'statut'         => 'cloturee',
            'closed_by'      => auth()->id(),
            'heure_cloture'  => now(),
            'notes_cloture'  => $notes,
        ]);

        return true;
    }
}
