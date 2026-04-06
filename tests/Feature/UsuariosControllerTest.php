<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use App\Models\Usuario;
use Laravel\Sanctum\Sanctum;

class UsuariosControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_obtener_lista_de_usuarios()
    {
        $usuarioAdmin = Usuario::create([
            'nombre_completo' => 'Admin Verador',
            'email' => 'adminverador@correo.com',
            'password' => 'password',
            'rol' => 'admin'
        ]);

        Sanctum::actingAs($usuarioAdmin);

        $response = $this->getJson('/api/usuario');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'data' => [
                         '*' => ['id', 'nombre_completo', 'email', 'avatar_url', 'created_at']
                     ]
                 ]);
    }

    public function test_obtener_un_usuario_especifico()
    {
        $usuario = Usuario::create([
            'nombre_completo' => 'Usuario Unico',
            'email' => 'unico@correo.com',
            'password' => 'password',
            'rol' => 'user'
        ]);

        Sanctum::actingAs($usuario);

        $response = $this->getJson('/api/usuario/' . $usuario->id);

        $response->assertStatus(200)
                 ->assertJsonFragment([
                     'nombre_completo' => 'Usuario Unico',
                     'email' => 'unico@correo.com'
                 ]);
    }

    public function test_crear_usuario_exitosamente()
    {
        Storage::fake('s3');

        $admin = Usuario::create([
            'nombre_completo' => 'Admin Creador',
            'email' => 'admincreador@correo.com',
            'password' => 'password',
            'rol' => 'admin'
        ]);

        Sanctum::actingAs($admin);

        $file = UploadedFile::fake()->image('nuevo_avatar.jpg');

        $payload = [
            'nombre_completo' => 'Nuevo Registrado',
            'email' => 'nuevoddd@correo.com',
            'password' => 'Password123!',
            'avatar' => $file
        ];

        $response = $this->postJson('/api/usuario', $payload);

        $response->assertStatus(201)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Usuario creado exitosamente'
                 ]);

        $this->assertDatabaseHas('usuarios', [
            'email' => 'nuevoddd@correo.com',
            'nombre_completo' => 'Nuevo Registrado'
        ]);
    }

    public function test_actualizar_usuario_exitosamente()
    {
        Storage::fake('s3');

        $usuario = Usuario::create([
            'nombre_completo' => 'Juan Viejo',
            'email' => 'viejo@correo.com',
            'password' => 'password',
            'rol' => 'user' // Para Sanctum o admin
        ]);

        Sanctum::actingAs($usuario);

        $file = UploadedFile::fake()->image('avatar.jpg');

        $payload = [
            'nombre_completo' => 'Juan Nuevo',
            'email' => 'nuevo@correo.com',
            'avatar' => $file
        ];

        // Observa que en api.php especificas PUT y envías multipart -> en Laravel puede fallar, usaremos un truco o enviamos con post y _method=PUT
        $response = $this->putJson('/api/usuario/' . $usuario->id, $payload);

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Usuario actualizado correctamente',
                 ]);

        $this->assertDatabaseHas('usuarios', [
            'id' => $usuario->id,
            'nombre_completo' => 'Juan Nuevo',
            'email' => 'nuevo@correo.com'
        ]);
    }

    public function test_eliminar_usuario_exitosamente()
    {
        Storage::fake('s3');

        // Usuario para autenticar la petición y a la vez ser borrado (o usar otro admin)
        $usuario = Usuario::create([
            'nombre_completo' => 'A Eliminar',
            'email' => 'borrar@correo.com',
            'password' => 'password',
            'avatar' => 'avatars/viejo.jpg' // Simular que tiene avatar en s3
        ]);

        Sanctum::actingAs($usuario);

        $response = $this->deleteJson('/api/usuario/' . $usuario->id);

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Usuario eliminado correctamente'
                 ]);

        $this->assertDatabaseMissing('usuarios', [
            'id' => $usuario->id
        ]);
    }
}
