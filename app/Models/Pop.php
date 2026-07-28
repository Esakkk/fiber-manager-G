<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pop extends Model
{
    protected $table = 'pop';

    protected $fillable = [
        'name',
        'code',
        'lat',
        'lng',
        'location',
        'address',
        'description',
        'has_photo',
    ];

    protected $casts = [
        'lat' => 'decimal:8',
        'lng' => 'decimal:8',
        'has_photo' => 'boolean',
    ];

    public function olts(): HasMany
    {
        return $this->hasMany(Olt::class, 'pop_id');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(PopPhoto::class, 'pop_id');
    }
}
