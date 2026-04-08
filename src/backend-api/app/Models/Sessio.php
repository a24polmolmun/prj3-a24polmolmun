<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sessio extends Model
{
    use HasFactory;

    protected $table = 'sessions_cinema';

    protected $fillable = [
        'esdeveniment_id',
        'hora',
        'dia',
    ];

    public function esdeveniment(): BelongsTo
    {
        return $this->belongsTo(Esdeveniment::class , 'esdeveniment_id');
    }

    public function seients(): HasMany
    {
        return $this->hasMany(Seient::class , 'sessio_id');
    }
}