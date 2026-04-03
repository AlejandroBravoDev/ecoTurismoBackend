<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Comentarios extends Model
{
    protected $fillable = [
        'lugar_id',
        'hospedaje_id', 
        'usuario_id',
        'contenido',
        'rating',
        'image_path',
        'category'
    ];
    
    protected $table = 'comentarios'; 

    // Añadimos esto para que la URL de S3 aparezca automáticamente en el JSON
    protected $appends = ['image_url'];

    public function getImageUrlAttribute()
    {
        return $this->image_path ? Storage::disk('s3')->url($this->image_path) : null;
    }

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