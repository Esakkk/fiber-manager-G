<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PortPhoto extends Model
{
    protected $table = 'port_photos';

    const UPDATED_AT = null;

    protected $fillable = [
        'port_id',
        'filename',
        'original_name',
        'file_size',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    public function port(): BelongsTo
    {
        return $this->belongsTo(OdpPort::class, 'port_id');
    }
}
