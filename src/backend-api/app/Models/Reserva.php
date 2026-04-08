<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reserva extends Model
{
    use HasFactory;

    protected $table = 'reserves';

    protected $fillable = [
        'usuari_id',
        'seient_id',
        'localitzador',
        'estat',
        'data_expiracio',
    ];

    public function usuari(): BelongsTo
    {
        return $this->belongsTo(User::class , 'usuari_id');
    }

    public function seient(): BelongsTo
    {
        return $this->belongsTo(Seient::class , 'seient_id');
    }
}