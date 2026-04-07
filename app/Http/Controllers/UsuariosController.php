<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UsuariosController extends Controller
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

    // Crear usuario (store)
    public function store(Request $request)
    {
        try {
            $request->validate([
                'nombre_completo' => 'required|string|max:255',
                'email' => 'required|email|unique:usuarios,email',
                'password' => 'required|string|min:8',
                'avatar' => 'nullable|image|max:2048'
            ]);

            $avatarPath = null;
            if ($request->hasFile('avatar')) {
                $avatarPath = $request->file('avatar')->store('avatars', 's3');
            }

            $usuario = Usuario::create([
                'nombre_completo' => $request->nombre_completo,
                'email' => strtolower($request->email),
                'password' => $request->password,
                'avatar' => $avatarPath,
                'rol' => 'user' // Default o recibir del request si es admin
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Usuario creado exitosamente',
                'data' => [
                    'id' => $usuario->id,
                    'nombre_completo' => $usuario->nombre_completo,
                    'email' => $usuario->email,
                    'avatar_url' => $usuario->avatar_url,
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear usuario'
            ], 500);
        }
    }

    // Actualizar usuario
    public function update(Request $request, $id)
    {
        try {
            $usuario = Usuario::findOrFail($id);

            $request->validate([
                'nombre_completo' => 'required|string|max:255',
                'email' => 'required|email|unique:usuarios,email,' . $id,
                'avatar' => 'nullable|image|max:2048'
            ]);

            $usuario->nombre_completo = $request->nombre_completo;
            $usuario->email = $request->email;

            // Manejar avatar si se subió uno nuevo
            if ($request->hasFile('avatar')) {
                // Eliminar avatar anterior si existe
                if ($usuario->avatar && Storage::disk('s3')->exists($usuario->avatar)) {
                    Storage::disk('s3')->delete($usuario->avatar);
                }

                $avatarPath = $request->file('avatar')->store('avatars', 's3');
                $usuario->avatar = $avatarPath;
            }

            $usuario->save();

            return response()->json([
                'success' => true,
                'message' => 'Usuario actualizado correctamente',
                'data' => [
                    'id' => $usuario->id,
                    'nombre_completo' => $usuario->nombre_completo,
                    'email' => $usuario->email,
                    'avatar_url' => $usuario->avatar_url,
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar usuario'
            ], 500);
        }
    }

    // Eliminar usuario
    public function destroy($id)
    {
        try {
            $usuario = Usuario::findOrFail($id);

            // Eliminar avatar si existe
            if ($usuario->avatar && Storage::disk('s3')->exists($usuario->avatar)) {
                Storage::disk('s3')->delete($usuario->avatar);
            }

            $usuario->delete();

            return response()->json([
                'success' => true,
                'message' => 'Usuario eliminado correctamente'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar usuario'
            ], 500);
        }
    }
}