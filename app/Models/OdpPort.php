<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OdpPort extends Model
{
    protected $table = 'odp_ports';

    protected $fillable = [
        'odp_id',
        'port_number',
        'status',
        'target',
        'connection_type',
        'target_port',
        'lat',
        'lng',
        'onu_number',
        'modem_type',
        'description',
        'has_photo',
        'path_coordinates',
    ];

    protected $casts = [
        'lat' => 'decimal:8',
        'lng' => 'decimal:8',
        'has_photo' => 'boolean',
    ];

    public function odp(): BelongsTo
    {
        return $this->belongsTo(Odp::class, 'odp_id');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(PortPhoto::class, 'port_id');
    }
}
