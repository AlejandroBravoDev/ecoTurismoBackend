<?php

namespace App\Http\Controllers;

use App\Models\Hospedaje;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class HospedajeController extends Controller
{
    /**
     * Helper para obtener la URL completa de la primera imagen.
     */
    private function getImagenPrincipalUrl($imagenes)
    {
        if (empty($imagenes) || !is_array($imagenes)) {
            return null;
        }

        $relativePath = $imagenes[0];

        if (filter_var($relativePath, FILTER_VALIDATE_URL)) {
            return $relativePath;
        }

        return Storage::disk('s3')->url($relativePath);
    }

    public function index(Request $request)
    {
        try {
            $query = Hospedaje::with(['municipio']);

            // 🔍 Filtro de búsqueda
            if ($request->has('search') && !empty($request->search)) {
                $searchTerm = $request->search;
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('nombre', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('descripcion', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('ubicacion', 'LIKE', "%{$searchTerm}%");
                });
            }

            // 🏙️ Filtro por municipio
            if ($request->has('municipio_id') && $request->municipio_id != 0) {
                $query->where('municipio_id', $request->municipio_id);
            }

            $hospedajes = $query->get();

            $hospedajesData = $hospedajes->map(function ($hospedaje) {
                return [
                    'id' => $hospedaje->id,
                    'nombre' => $hospedaje->nombre,
                    'descripcion' => $hospedaje->descripcion,
                    'coordenadas' => $hospedaje->coordenadas,
                    'municipio' => optional($hospedaje->municipio)->nombre,
                    'municipio_id' => $hospedaje->municipio_id,
                    'imagen_url' => $this->getImagenPrincipalUrl($hospedaje->imagenes),
                    'ubicacion' => $hospedaje->ubicacion,
                    'tipo' => $hospedaje->tipo,
                    'contacto' => $hospedaje->contacto,
                ];
            });

            return response()->json($hospedajesData, 200);

        } catch (\Exception $e) {
            Log::error('Error en HospedajeController@index: ' . $e->getMessage());
            return response()->json(['message' => 'Error al obtener hospedajes.'], 500);
        }
    }

    public function show($id)
    {
        try {
            $hospedaje = Hospedaje::with(['municipio'])->findOrFail($id);

            $imagenesPaths = $hospedaje->imagenes ?? [];
            $todasLasImagenesUrls = collect($imagenesPaths)->map(function ($path) {
                return filter_var($path, FILTER_VALIDATE_URL)
                    ? $path
                    : Storage::disk('s3')->url($path);
            })->toArray();

            return response()->json([
                'id' => $hospedaje->id,
                'nombre' => $hospedaje->nombre,
                'descripcion' => $hospedaje->descripcion,
                'coordenadas' => $hospedaje->coordenadas,
                'municipio' => optional($hospedaje->municipio)->nombre,
                'imagen_principal_url' => $this->getImagenPrincipalUrl($imagenesPaths),
                'todas_las_imagenes' => $todasLasImagenesUrls,
                'ubicacion' => $hospedaje->ubicacion,
                'tipo' => $hospedaje->tipo,
                'contacto' => $hospedaje->contacto,
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error en HospedajeController@show: ' . $e->getMessage());
            return response()->json(['message' => 'Error al obtener el hospedaje.'], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'nombre' => 'required|string|max:255',
                'descripcion' => 'required|string',
                'municipio_id' => 'required|exists:municipios,id',
                'ubicacion' => 'nullable|string',
                'coordenadas' => 'nullable|string',
                'imagenes' => 'required|array|max:3',
                'imagenes.*' => 'image|mimes:jpg,jpeg,png,webp|max:4096',
            ]);

            $imagenesGuardadas = [];
            if ($request->hasFile('imagenes')) {
                foreach ($request->file('imagenes') as $imagen) {
                    $imagenesGuardadas[] = $imagen->store('hospedajes', 's3');
                }
            }

            $hospedaje = Hospedaje::create([
                'nombre' => $validated['nombre'],
                'descripcion' => $validated['descripcion'],
                'municipio_id' => $validated['municipio_id'],
                'ubicacion' => $validated['ubicacion'] ?? null,
                'coordenadas' => $validated['coordenadas'] ?? null,
                'imagenes' => $imagenesGuardadas,
            ]);

            return response()->json([
                'message' => 'Hospedaje creado correctamente',
                'data' => [
                    'id' => $hospedaje->id,
                    'nombre' => $hospedaje->nombre,
                    'imagen_principal_url' => $this->getImagenPrincipalUrl($imagenesGuardadas),
                ]
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error en HospedajeController@store: ' . $e->getMessage());
            return response()->json(['message' => 'Error al crear el hospedaje'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $hospedaje = Hospedaje::findOrFail($id);

            $request->validate([
                'nombre' => 'required|string|max:255',
                'descripcion' => 'required|string',
                'imagenes_existentes' => 'nullable|string',
                'imagenes_nuevas.*' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            ]);

            $imagenesFinales = $request->filled('imagenes_existentes') 
                ? json_decode($request->imagenes_existentes, true) 
                : [];

            if ($request->hasFile('imagenes_nuevas')) {
                foreach ($request->file('imagenes_nuevas') as $imagen) {
                    $imagenesFinales[] = $imagen->store('hospedajes', 's3');
                }
            }

            $imagenesFinales = array_slice($imagenesFinales, 0, 3);
            $hospedaje->update([
                'nombre' => $request->nombre,
                'descripcion' => $request->descripcion,
                'imagenes' => $imagenesFinales,
            ]);

            return response()->json(['message' => 'Hospedaje actualizado correctamente'], 200);

        } catch (\Exception $e) {
            Log::error('Error en update Hospedaje: ' . $e->getMessage());
            return response()->json(['message' => 'Error al actualizar'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $hospedaje = Hospedaje::findOrFail($id);
            $hospedaje->delete();
            return response()->json(['message' => 'Hospedaje eliminado correctamente'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al eliminar'], 500);
        }
    }
}