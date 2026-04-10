<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

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
        'aforament_total',
    ];

    public function sessions(): HasMany
    {
        return $this->hasMany(Sessio::class , 'esdeveniment_id');
    }

    public function tipusEntrades(): HasMany
    {
        return $this->hasMany(TipusEntrada::class , 'esdeveniment_id');
    }

    public function seients(): HasManyThrough
    {
        return $this->hasManyThrough(Seient::class , Sessio::class , 'esdeveniment_id', 'sessio_id');
    }

    /**
     * Get the reviews for the event.
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class , 'esdeveniment_id');
    }

    /**
     * Get the average rating for the event.
     */
    public function getAverageRatingAttribute()
    {
        return round($this->reviews()->avg('rating'), 1) ?: 0;
    }

    protected $appends = ['average_rating'];
}