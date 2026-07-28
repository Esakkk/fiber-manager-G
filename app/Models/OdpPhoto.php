<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OdpPhoto extends Model
{
    protected $table = 'odp_photos';

    const UPDATED_AT = null;

    protected $fillable = [
        'odp_id',
        'filename',
        'original_name',
        'file_size',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    public function odp(): BelongsTo
    {
        return $this->belongsTo(Odp::class, 'odp_id');
    }
}
