<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use App\Models\Municipios;
use App\Models\Usuario;

class Lugares extends Model
{
    protected $table = 'lugares';

    protected $fillable = [
        'nombre',
        'descripcion',
        'ubicacion',
        'municipio_id',
        'hoteles_cercanos',
        'recomendaciones',
        'coordenadas',
        'imagenes',
        'usuario_id',
    ];

    protected $casts = [
        'hoteles_cercanos' => 'array',
        'recomendaciones' => 'array',
        'imagenes' => 'array',
    ];

    protected $appends = [
        'imagen_principal_url',
        'imagenes_url',
    ];


    public function getImagenPrincipalUrlAttribute()
    {
        if (empty($this->imagenes)) {
            return null;
        }

        return Storage::disk('s3')->url($this->imagenes[0]);
    }

    public function getImagenesUrlAttribute()
    {
        if (empty($this->imagenes)) {
            return [];
        }

        return collect($this->imagenes)->map(fn ($img) =>
            Storage::disk('s3')->url($img)
        )->toArray();
    }


    public function municipio()
    {
        return $this->belongsTo(Municipios::class, 'municipio_id');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}