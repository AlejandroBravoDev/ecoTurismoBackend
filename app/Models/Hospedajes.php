<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Municipios;

class Hospedaje extends Model
{
    protected $table = 'hospedajes';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';
    
    protected $fillable = [
        'nombre',
        'ubicacion',
        'descripcion',
        'municipio_id',
        'tipo',
        'contacto',
        'coordenadas',
        'servicios',
        'imagenes',
    ];

    protected $casts = [
        'imagenes' => 'array',
        'servicios' => 'array',
    ];

    /**
     * Relación con el municipio
     */
    public function municipio()
    {
        return $this->belongsTo(Municipios::class, 'municipio_id');
    }
}