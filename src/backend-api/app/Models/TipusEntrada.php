<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TipusEntrada extends Model
{
    use HasFactory;

    protected $table = 'tipus_entrades';

    protected $fillable = [
        'esdeveniment_id',
        'nom',
        'preu',
    ];

    public function esdeveniment(): BelongsTo
    {
        return $this->belongsTo(Esdeveniment::class , 'esdeveniment_id');
    }
}