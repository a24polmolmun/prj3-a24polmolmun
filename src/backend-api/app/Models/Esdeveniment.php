<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Esdeveniment extends Model
{
    use HasFactory;

    protected $table = 'esdeveniments';

    protected $fillable = [
        'nom',
        'data_hora',
        'recinte',
        'descripcio',
        'imatge',
    ];

    protected $appends = ['sessions'];

    public function getSessionsAttribute()
    {
        // Retornem un array d'hores estàtiques per a la cartellera
        return ['17:00', '19:30', '22:00'];
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(Sessio::class , 'esdeveniment_id');
    }

    public function tipusEntrades(): HasMany
    {
        return $this->hasMany(TipusEntrada::class , 'esdeveniment_id');
    }

    public function seients(): HasMany
    {
        return $this->hasMany(Seient::class , 'esdeveniment_id');
    }
}