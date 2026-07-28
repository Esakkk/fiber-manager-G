<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Odc extends Model
{
    protected $table = 'odc';

    protected $fillable = [
        'name',
        'lat',
        'lng',
        'location',
        'capacity',
        'used_ports',
        'description',
        'source_type',
        'source_id',
        'pon_id',
        'pon_port_number',
        'olt_id',
        'path_coordinates',
    ];

    protected $casts = [
        'lat' => 'decimal:8',
        'lng' => 'decimal:8',
    ];

    public function source(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'source_type', 'source_id');
    }

    public function pon(): BelongsTo
    {
        return $this->belongsTo(Pon::class, 'pon_id');
    }

    public function olt(): BelongsTo
    {
        return $this->belongsTo(Olt::class, 'olt_id');
    }

    public function connections(): HasMany
    {
        return $this->hasMany(OdcOdpConnection::class, 'odc_id');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(OdcPhoto::class, 'odc_id');
    }
}
