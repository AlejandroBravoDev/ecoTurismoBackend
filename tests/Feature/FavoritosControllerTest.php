<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Usuario;
use App\Models\Lugares;
use App\Models\Favorito;
use Laravel\Sanctum\Sanctum;

class FavoritosControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_anadir_favorito_exitosamente()
    {
        $usuario = Usuario::create([
            'nombre_completo' => 'Usuario Favoritos',
            'email' => 'favoritos@correo.com',
            'password' => 'password',
            'rol' => 'user'
        ]);

        Sanctum::actingAs($usuario);

        $lugar = Lugares::create([
            'nombre' => 'Lugar Hermoso',
            'descripcion' => 'Una hermosa vista.',
            'municipio_id' => 1
        ]);

        $payload = [
            'lugar_id' => $lugar->id
        ];

        $response = $this->postJson('/api/favoritos', $payload);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'message',
                     'favorito' => [
                         'id',
                         'usuario_id',
                         'lugar_id'
                     ]
                 ])
                 ->assertJsonFragment([
                     'message' => 'Favorito añadido',
                     'lugar_id' => $lugar->id,
                     'usuario_id' => $usuario->id
                 ]);

        $this->assertDatabaseHas('favoritos', [
            'usuario_id' => $usuario->id,
            'lugar_id' => $lugar->id
        ]);
    }

    public function test_no_se_puede_anadir_favorito_duplicado()
    {
        $usuario = Usuario::create([
            'nombre_completo' => 'Usuario Favoritos 2',
            'email' => 'favoritos2@correo.com',
            'password' => 'password'
        ]);

        Sanctum::actingAs($usuario);

        $lugar = Lugares::create([
            'nombre' => 'Lugar Duplicado',
            'descripcion' => 'Para probar duplicidad.',
            'municipio_id' => 1
        ]);

        // Crear primer favorito
        Favorito::create([
            'usuario_id' => $usuario->id,
            'lugar_id' => $lugar->id
        ]);

        // Intentar añadir el mismo
        $payload = [
            'lugar_id' => $lugar->id
        ];

        $response = $this->postJson('/api/favoritos', $payload);

        $response->assertStatus(400)
                 ->assertJson(['message' => 'Ya está en favoritos']);
    }

    public function test_error_si_no_se_envia_lugar_ni_hospedaje()
    {
        $usuario = Usuario::create([
            'nombre_completo' => 'Usuario Err',
            'email' => 'err@correo.com',
            'password' => 'password'
        ]);

        Sanctum::actingAs($usuario);

        $payload = []; // enviamos payload vacío

        $response = $this->postJson('/api/favoritos', $payload);

        $response->assertStatus(400)
                 ->assertJson(['message' => 'Debes proporcionar lugar_id o hospedaje_id']);
    }

    public function test_eliminar_favorito_exitosamente()
    {
        $usuario = Usuario::create([
            'nombre_completo' => 'Usuario Eliminar',
            'email' => 'eliminar@correo.com',
            'password' => 'password'
        ]);

        Sanctum::actingAs($usuario);

        $lugar = Lugares::create([
            'nombre' => 'Lugar a eliminar',
            'descripcion' => 'Descripción',
            'municipio_id' => 1
        ]);

        // Creamos récord
        Favorito::create([
            'usuario_id' => $usuario->id,
            'lugar_id' => $lugar->id
        ]);

        $response = $this->deleteJson('/api/favoritos/' . $lugar->id);

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Favorito eliminado']);

        $this->assertDatabaseMissing('favoritos', [
            'usuario_id' => $usuario->id,
            'lugar_id' => $lugar->id
        ]);
    }

    public function test_comprobar_si_es_favorito()
    {
        $usuario = Usuario::create([
            'nombre_completo' => 'Usuario Checar',
            'email' => 'checar@correo.com',
            'password' => 'password'
        ]);

        Sanctum::actingAs($usuario);

        $lugar = Lugares::create([
            'nombre' => 'Lugar check',
            'descripcion' => 'Check desc',
            'municipio_id' => 1
        ]);

        // Comprobamos que no es favorito todavía
        $response1 = $this->getJson('/api/favoritos/check/' . $lugar->id);
        $response1->assertStatus(200)
                  ->assertJson(['isFavorite' => false]);

        // Lo hacemos favorito
        Favorito::create([
            'usuario_id' => $usuario->id,
            'lugar_id' => $lugar->id
        ]);

        // Comprobamos de nuevo
        $response2 = $this->getJson('/api/favoritos/check/' . $lugar->id);
        $response2->assertStatus(200)
                  ->assertJson(['isFavorite' => true]);
    }
}
