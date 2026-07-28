<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pole extends Model
{
    protected $table = 'pole';

    protected $fillable = [
        'name',
        'code',
        'lat',
        'lng',
        'location',
        'description',
    ];

    protected $casts = [
        'lat' => 'decimal:8',
        'lng' => 'decimal:8',
    ];
}
