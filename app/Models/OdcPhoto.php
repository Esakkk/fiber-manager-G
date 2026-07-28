<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OdcPhoto extends Model
{
    protected $table = 'odc_photos';

    const UPDATED_AT = null;

    protected $fillable = [
        'odc_id',
        'filename',
        'original_name',
        'file_size',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    public function odc(): BelongsTo
    {
        return $this->belongsTo(Odc::class, 'odc_id');
    }
}
