<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use App\Models\Usuario;
use App\Models\Lugares;
use Laravel\Sanctum\Sanctum;

class LugaresControllerTest extends TestCase
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

    public function test_obtener_lista_de_lugares()
    {
        Lugares::create([
            'nombre'       => 'Lugar de Montaña',
            'descripcion'  => 'Una descripción muy larga.',
            'municipio_id' => 1,
            'imagenes'     => json_encode(['fake-image-1.jpg']),
        ]);

        $response = $this->getJson('/api/lugares');

        $response->assertStatus(200)
                 ->assertJsonFragment([
                     'nombre' => 'Lugar de Montaña'
                 ]);
    }

    public function test_crear_lugar_exitosamente()
    {
        Storage::fake('s3');

        $usuario = Usuario::create([
            'nombre_completo' => 'Admin Lugares',
            'email'           => 'admin_lugares@correo.com',
            'password'        => bcrypt('password'),
            'rol'             => 'admin'
        ]);

        Sanctum::actingAs($usuario);

        // UploadedFile::fake()->create() no requiere GD
        $file = UploadedFile::fake()->create('lugar.jpg', 100, 'image/jpeg');

        $payload = [
            'nombre'      => 'Lugar Espectacular',
            'descripcion' => 'Descripción del lugar espectacular.',
            'municipio_id'=> 1,
            'ubicacion'   => 'En algún sitio',
            'coordenadas' => '10.123, -20.456',
            'imagenes'    => [$file],
        ];

        $response = $this->postJson('/api/lugares', $payload);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'message',
                     'data' => ['id', 'nombre', 'imagen_principal_url']
                 ]);

        $this->assertDatabaseHas('lugares', [
            'nombre'    => 'Lugar Espectacular',
            'ubicacion' => 'En algún sitio'
        ]);
    }

    public function test_actualizar_lugar_exitosamente()
    {
        Storage::fake('s3');

        $usuario = Usuario::create([
            'nombre_completo' => 'Admin Updater',
            'email'           => 'updater@correo.com',
            'password'        => bcrypt('password'),
            'rol'             => 'admin'
        ]);

        Sanctum::actingAs($usuario);

        $lugar = Lugares::create([
            'nombre'       => 'Lugar Original',
            'descripcion'  => 'Vieja descripción',
            'municipio_id' => 1,
            'ubicacion'    => 'Ubicacion vieja',
            'coordenadas'  => '0,0',
            'imagenes'     => json_encode(['vieja.jpg'])
        ]);

        $payload = [
            'nombre'              => 'Lugar Actualizado',
            'descripcion'         => 'Nueva descripción',
            'ubicacion'           => 'Nueva Ubicación',
            'coordenadas'         => '1,1',
            'imagenes_existentes' => json_encode(['vieja.jpg']),
        ];

        $response = $this->putJson('/api/lugares/' . $lugar->id, $payload);

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Lugar actualizado correctamente']);

        $this->assertDatabaseHas('lugares', [
            'id'          => $lugar->id,
            'nombre'      => 'Lugar Actualizado',
            'descripcion' => 'Nueva descripción'
        ]);
    }

    public function test_eliminar_lugar_exitosamente()
    {
        Storage::fake('s3');

        $usuario = Usuario::create([
            'nombre_completo' => 'Admin Deleter',
            'email'           => 'deleter@correo.com',
            'password'        => bcrypt('password'),
            'rol'             => 'admin'
        ]);

        Sanctum::actingAs($usuario);

        $lugar = Lugares::create([
            'nombre'       => 'Lugar a Borrar',
            'descripcion'  => 'Se va a borrar',
            'municipio_id' => 1,
            'imagenes'     => json_encode(['test.jpg'])
        ]);

        $response = $this->deleteJson('/api/lugares/' . $lugar->id);

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Lugar eliminado correctamente']);

        $this->assertDatabaseMissing('lugares', [
            'id' => $lugar->id
        ]);
    }
}