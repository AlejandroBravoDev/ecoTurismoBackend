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
        
        // Dado que puede faltar el modelo Municipios, insertamos directamente en la BD
        // para cumplir con la regla de validación 'exists:municipios,id'
        DB::table('municipios')->insert([
            'id' => 1,
            'nombre' => 'Municipio de Prueba',
            // Agrega otros campos si son not-null en tu migración
        ]);
    }

    public function test_obtener_lista_de_lugares()
    {
        // Creamos un lugar manualmente
        Lugares::create([
            'nombre' => 'Lugar de Montaña',
            'descripcion' => 'Una descripción muy larga.',
            'municipio_id' => 1,
            'imagenes' => ['fake-image-1.jpg'],
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
            'email' => 'admin_lugares@correo.com',
            'password' => 'password',
            'rol' => 'admin' // Suponiendo que debe ser admin, aunque la ruta auth:sanctum no explicite middleware admin
        ]);

        Sanctum::actingAs($usuario);

        $file = UploadedFile::fake()->image('lugar.jpg');

        $payload = [
            'nombre' => 'Lugar Espectacular',
            'descripcion' => 'Descripción del lugar espectacular.',
            'municipio_id' => 1,
            'ubicacion' => 'En algún sitio',
            'coordenadas' => '10.123, -20.456',
            'imagenes' => [$file],
        ];

        $response = $this->postJson('/api/lugares', $payload);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'message',
                     'data' => [
                         'id',
                         'nombre',
                         'imagen_principal_url'
                     ]
                 ]);

        $this->assertDatabaseHas('lugares', [
            'nombre' => 'Lugar Espectacular',
            'ubicacion' => 'En algún sitio'
        ]);
    }

    public function test_actualizar_lugar_exitosamente()
    {
        Storage::fake('s3');

        $usuario = Usuario::create([
            'nombre_completo' => 'Admin Updater',
            'email' => 'updater@correo.com',
            'password' => 'password',
            'rol' => 'admin'
        ]);

        Sanctum::actingAs($usuario);

        $lugar = Lugares::create([
            'nombre' => 'Lugar Original',
            'descripcion' => 'Vieja descripción',
            'municipio_id' => 1,
            'ubicacion' => 'Ubicacion vieja',
            'coordenadas' => '0,0',
            'imagenes' => ['vieja.jpg']
        ]);

        $payload = [
            'nombre' => 'Lugar Actualizado',
            'descripcion' => 'Nueva descripción',
            'ubicacion' => 'Nueva Ubicación',
            'coordenadas' => '1,1',
            'imagenes_existentes' => json_encode(['vieja.jpg']), // Enviar imagen vieja
            // Sin imagenes_nuevas para probar simple actualización
        ];

        $response = $this->putJson('/api/lugares/' . $lugar->id, $payload);

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Lugar actualizado correctamente']);

        $this->assertDatabaseHas('lugares', [
            'id' => $lugar->id,
            'nombre' => 'Lugar Actualizado',
            'descripcion' => 'Nueva descripción'
        ]);
    }

    public function test_eliminar_lugar_exitosamente()
    {
        Storage::fake('s3');

        $usuario = Usuario::create([
            'nombre_completo' => 'Admin Deleter',
            'email' => 'deleter@correo.com',
            'password' => 'password',
            'rol' => 'admin'
        ]);

        Sanctum::actingAs($usuario);

        $lugar = Lugares::create([
            'nombre' => 'Lugar a Borrar',
            'descripcion' => 'Se va a borrar',
            'municipio_id' => 1,
            'imagenes' => ['test.jpg']
        ]);

        $response = $this->deleteJson('/api/lugares/' . $lugar->id);

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Lugar eliminado correctamente']);

        $this->assertDatabaseMissing('lugares', [
            'id' => $lugar->id
        ]);
    }
}
