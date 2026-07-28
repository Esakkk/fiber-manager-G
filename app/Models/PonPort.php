<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PonPort extends Model
{
    protected $table = 'pon_ports';

    protected $fillable = [
        'pon_id',
        'port_number',
        'status',
        'target_odc_id',
        'description',
    ];

    public function pon(): BelongsTo
    {
        return $this->belongsTo(Pon::class, 'pon_id');
    }

    public function targetOdc(): BelongsTo
    {
        return $this->belongsTo(Odc::class, 'target_odc_id');
    }
}
