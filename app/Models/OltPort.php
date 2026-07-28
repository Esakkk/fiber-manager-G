<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OltPort extends Model
{
    protected $table = 'olt_ports';

    protected $fillable = [
        'olt_id',
        'port_number',
        'status',
        'target_odc_id',
        'description',
    ];

    public function olt(): BelongsTo
    {
        return $this->belongsTo(Olt::class, 'olt_id');
    }

    public function targetOdc(): BelongsTo
    {
        return $this->belongsTo(Odc::class, 'target_odc_id');
    }
}
