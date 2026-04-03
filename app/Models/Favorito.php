<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Favorito extends Model
{
    protected $fillable = [
        'usuario_id',
        'lugar_id',
        'hospedaje_id'
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
    
    public function lugar()
    {
        return $this->belongsTo(Lugares::class, 'lugar_id');
    }
    

    public function hospedaje()
    {
        return $this->belongsTo(Hospedaje::class, 'hospedaje_id');
    }
}