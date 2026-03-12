<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class VehiclePhoto extends Model
{
    protected $fillable = ['vehicle_id', 'path', 'description', 'type', 'uploaded_by'];

    public const TYPES = [
        'avant_reparation'  => 'Avant réparation',
        'apres_reparation'  => 'Après réparation',
        'dommage'           => 'Dommage / Dégât',
        'general'           => 'Général',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getUrlAttribute(): string
    {
        return Storage::disk('public')->url($this->path);
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? ucfirst($this->type);
    }
}
