<?php

namespace App\Http\Controllers;

use App\Models\Lugares;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class LugaresController extends Controller
{
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
            $query = Lugares::with(['municipio']);

            if ($request->has('municipio_id') && !empty($request->municipio_id)) {
                $query->where('municipio_id', $request->municipio_id);
            }

            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('nombre', 'LIKE', "%{$search}%")
                      ->orWhere('descripcion', 'LIKE', "%{$search}%");
                });
            }

            $lugares = $query->get();

            $lugaresData = $lugares->map(function ($lugar) {
                return [
                    'id' => $lugar->id,
                    'nombre' => $lugar->nombre,
                    'descripcion' => $lugar->descripcion,
                    'municipio' => optional($lugar->municipio)->nombre,
                    'municipio_id' => $lugar->municipio_id,
                    'imagen_url' => $this->getImagenPrincipalUrl($lugar->imagenes ?? []),
                    'ubicacion' => $lugar->ubicacion,
                ];
            });

            return response()->json($lugaresData, 200);
        } catch (\Exception $e) {
            Log::error('Error en LugaresController@index: ' . $e->getMessage());
            return response()->json(['message' => 'Error en el servidor'], 500);
        }
    }

    public function show($id)
    {
        try {
            $lugar = Lugares::with(['municipio'])->findOrFail($id);
            $imagenesPaths = $lugar->imagenes ?? []; 
            
            $todasLasImagenesUrls = collect($imagenesPaths)->map(function ($path) {
                 return filter_var($path, FILTER_VALIDATE_URL) ? $path : Storage::disk('s3')->url($path);
            })->toArray();

            return response()->json([
                'id' => $lugar->id,
                'nombre' => $lugar->nombre,
                'descripcion' => $lugar->descripcion,
                'coordenadas' => $lugar->coordenadas,
                'municipio' => optional($lugar->municipio)->nombre,
                'imagen_principal_url' => $this->getImagenPrincipalUrl($imagenesPaths),
                'todas_las_imagenes' => $todasLasImagenesUrls,
                'ubicacion' => $lugar->ubicacion,
                'hoteles_cercanos' => $lugar->hoteles_cercanos,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error en LugaresController@show: ' . $e->getMessage());
            return response()->json(['message' => 'Error al obtener los detalles del lugar.'], 500);
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
                    $imagenesGuardadas[] = $imagen->store('lugares', 's3');
                }
            }

            $lugar = Lugares::create([
                'nombre' => $validated['nombre'],
                'descripcion' => $validated['descripcion'],
                'municipio_id' => $validated['municipio_id'],
                'ubicacion' => $validated['ubicacion'] ?? null,
                'coordenadas' => $validated['coordenadas'] ?? null,
                'imagenes' => $imagenesGuardadas,
            ]);

            return response()->json([
                'message' => 'Lugar creado correctamente',
                'data' => [
                    'id' => $lugar->id,
                    'nombre' => $lugar->nombre,
                    'imagen_principal_url' => $this->getImagenPrincipalUrl($imagenesGuardadas),
                ]
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error en LugaresController@store: ' . $e->getMessage());
            return response()->json(['message' => 'Error al crear el lugar'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $lugar = Lugares::findOrFail($id);
            
            $request->validate([
                'nombre' => 'required|string|max:255',
                'descripcion' => 'required|string',
                'ubicacion' => 'required|string',
                'coordenadas' => 'required|string',
                'imagenes_existentes' => 'nullable|string',
                'imagenes_nuevas.*' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            ]);

            $imagenesFinales = $request->filled('imagenes_existentes') 
                ? json_decode($request->imagenes_existentes, true) 
                : [];

            if ($request->hasFile('imagenes_nuevas')) {
                foreach ($request->file('imagenes_nuevas') as $imagen) {
                    $imagenesFinales[] = $imagen->store('lugares', 's3');
                }
            }

            $lugar->update([
                'nombre' => $request->nombre,
                'descripcion' => $request->descripcion,
                'imagenes' => array_slice($imagenesFinales, 0, 3),
                'ubicacion' => $request->ubicacion,
                'coordenadas' => $request->coordenadas,
            ]);

            return response()->json(['message' => 'Lugar actualizado correctamente'], 200);
        } catch (\Exception $e) {
            Log::error('Error en LugaresController@update: ' . $e->getMessage());
            return response()->json(['message' => 'Error al actualizar el lugar'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $lugar = Lugares::findOrFail($id);
            if (!empty($lugar->imagenes)) {
                foreach ($lugar->imagenes as $path) {
                    if (!filter_var($path, FILTER_VALIDATE_URL)) {
                        Storage::disk('s3')->delete($path);
                    }
                }
            }
            $lugar->delete();
            return response()->json(['message' => 'Lugar eliminado correctamente'], 200);
        } catch (\Exception $e) {
            Log::error('Error en LugaresController@destroy: ' . $e->getMessage());
            return response()->json(['message' => 'Error al eliminar el lugar'], 500);
        }
    }
}