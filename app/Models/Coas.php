<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coas extends Model
{
    use HasFactory;

    protected $table = 'coas';

    protected $fillable = [
        'name',
        'base',
    ];

    protected $casts = [
        'base' => 'string',
    ];

}
