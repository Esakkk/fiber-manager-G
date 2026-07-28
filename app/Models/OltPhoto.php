<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OltPhoto extends Model
{
    protected $table = 'olt_photos';

    const UPDATED_AT = null;

    protected $fillable = [
        'olt_id',
        'filename',
        'original_name',
        'file_size',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    public function olt(): BelongsTo
    {
        return $this->belongsTo(Olt::class, 'olt_id');
    }
}
