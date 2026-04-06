<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\Usuario;

class PasswordResetController extends Controller
{
    // Enviar enlace de recuperación
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:usuarios,email',
        ], [
            'email.required' => 'El correo es obligatorio',
            'email.email' => 'Formato de correo inválido',
            'email.exists' => 'Este correo no está registrado',
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json([
                'message' => 'Correo de recuperación enviado exitosamente'
            ], 200);
        }

        return response()->json([
            'message' => 'No se pudo enviar el correo. Intenta nuevamente.'
        ], 500);
    }

    // Resetear la contraseña
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email|exists:usuarios,email',
            'password' => 'required|min:8|confirmed',
        ], [
            'token.required' => 'El token es obligatorio',
            'email.required' => 'El correo es obligatorio',
            'email.email' => 'Formato de correo inválido',
            'email.exists' => 'Este correo no está registrado',
            'password.required' => 'La contraseña es obligatoria',
            'password.min' => 'La contraseña debe tener mínimo 8 caracteres',
            'password.confirmed' => 'Las contraseñas no coinciden',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (Usuario $usuario, string $password) {
                $usuario->forceFill([
                    'password' => $password, // Se hashea automáticamente por el mutador
                ])->save();

                $usuario->tokens()->delete(); // Cierra todas las sesiones
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json([
                'message' => 'Contraseña actualizada correctamente'
            ], 200);
        }

        return response()->json([
            'message' => 'Token inválido o expirado'
        ], 400);
    }
}