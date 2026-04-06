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
            'email'           => 'adminverador@correo.com',
            'password'        => bcrypt('password'),
            'rol'             => 'admin'
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
            'email'           => 'unico@correo.com',
            'password'        => bcrypt('password'),
            'rol'             => 'user'
        ]);

        Sanctum::actingAs($usuario);

        $response = $this->getJson('/api/usuario/' . $usuario->id);

        $response->assertStatus(200)
                 ->assertJsonFragment([
                     'nombre_completo' => 'Usuario Unico',
                     'email'           => 'unico@correo.com'
                 ]);
    }

    public function test_crear_usuario_exitosamente()
    {
        Storage::fake('s3');

        $admin = Usuario::create([
            'nombre_completo' => 'Admin Creador',
            'email'           => 'admincreador@correo.com',
            'password'        => bcrypt('password'),
            'rol'             => 'admin'
        ]);

        Sanctum::actingAs($admin);

        // create() no requiere GD, image() sí
        $file = UploadedFile::fake()->create('nuevo_avatar.jpg', 100, 'image/jpeg');

        $payload = [
            'nombre_completo' => 'Nuevo Registrado',
            'email'           => 'nuevoddd@correo.com',
            'password'        => 'Password123!',
            'avatar'          => $file
        ];

        $response = $this->postJson('/api/usuario', $payload);

        $response->assertStatus(201)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Usuario creado exitosamente'
                 ]);

        $this->assertDatabaseHas('usuarios', [
            'email'           => 'nuevoddd@correo.com',
            'nombre_completo' => 'Nuevo Registrado'
        ]);
    }

    public function test_actualizar_usuario_exitosamente()
    {
        Storage::fake('s3');

        $usuario = Usuario::create([
            'nombre_completo' => 'Juan Viejo',
            'email'           => 'viejo@correo.com',
            'password'        => bcrypt('password'),
            'rol'             => 'user'
        ]);

        Sanctum::actingAs($usuario);

        $file = UploadedFile::fake()->create('avatar.jpg', 100, 'image/jpeg');

        $payload = [
            'nombre_completo' => 'Juan Nuevo',
            'email'           => 'nuevo@correo.com',
            'avatar'          => $file
        ];

        $response = $this->putJson('/api/usuario/' . $usuario->id, $payload);

        $response->assertStatus(200)
                 ->assertJson([
                     'success'  => true,
                     'message'  => 'Usuario actualizado correctamente',
                 ]);

        $this->assertDatabaseHas('usuarios', [
            'id'              => $usuario->id,
            'nombre_completo' => 'Juan Nuevo',
            'email'           => 'nuevo@correo.com'
        ]);
    }

    public function test_eliminar_usuario_exitosamente()
    {
        Storage::fake('s3');

        $usuario = Usuario::create([
            'nombre_completo' => 'A Eliminar',
            'email'           => 'borrar@correo.com',
            'password'        => bcrypt('password'),
            'rol'             => 'user'
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