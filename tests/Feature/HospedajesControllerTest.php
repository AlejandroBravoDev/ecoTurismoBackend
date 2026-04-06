<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use App\Models\Usuario;
use App\Models\Hospedaje;
use Laravel\Sanctum\Sanctum;

class HospedajesControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        DB::table('municipios')->insert([
            'id' => 1,
            'nombre' => 'Municipio de Prueba 2'
        ]);
    }

    public function test_obtener_lista_de_hospedajes()
    {
        Hospedaje::create([
            'nombre' => 'Hotel Central',
            'descripcion' => 'Un hotel céntrico.',
            'municipio_id' => 1,
            'ubicacion' => 'Centro de la ciudad',
            'tipo' => 'Hotel',
            'contacto' => '1234567890',
            'imagenes' => ['hotel1.jpg'],
        ]);

        $response = $this->getJson('/api/hospedajes');

        $response->assertStatus(200)
                 ->assertJsonFragment([
                     'nombre' => 'Hotel Central',
                     'tipo' => 'Hotel'
                 ]);
    }

    public function test_crear_hospedaje_exitosamente()
    {
        Storage::fake('s3');

        $usuario = Usuario::create([
            'nombre_completo' => 'Admin Hospedajes',
            'email' => 'admin_hospedajes@correo.com',
            'password' => 'password',
            'rol' => 'admin'
        ]);

        Sanctum::actingAs($usuario);

        $file = UploadedFile::fake()->image('hospedaje.jpg');

        $payload = [
            'nombre' => 'Cabaña Bosque',
            'descripcion' => 'Una hermosa cabaña.',
            'municipio_id' => 1,
            'ubicacion' => 'En el bosque',
            'coordenadas' => '10.1, -20.4',
            'imagenes' => [$file],
        ];

        $response = $this->postJson('/api/hospedajes', $payload);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'message',
                     'data' => [
                         'id',
                         'nombre',
                         'imagen_principal_url'
                     ]
                 ]);

        $this->assertDatabaseHas('hospedajes', [
            'nombre' => 'Cabaña Bosque',
            'ubicacion' => 'En el bosque'
        ]);
    }

    public function test_actualizar_hospedaje_exitosamente()
    {
        Storage::fake('s3');

        $usuario = Usuario::create([
            'nombre_completo' => 'Admin UpdHospedaje',
            'email' => 'updhosp@correo.com',
            'password' => 'password',
            'rol' => 'admin'
        ]);

        Sanctum::actingAs($usuario);

        $hospedaje = Hospedaje::create([
            'nombre' => 'Hostal Viejo',
            'descripcion' => 'Vieja desc',
            'municipio_id' => 1,
            'ubicacion' => 'Calle vieja',
            'imagenes' => ['vieja.jpg']
        ]);

        $payload = [
            'nombre' => 'Hostal Nuevo',
            'descripcion' => 'Nueva desc',
            'imagenes_existentes' => json_encode(['vieja.jpg'])
        ];

        $response = $this->putJson('/api/hospedajes/' . $hospedaje->id, $payload);

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Hospedaje actualizado correctamente']);

        $this->assertDatabaseHas('hospedajes', [
            'id' => $hospedaje->id,
            'nombre' => 'Hostal Nuevo',
            'descripcion' => 'Nueva desc'
        ]);
    }

    public function test_eliminar_hospedaje_exitosamente()
    {
        $usuario = Usuario::create([
            'nombre_completo' => 'Admin DelHospedaje',
            'email' => 'delhosp@correo.com',
            'password' => 'password',
            'rol' => 'admin'
        ]);

        Sanctum::actingAs($usuario);

        $hospedaje = Hospedaje::create([
            'nombre' => 'Hostal Borrable',
            'descripcion' => 'Se borrara',
            'municipio_id' => 1
        ]);

        $response = $this->deleteJson('/api/hospedajes/' . $hospedaje->id);

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Hospedaje eliminado correctamente']);

        $this->assertDatabaseMissing('hospedajes', [
            'id' => $hospedaje->id
        ]);
    }
}
