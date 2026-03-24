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
    ];

    public function tipusEntrades(): HasMany
    {
        return $this->hasMany(TipusEntrada::class , 'esdeveniment_id');
    }

    public function seients(): HasMany
    {
        return $this->hasMany(Seient::class , 'esdeveniment_id');
    }
}