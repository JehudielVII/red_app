<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PendingApiRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'method',
        'endpoint',
        'payload',
        'attempts',
        'last_error'
    ];

    protected $casts = [
        'payload' => 'array',
    ];
}
