<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class City extends Model
{
    protected $fillable = ['state_id', 'name', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

    public function country(): HasOneThrough
    {
        return $this->hasOneThrough(
            Country::class,    // Modelo final
            State::class    // Modelo intermedio
           /*  'id',             // Clave foránea en el modelo State (hacia City)
            'id',             // Clave foránea en Country (hacia State) 
            'state_id',       // Clave local en City
            'country_id'      // Clave local en State */
        );
    }
}
