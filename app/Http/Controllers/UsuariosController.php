<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class usuarioController extends Controller
{
    // Listar todos los usuarios
    public function index()
    {
        try {
            $usuarios = Usuario::select('id', 'nombre_completo', 'email', 'avatar', 'created_at')
                ->orderBy('created_at', 'desc')
                ->get();

            $usuariosData = $usuarios->map(function ($usuario) {
                return [
                    'id' => $usuario->id,
                    'nombre_completo' => $usuario->nombre_completo,
                    'email' => $usuario->email,
                    'avatar_url' => $usuario->avatar_url ?? asset('assets/usuarioDemo.png'),
                    'created_at' => $usuario->created_at->toDateTimeString(),
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $usuariosData
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener usuarios'
            ], 500);
        }
    }

    // Ver un usuario específico
    public function show($id)
    {
        try {
            $usuario = Usuario::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $usuario->id,
                    'nombre_completo' => $usuario->nombre_completo,
                    'email' => $usuario->email,
                    'avatar_url' => $usuario->avatar_url ?? asset('assets/usuarioDemo.png'),
                    'created_at' => $usuario->created_at->toDateTimeString(),
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no encontrado'
            ], 404);
        }
    }
}