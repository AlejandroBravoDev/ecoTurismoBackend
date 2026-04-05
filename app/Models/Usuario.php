<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Storage;
use App\Models\Comentarios;
use App\Models\Favorito;

class Usuario extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'usuarios';

    protected $fillable = [
        'nombre_completo',
        'email',
        'password',
        'avatar', 
        'banner', 
        'rol',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Para que el frontend reciba las URLs de S3 automáticamente
    protected $appends = ['avatar_url', 'banner_url'];

    public function getAvatarUrlAttribute()
    {
        if (!$this->avatar) return null;
        if (filter_var($this->avatar, FILTER_VALIDATE_URL)) return $this->avatar;
        return Storage::disk('s3')->url($this->avatar);
    }

    public function getBannerUrlAttribute()
    {
        if (!$this->banner) return null;
        if (filter_var($this->banner, FILTER_VALIDATE_URL)) return $this->banner;
        return Storage::disk('s3')->url($this->banner);
    }

    public function setPasswordAttribute($value)
    {
        if (!empty($value)) {
            $this->attributes['password'] = Hash::make($value);
        }
    }

    public function favoritos() 
    { 
        return $this->hasMany(Favorito::class, 'usuario_id'); 
    }

    public function comentarios() 
    { 
        return $this->hasMany(Comentarios::class, 'usuario_id'); 
    }

    public function esAdmin() 
    { 
        return $this->rol === 'admin'; 
    }
}