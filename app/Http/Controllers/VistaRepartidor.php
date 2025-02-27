<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\JwtAuth;
use App\Models\AdminModels\Repartidor;

class VistaRepartidor extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //Metodo de autenticación
        $hash = $request->header('Authorization', null);

        $jwtAuth = new JwtAuth();
        $checkToken = $jwtAuth->checkToken($hash);

        if($checkToken){
            /** Datos de usuario */
            $user = $jwtAuth->checkToken($hash, true);

            $repartidores = Repartidor::where('id_sucursal', $user->id_sucursal)->get();

            if($repartidores->isEmpty()){
                return response()->json([
                    'message' => 'Sin registros',
                    'status' => 'error'
                ], 404);
            }

            return response()->json([
                'Repartidores' => $repartidores,
                'status' => 'success'
            ], 200);

        }else{
            /** Error de autenticación */
            return response()->json([
                'message' => 'Login incorrecto',
                'status' => 'error'
            ], 401);
        }
    }
}
