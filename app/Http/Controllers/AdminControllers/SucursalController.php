<?php

namespace App\Http\Controllers\AdminControllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helpers\JwtAuth;
use App\Models\AdminModels\Sucursal;

class SucursalController extends Controller
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

            $sucursales = Sucursal::all();

            if(!$sucursales->isEmpty()){
                return response()->json([
                    'message' => 'Sin registros',
                    'status' => 'error'
                ], 404);
            }

            return response()->json([
                'Sucursales' => $sucursales,
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

            /** Datos de entrada */
            $validate = $request->validate([
                'nombre' => 'required|unique:sucursales,nombre',
                'direccion' => 'required',
                'telefono' => 'required'
            ]);

            try{
                /** Inicia transacción */
                DB::beginTransaction();

                $sucursal = Sucursal::created([
                    'nombre' => $validate['nombre'],
                    'direccion' => $validate['direccion'],
                    'telefono' => $validate['telefono']
                ]);

                DB::commit();

                return response()->json([
                    'Sucursal' => $sucursal,
                    'message' => 'Registro creado con exito',
                    'status' => 'success'
                ], 200);

            } catch (\Exception $e) {
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
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
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

            $sucursal = Sucursal::where('id', $id)->first();

            if(!$sucursal){
                return response()->json([
                    'message' => 'Registro no encontrado',
                    'status' => 'success'
                ], 404);
            }

            return response()->json([
                'Sucursal' => $sucursal,
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

            $sucursal = Sucursal::find($id);

            if(!$sucursal){
                return response()->json([
                    'message' => 'Registro no encontrado',
                    'status' => 'error'
                ], 404);
            }

            /** Datos de entrada */
            $validate = $request->validate([
                'direccion' => 'nullable',
                'telefono' => 'nullable'
            ]);

            try {
                /** Inicia transacción */
                DB::beginTransaction();

                $sucursal->update(array_filter($validate));

                DB::commit();

                return response()->json([
                    'Sucursal' => $sucursal,
                    'message' => 'Registro actualizado',
                    'status' => 'success'
                ], 200);

            } catch (\Exception $e) {
                DB::rollBack();

                return response()->json([
                    'status' => 'error',
                    'message' => 'Error en al actualizar el registro: ' .$e->getMessage()
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

            try {
                /** Inicia transacción */
                DB::beginTransaction();

                $sucursal = Sucursal::find($id);

                if(!$sucursal){
                    return response()->json([
                        'message' => 'Registro no encontrado',
                        'status' => 'error'
                    ], 404);
                }

                $sucursal->delete();

                DB::commit();

                return response()->json([
                    'message' => 'Registro eliminado',
                    'status' => 'error'
                ], 200);

            } catch (\Exception $e) {
                DB::rollBack();

                return response()->json([
                    'status' => 'error',
                    'message' => 'Error al eliminar el registro: ' .$e->getMessage()
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
}
