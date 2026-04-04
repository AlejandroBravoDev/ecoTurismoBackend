<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use App\Models\Comentarios;
use App\Models\municipios;
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
        'rating_promedio',
        'total_comentarios',
        'imagen_principal_url',
        'imagenes_url',
    ];

    public function getRatingPromedioAttribute()
    {
        $promedio = $this->opiniones()->avg('rating');
        return $promedio ? round($promedio, 1) : 0;
    }

    public function getTotalComentariosAttribute()
    {
        return $this->opiniones()->count();
    }

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

    public function opiniones()
    {
        return $this->hasMany(Comentarios::class, 'lugar_id')->latest();
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}
