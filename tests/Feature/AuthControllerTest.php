<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Usuario;
use Laravel\Sanctum\Sanctum;
use Illuminate\Support\Facades\Hash;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_registro_exitoso()
    {
        $payload = [
            'nombre_completo' => 'Juan Perez',
            'email' => 'juan.perez@gmail.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!'
        ];

        $response = $this->postJson('/api/register', $payload);

        $response->assertStatus(201)
                 ->assertJson(['message' => 'Registro exitoso']);

        $this->assertDatabaseHas('usuarios', [
            'email' => 'juan.perez@gmail.com',
            'nombre_completo' => 'Juan Perez',
            'rol' => 'user'
        ]);
    }

    public function test_registro_falla_con_password_secuencial()
    {
        $payload = [
            'nombre_completo' => 'Secuencial',
            'email' => 'secuencial@gmail.com',
            'password' => '12345678aA!',
            'password_confirmation' => '12345678aA!'
        ];

        $response = $this->postJson('/api/register', $payload);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['password']);
    }

    public function test_registro_falla_con_extension_invalida_custom()
    {
        $payload = [
            'nombre_completo' => 'Email Mal',
            'email' => 'mal@gmail.comm', // 'comm' está bloqueado en isValidEmail
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!'
        ];

        $response = $this->postJson('/api/register', $payload);

        $response->assertStatus(422)
                 ->assertJsonFragment([
                     'email' => ['El correo tiene un formato inválido o extensión mal escrita']
                 ]);
    }

    public function test_login_exitoso()
    {
        $usuario = Usuario::create([
            'nombre_completo' => 'Admin de prueba',
            'email' => 'login_test@gmail.com',
            'password' => 'PassworD#12', // El mutator internamente hace Hash::make()
            'rol' => 'admin'
        ]);

        $payload = [
            'email' => 'login_test@gmail.com',
            'password' => 'PassworD#12'
        ];

        $response = $this->postJson('/api/login', $payload);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'message',
                     'token',
                     'usuario' => [
                         'id',
                         'nombre_completo',
                         'email'
                     ]
                 ]);
    }

    public function test_login_falla_por_password_incorrecto()
    {
        $usuario = Usuario::create([
            'nombre_completo' => 'Pass fail',
            'email' => 'fail@gmail.com',
            'password' => 'Correcta123!',
        ]);

        $payload = [
            'email' => 'fail@gmail.com',
            'password' => 'Incorrecta123!'
        ];

        $response = $this->postJson('/api/login', $payload);

        $response->assertStatus(401)
                 ->assertJson(['message' => 'Contraseña incorrecta']);
    }

    public function test_logout_exitoso()
    {
        $usuario = Usuario::create([
            'nombre_completo' => 'Logout user',
            'email' => 'logout@gmail.com',
            'password' => 'Password123!'
        ]);

        Sanctum::actingAs($usuario);

        $response = $this->postJson('/api/logout');

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Sesión cerrada correctamente']);
    }
}
