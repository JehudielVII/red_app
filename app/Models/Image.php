<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Image extends Model
{
    use HasFactory; // 2. Agrega esta línea justo aquí adentro
    protected $guarded = [];

    protected $fillable = [
        'url',
        'imageable_id',
        'imageable_type',
        'created_at',
        'updated_at',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'integer',
        ];
    }

    /**
     * Define la relación polimórfica.
     */
    public function imageable(): MorphTo
    {
        return $this->morphTo();
    }
}