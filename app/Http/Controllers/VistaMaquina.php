<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\JwtAuth;
use App\Models\AdminModels\Maquina;

class VistaMaquina extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request){
        //Metodo de autenticación
        $hash = $request->header('Authorization', null);

        $jwtAuth = new JwtAuth();
        $checkToken = $jwtAuth->checkToken($hash);

        if($checkToken){
            /** Se obtienen datos */
            $maquinas = Maquina::all();

            if($maquinas->isEmpty()){
                return response()->json([
                    'message' => 'Sin registros',
                    'status' => 'error'
                ], 404);
            }

            return response()->json([
                'Maquinas' => $maquinas,
                'status' => 'success'
            ], 200);
        }else{
            /** Error de auteticación */
            return response()->json([
                'message' => 'Login incorrecto',
                'status' => 'error'
            ], 401);
        }

    }
}
