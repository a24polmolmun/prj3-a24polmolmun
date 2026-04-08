<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Seient extends Model
{
    use HasFactory;

    protected $table = 'seients';

    protected $fillable = [
        'sessio_id',
        'fila',
        'numero',
        'estat',
    ];

    public function sessio(): BelongsTo
    {
        return $this->belongsTo(Sessio::class , 'sessio_id');
    }

    public function reserves(): HasMany
    {
        return $this->hasMany(Reserva::class , 'seient_id');
    }
}