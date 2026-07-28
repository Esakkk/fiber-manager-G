<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PopPhoto extends Model
{
    protected $table = 'pop_photos';

    const UPDATED_AT = null;

    protected $fillable = [
        'pop_id',
        'filename',
        'original_name',
        'file_size',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    public function pop(): BelongsTo
    {
        return $this->belongsTo(Pop::class, 'pop_id');
    }
}
