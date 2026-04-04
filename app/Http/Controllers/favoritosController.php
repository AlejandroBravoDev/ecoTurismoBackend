<?php

namespace App\Http\Controllers;

use App\Models\Favorito;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class favoritosController extends Controller
{
    public function store(Request $request)
    {
        try {
            $request->validate([
                'lugar_id' => 'nullable|exists:lugares,id',
                'hospedaje_id' => 'nullable|exists:hospedajes,id',
            ]);

            // Verificar que al menos uno esté presente
            if (!$request->lugar_id && !$request->hospedaje_id) {
                return response()->json([
                    'message' => 'Debes proporcionar lugar_id o hospedaje_id'
                ], 400);
            }

            $existingFavorite = Favorito::where('usuario_id', Auth::id())
                ->where(function($query) use ($request) {
                    if ($request->lugar_id) {
                        $query->where('lugar_id', $request->lugar_id);
                    }
                    if ($request->hospedaje_id) {
                        $query->where('hospedaje_id', $request->hospedaje_id);
                    }
                })
                ->first();

            if ($existingFavorite) {
                return response()->json([
                    'message' => 'Ya está en favoritos'
                ], 400);
            }

            $favorito = new Favorito();
            $favorito->usuario_id = Auth::id();
            $favorito->lugar_id = $request->lugar_id;
            $favorito->hospedaje_id = $request->hospedaje_id;
            $favorito->save();

            return response()->json([
                'message' => 'Favorito añadido',
                'favorito' => $favorito
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error al añadir favorito: ' . $e->getMessage());
            return response()->json(['message' => 'Error al añadir favorito'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            // El $id puede ser lugar_id O hospedaje_id
            $favorito = Favorito::where('usuario_id', Auth::id())
                ->where(function($query) use ($id) {
                    $query->where('lugar_id', $id)
                          ->orWhere('hospedaje_id', $id);
                })
                ->first();
            
            if (!$favorito) {
                return response()->json([
                    'message' => 'Favorito no encontrado'
                ], 404);
            }
                
            $favorito->delete();
            return response()->json(['message' => 'Favorito eliminado'], 200);

        } catch (\Exception $e) {
            Log::error('Error al eliminar favorito: ' . $e->getMessage());
            return response()->json(['message' => 'Error al eliminar favorito'], 500);
        }
    }

    public function check($id)
    {
        try {
            $userId = Auth::id();
            \Log::info('Check favorito - User ID: ' . $userId . ', ID: ' . $id);
            
            if (!$userId) {
                return response()->json(['message' => 'Not authenticated'], 401);
            }
            
            $isFavorite = Favorito::where('usuario_id', $userId)
                ->where(function($query) use ($id) {
                    $query->where('lugar_id', $id)
                        ->orWhere('hospedaje_id', $id);
                })
                ->exists();
                
            return response()->json(['isFavorite' => $isFavorite], 200);

        } catch (\Exception $e) {
            Log::error('Error al verificar favorito: ' . $e->getMessage());
            return response()->json(['message' => 'Error al verificar favorito'], 500);
        }
    }
}