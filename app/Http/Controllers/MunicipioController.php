<?php
namespace App\Http\Controllers;

use App\Models\Municipios;
use Illuminate\Http\Request;

class MunicipioController extends Controller
{
    public function index()
    {
        try {
            $municipios = Municipios::select('id', 'nombre')
                ->orderBy('nombre', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $municipios
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener municipios'
            ], 500);
        }
    }
}