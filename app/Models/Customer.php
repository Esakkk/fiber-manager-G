<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Customer extends Model
{
    protected $table = 'customers';

    protected $fillable = [
        'name',
        'lat',
        'lng',
        'onu_number',
        'modem_type',
        'address',
        'phone',
        'description',
        'odp_id',
        'port_number',
        'path_coordinates',
    ];

    protected $casts = [
        'lat' => 'decimal:8',
        'lng' => 'decimal:8',
        'port_number' => 'integer',
    ];

    public function odp(): BelongsTo
    {
        return $this->belongsTo(Odp::class, 'odp_id');
    }
}
