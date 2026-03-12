<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class RepairOrderPhoto extends Model
{
    protected $fillable = ['repair_order_id', 'path', 'caption', 'moment', 'uploaded_by'];

    public const MOMENTS = [
        'avant'   => 'Avant réparation',
        'pendant' => 'Pendant réparation',
        'apres'   => 'Après réparation',
    ];

    public function repairOrder()
    {
        return $this->belongsTo(RepairOrder::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getUrlAttribute(): string
    {
        return Storage::disk('public')->url($this->path);
    }

    public function getMomentLabelAttribute(): string
    {
        return self::MOMENTS[$this->moment] ?? ucfirst($this->moment);
    }

    protected static function booted(): void
    {
        static::deleting(function (self $photo) {
            Storage::disk('public')->delete($photo->path);
        });
    }
}
