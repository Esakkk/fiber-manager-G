<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Odp extends Model
{
    protected $table = 'odp';

    protected $fillable = [
        'name',
        'source_id',
        'source_type',
        'port_number_in_odc',
        'lat',
        'lng',
        'path_coordinates',
        'location',
        'total_ports',
        'available_ports',
        'description',
    ];

    protected $casts = [
        'lat' => 'decimal:8',
        'lng' => 'decimal:8',
    ];

    public function source(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'source_type', 'source_id');
    }

    public function ports(): HasMany
    {
        return $this->hasMany(OdpPort::class, 'odp_id');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(OdpPhoto::class, 'odp_id');
    }
}
