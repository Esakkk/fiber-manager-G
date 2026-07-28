<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pon extends Model
{
    protected $table = 'pon';

    protected $fillable = [
        'olt_id',
        'card_number',
        'name',
        'port_count',
        'status',
        'description',
    ];

    public function olt(): BelongsTo
    {
        return $this->belongsTo(Olt::class, 'olt_id');
    }

    public function ports(): HasMany
    {
        return $this->hasMany(PonPort::class, 'pon_id');
    }
}
