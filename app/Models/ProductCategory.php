<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ProductCategory extends Model
{
    protected $fillable = ['nom', 'slug', 'description', 'couleur', 'ordre', 'actif'];

    protected function casts(): array
    {
        return ['actif' => 'boolean'];
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'category_id');
    }

    public function scopeActives($query)
    {
        return $query->where('actif', true)->orderBy('ordre');
    }

    protected static function booted(): void
    {
        static::creating(function (self $cat) {
            if (!$cat->slug) {
                $cat->slug = Str::slug($cat->nom);
            }
        });
    }
}
