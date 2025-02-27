<?php

namespace App\Http\Controllers\AdminControllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\AdminModels\Maquina;
use App\Helpers\JwtAuth;

class MaquinasController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //Metodos de autenticación
        $hash = $request->header('Authorization', null);

        $jwtAuth = new JwtAuth();
        $checkToken = $jwtAuth->checkToken($hash);

        if($checkToken){
            /** Datos de usuario */
            $user = $jwtAuth->checkToken($hash, true);

            if(!$user->hasRole('admin')){
                return response()->json([
                    'message' => 'Acceso denegado',
                    'status' => 'error'
                ], 403);
            }

            /** Información */
            $maquinas = Maquina::all();

            if($maquinas->isEmpty()){
                return response()->json([
                    'message' => 'Registro no encontrado',
                    'status' => 'error'
                ], 404);
            }

            return response()->json([
                'maquinas' => $maquinas,
                'status' => 'success'
            ], 200);

        }else{
            /** Error en el login */
            return response()->json([
                'message'=> 'Login incorrecto',
                'status' => 'error'
            ], 401);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //Metodos de autenticación
        $hash = $request->header('Authorization', null);

        $jwtAuth = new JwtAuth();
        $checkToken = $jwtAuth->checkToken($hash);

        if($checkToken){
            /** Datos de usuario */
            $user = $jwtAuth->checkToken($hash, true);

            if(!$user->hasRole('admin')){
                return response()->json([
                    'message' => 'Acceso denegado',
                    'status' => 'error'
                ], 403);
            }
            
            /** Datos del entrada */
            $validated = $request->validate([
                'nombre' => 'required',
                'materia_prima' => 'required',
                'ubicacion' => 'required'
            ]);

            try{
                /** Inicia transacción */
                DB::beginTransaction();

                /** Crear registro */
                $maquina = Maquina::created([
                    'nombre' => $validated['nombre'],
                    'materia_prima' => $validated['materia_prima'],
                    'ubicación' => $validated['ubicacion']
                ]);

                DB::commit();
    
                return response()->json([
                    'Maquina' => $maquina,
                    'message' => 'Registro creado con exito',
                    'status' => 'success'
                ], 200);

            }catch(\Exception $e){
                DB::rollBack();

                return response()->json([
                    'status' => 'error',
                    'message' => 'Error al crear el registro: ' . $e->getMessage()
                ], 500);
            }

        }else{
            /** Error de autenticación */
            return response()->json([
                'message' => 'Login incorrecto',
                'status' => 'error'
            ], 401);
        }

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        //Metodo de autenticación
        $hash = $request->header('Authorization', null);

        $jwtAuth = new JwtAuth();
        $checkToken = $jwtAuth->checkToken($hash);

        if($checkToken){
            /** Datos de usuario */
            $user = $jwtAuth->checkToken($hash, true);

            if(!$user->hasRole('admin')){
                return response()->json([
                    'message' => 'Acceso denegado',
                    'status' => 'error'
                ], 403);
            }
            
            /** Buscar registro */
            $maquina = Maquina::where('id', $id)->first();

            if(!$maquina){
                return response()->json([
                    'message' => 'Registro no encontrado',
                    'status' => 'error'
                ], 404);
            }

            return response()->json([
                'Maquina' => $maquina,
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

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //Metodos de autenticación
        $hash = $request->header('Authorization', null);

        $jwtAuth = new JwtAuth();
        $checkToken = $jwtAuth->checkToken($hash);

        if($checkToken){
            /** Datos de usuario */
            $user = $jwtAuth->checkToken($hash, true);

            if(!$user->hasRole('admin')){
                return response()->json([
                    'message' => 'Acceso denegado',
                    'status' => 'error'
                ], 403);
            }

            /** Datos de entrada */
            $validated = $request->validate([
                'ubicacion' => 'required'
            ]);

            try{
                /** Inicia transacción */
                DB::beginTransaction();

                $maquina = Maquina::where('id', $id)->first();

                if(!$maquina){
                    return response()->json([
                        'message' => 'Registro no encontrado',
                        'status' => 'error'
                    ], 404);
                }

                $maquina->update([
                    'ubicacion' => $validated['ubicacion']
                ]);

                DB::commit();

                return response()->json([
                    'Maquina' => $maquina,
                    'message' => 'Registro actualizado con exito',
                    'status' => 'success'
                ], 200);

            }catch(\Exception $e){
                DB::rollBack();

                return response()->json([
                    'status' => 'error',
                    'message' => 'Error al crear el registro: ' .$e->getMessage()
                ], 500);
            }

        }else{
            /** Error de autenticación */
            return response()->json([
                'message' => 'Login incorrecto',
                'status' => 'error'
            ], 401);
        }

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        //Metodo de autentificación
        $hash = $request->header('Authorization', null);

        $jwtAuth = new JwtAuth();
        $checkToken = $jwtAuth->checkToken($hash);

        if($checkToken){
            /** Datos de usuario */
            $user = $jwtAuth->checkToken($hash, true);

            if(!$user->hasRole('admin')){
                return response()->json([
                    'message' => 'Acceso denegado',
                    'status' => 'error'
                ], 403);
            }

            /** Datos de registro */
            $maquina = Maquina::where('id', $id)->first();

            if(!$maquina){
                return response()->json([
                    'message' => 'Registro no encontrado',
                    'status' => 'error'
                ], 404);
            }

            return response()->json([
                'message' => 'Registro eliminado con exito',
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
