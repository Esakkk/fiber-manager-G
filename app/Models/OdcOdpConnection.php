<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OdcOdpConnection extends Model
{
    protected $table = 'odc_odp_connections';

    const UPDATED_AT = null;

    protected $fillable = [
        'odc_id',
        'odp_id',
        'port_number',
    ];

    public function odc(): BelongsTo
    {
        return $this->belongsTo(Odc::class, 'odc_id');
    }

    public function odp(): BelongsTo
    {
        return $this->belongsTo(Odp::class, 'odp_id');
    }
}
