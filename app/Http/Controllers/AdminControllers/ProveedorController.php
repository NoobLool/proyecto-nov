<?php

namespace App\Http\Controllers\AdminControllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helpers\JwtAuth;
use App\Models\AdminModels\Proveedor;

class ProveedorController extends Controller
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

            $proveedores = Proveedor::all();

            if($proveedores->isEmpty()){
                return response()->json([
                    'message' => 'Sin registros',
                    'status' => 'error'
                ], 404);
            }

            return response()->json([
                'Proveedores' => $proveedores,
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

            $validated = $request->validate([
                'nombre' => 'required|unique',
                'telefono' => 'required'
            ]);

            try {
                /** Inicia transacción */
                DB::beginTransaction();

                $proveedor = Proveedor::created([
                    'nombre' => $validated['nombre'],
                    'telefono' => $validated['telefono']
                ]);

                DB::commit();

                return response()->json([
                    'Proveedor' => $proveedor,
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

            $proveedor = Proveedor::where('id', $id)->first();

            if(!$proveedor){
                return response()->json([
                    'message' => 'Registro no encontrado',
                    'status' => 'error'
                ], 404);
            }

            return response()->json([
                'Proveedor' => $proveedor,
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

            $proveedor = Proveedor::find($id);

            if(!$proveedor){
                return response()->json([
                    'message' => 'Registro no encontrado',
                    'status' => 'error'
                ], 404);
            }

            $validated = $request->validate([
                'telefono' => 'required'
            ]);

            try {
                /** Inicia transacción */
                DB::beginTransaction();

                $proveedor->update([
                    'telefono' => $validated['telefono']
                ]);

                DB::commit();

                return response()->json([
                    'message' => 'Registro actualizado',
                    'status' => 'success'
                ], 200);

            } catch (\Exception $e) {
                DB::rollBack();

                return response()->json([
                    'status' => 'error',
                    'message' => 'Error al actualizar el registro: ' .$e->getMessage()
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

                $proveedor = Proveedor::find($id);

                if(!$proveedor){
                    return response()->json([
                        'message' => 'Registro no encontrado',
                        'status' => 'error'
                    ], 404);
                }

                DB::commit();

                return response()->json([
                    'message' => 'Registro eliminado',
                    'status' => 'success'
                ], 200);

            } catch (\Exception $e) {
                DB::rollBack();

                return response()->json([
                    'status' => 'error',
                    'message' => 'Erro al eliminar el registro: ' .$e->getMessage()
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
