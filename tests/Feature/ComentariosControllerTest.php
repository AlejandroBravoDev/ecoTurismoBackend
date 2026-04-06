<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Usuario;
use App\Models\Lugares;
use App\Models\Comentarios;
use Laravel\Sanctum\Sanctum;

class ComentariosControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_crear_comentario_exitosamente()
    {
        // Crear un usuario de prueba
        $usuario = Usuario::create([
            'nombre_completo' => 'Usuario Prueba',
            'email' => 'prueba@correo.com',
            'password' => 'password',
            'rol' => 'usuario' // Asegurando que tenga el rol necesario si se requiere
        ]);

        // Autenticar como el usuario creado usando Sanctum
        Sanctum::actingAs($usuario);

        // Crear lugar manualmente para asociar el comentario
        $lugar = Lugares::create([
            'nombre' => 'Lugar Prueba',
            'descripcion' => 'Descripción prueba',
            // Agrega otros campos requeridos según sea el diseño de BD
            'municipio_id' => 1 // Asumiendo que es nullable o podemos poner un id directo
        ]);

        $payload = [
            'lugar_id' => $lugar->id,
            'contenido' => 'Este es un excelente lugar, muy recomendado.',
            'rating' => 5,
            'category' => 'General'
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

        $this->assertDatabaseHas('comentarios', [
            'contenido' => 'Este es un excelente lugar, muy recomendado.',
            'usuario_id' => $usuario->id,
            'lugar_id' => $lugar->id,
            'rating' => 5
        ]);
    }

    public function test_eliminar_comentario_exitosamente()
    {
        $usuario = Usuario::create([
            'nombre_completo' => 'Usuario Prueba 2',
            'email' => 'prueba2@correo.com',
            'password' => 'password'
        ]);

        Sanctum::actingAs($usuario);

        // Crear un comentario en base de datos para este usuario
        $comentario = Comentarios::create([
            'usuario_id' => $usuario->id,
            'contenido' => 'Comentario a ser eliminado',
            'rating' => 4,
            'category' => 'Servicio'
        ]);

        $response = $this->deleteJson('/api/comentarios/' . $comentario->id);

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Comentario eliminado']);

        $this->assertDatabaseMissing('comentarios', [
            'id' => $comentario->id
        ]);
    }

    public function test_error_al_crear_comentario_sin_autenticacion()
    {
        $payload = [
            'contenido' => 'Intento sin loguearse',
            'rating' => 3,
            'category' => 'General'
        ];

        $response = $this->postJson('/api/comentarios', $payload);

        $response->assertStatus(401); // Unauthorized
    }

    public function test_no_se_puede_eliminar_comentario_de_otro_usuario()
    {
        $usuario1 = Usuario::create([
            'nombre_completo' => 'Usuario Propietario',
            'email' => 'propietario@correo.com',
            'password' => 'password'
        ]);

        $usuario2 = Usuario::create([
            'nombre_completo' => 'Usuario Intruso',
            'email' => 'intruso@correo.com',
            'password' => 'password'
        ]);

        // Autenticarnos con el usuario 2 (Intruso)
        Sanctum::actingAs($usuario2);

        // Crear comentario a nombre del usuario 1
        $comentario = Comentarios::create([
            'usuario_id' => $usuario1->id,
            'contenido' => 'Comentario privado',
            'rating' => 4,
            'category' => 'Limpieza'
        ]);

        $response = $this->deleteJson('/api/comentarios/' . $comentario->id);

        $response->assertStatus(403) // Forbidden - No autorizado
                 ->assertJson(['message' => 'No autorizado']);
    }
}
