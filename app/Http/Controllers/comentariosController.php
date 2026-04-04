<?php

namespace App\Http\Controllers;

use App\Models\Comentarios;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

// Controlador para comentarios
class ComentariosController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'lugar_id' => 'nullable|exists:lugares,id',
            'hospedaje_id' => 'nullable|exists:hospedajes,id', 
            'contenido' => 'required|string|max:5000', 
            'rating' => 'required|integer|min:1|max:5',
            'category' => 'required|string',
            'image' => 'nullable|image|max:2048',
        ]);

        try {
            $path = null;
            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('comentarios', $filename, 's3');
                Log::info('Imagen de comentario subida a S3: ' . $path);
            }

            $comentario = Comentarios::create([
                'usuario_id' => auth()->id(),
                'lugar_id' => $request->lugar_id,
                'hospedaje_id' => $request->hospedaje_id, 
                'contenido' => $request->contenido,
                'rating' => $request->rating,
                'category' => $request->category,
                'image_path' => $path,
            ]);

            $comentario->load('usuario');
            $image_url = $comentario->image_path 
                ? Storage::disk('s3')->url($comentario->image_path) 
                : null;

            $comentarioData = [
                'id' => $comentario->id,
                'contenido' => $comentario->contenido,
                'rating' => $comentario->rating,
                'category' => $comentario->category,
                'image_path' => $comentario->image_path,
                'image_url' => $image_url,
                'created_at' => $comentario->created_at->toDateTimeString(),
                'updated_at' => $comentario->updated_at->toDateTimeString(),
                'usuario_id' => $comentario->usuario_id,
                'lugar_id' => $comentario->lugar_id,
                'hospedaje_id' => $comentario->hospedaje_id, 
                'user' => [
                    'id' => $comentario->usuario->id,
                    'name' => $comentario->usuario->nombre_completo,
                    'avatar' => $comentario->usuario->avatar_url,
                ]
            ];

            return response()->json([
                'message' => 'Comentario creado con éxito',
                'comentario' => $comentarioData
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error al crear comentario:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'message' => 'Error al guardar el comentario.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $comentario = Comentarios::find($id);
            
            if (!$comentario) {
                return response()->json(['message' => 'Comentario no encontrado'], 404);
            }

            if (Auth::id() !== $comentario->usuario_id) {
                return response()->json(['message' => 'No autorizado'], 403);
            }

            if ($comentario->image_path) {
                Storage::disk('s3')->delete($comentario->image_path);
                Log::info("Imagen eliminada: " . $comentario->image_path);
            }

            $comentario->delete();
            return response()->json(['message' => 'Comentario eliminado'], 200);

        } catch (\Exception $e) {
            Log::error("Error al eliminar comentario: " . $e->getMessage());
            return response()->json([
                'message' => 'Error al eliminar el comentario',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}