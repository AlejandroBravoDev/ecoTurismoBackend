<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use App\Models\Usuario;
use App\Models\Lugares;
use App\Models\Comentarios;
use Laravel\Sanctum\Sanctum;

class ComentariosControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        DB::table('municipios')->insert([
            'id'     => 1,
            'nombre' => 'Municipio de Prueba'
        ]);
    }

    public function test_crear_comentario_exitosamente()
    {
        $usuario = Usuario::create([
            'nombre_completo' => 'Usuario Prueba',
            'email'           => 'prueba@correo.com',
            'password'        => bcrypt('password'),
            'rol'             => 'user'
        ]);

        Sanctum::actingAs($usuario);

        $lugar = Lugares::create([
            'nombre'       => 'Lugar Prueba',
            'descripcion'  => 'Descripción prueba',
            'municipio_id' => 1,
            'imagenes'     => json_encode([])
        ]);

        $payload = [
            'lugar_id'  => $lugar->id,
            'contenido' => 'Este es un excelente lugar, muy recomendado.',
            'rating'    => 5,
            'category'  => 'General'
        ];

        $response = $this->postJson('/api/comentarios', $payload);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'message',
                     'comentario' => [
                         'id',
                         'contenido',
                         'rating',
                         'category',
                         'usuario_id',
                         'lugar_id'
                     ]
                 ]);

        $this->assertDatabaseHas('resenas', [
            'contenido'  => 'Este es un excelente lugar, muy recomendado.',
            'usuario_id' => $usuario->id,
            'lugar_id'   => $lugar->id,
            'rating'     => 5
        ]);
    }

    public function test_eliminar_comentario_exitosamente()
    {
        $usuario = Usuario::create([
            'nombre_completo' => 'Usuario Prueba 2',
            'email'           => 'prueba2@correo.com',
            'password'        => bcrypt('password'),
            'rol'             => 'user'
        ]);

        Sanctum::actingAs($usuario);

        $lugar = Lugares::create([
            'nombre'       => 'Lugar Para Comentario',
            'descripcion'  => 'Desc',
            'municipio_id' => 1,
            'imagenes'     => json_encode([])
        ]);

        $comentario = Comentarios::create([
            'usuario_id' => $usuario->id,
            'lugar_id'   => $lugar->id,
            'contenido'  => 'Comentario a ser eliminado',
            'rating'     => 4,
            'category'   => 'Servicio'
        ]);

        $response = $this->deleteJson('/api/comentarios/' . $comentario->id);

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Comentario eliminado']);

        $this->assertDatabaseMissing('resenas', [
            'id' => $comentario->id
        ]);
    }

    public function test_error_al_crear_comentario_sin_autenticacion()
    {
        $payload = [
            'contenido' => 'Intento sin loguearse',
            'rating'    => 3,
            'category'  => 'General'
        ];

        $response = $this->postJson('/api/comentarios', $payload);

        $response->assertStatus(401);
    }

    public function test_no_se_puede_eliminar_comentario_de_otro_usuario()
    {
        $usuario1 = Usuario::create([
            'nombre_completo' => 'Usuario Propietario',
            'email'           => 'propietario@correo.com',
            'password'        => bcrypt('password'),
            'rol'             => 'user'
        ]);

        $usuario2 = Usuario::create([
            'nombre_completo' => 'Usuario Intruso',
            'email'           => 'intruso@correo.com',
            'password'        => bcrypt('password'),
            'rol'             => 'user'
        ]);

        Sanctum::actingAs($usuario2);

        $lugar = Lugares::create([
            'nombre'       => 'Lugar Ajeno',
            'descripcion'  => 'Desc',
            'municipio_id' => 1,
            'imagenes'     => json_encode([])
        ]);

        $comentario = Comentarios::create([
            'usuario_id' => $usuario1->id,
            'lugar_id'   => $lugar->id,
            'contenido'  => 'Comentario privado',
            'rating'     => 4,
            'category'   => 'Limpieza'
        ]);

        $response = $this->deleteJson('/api/comentarios/' . $comentario->id);

        $response->assertStatus(403)
                 ->assertJson(['message' => 'No autorizado']);
    }
}