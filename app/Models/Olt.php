<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Olt extends Model
{
    protected $table = 'olt';

    protected $fillable = [
        'pop_id',
        'name',
        'model',
        'ip_address',
        'management_port',
        'total_ports',
        'total_pon_ports',
        'used_pon_ports',
        'lat',
        'lng',
        'location',
        'description',
        'has_photo',
    ];

    protected $casts = [
        'lat' => 'decimal:8',
        'lng' => 'decimal:8',
        'has_photo' => 'boolean',
    ];

    public function pop(): BelongsTo
    {
        return $this->belongsTo(Pop::class, 'pop_id');
    }

    public function pons(): HasMany
    {
        return $this->hasMany(Pon::class, 'olt_id');
    }

    public function ports(): HasMany
    {
        return $this->hasMany(OltPort::class, 'olt_id');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(OltPhoto::class, 'olt_id');
    }
}
