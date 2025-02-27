<?php

namespace App\Http\Controllers\AdminControllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Helpers\JwtAuth;
use App\Models\AdminModels\Repartidor;

class RepartidorController extends Controller
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

            /** Verifica el rol del usuario */
            if(!$user->hasRole('admin')){
                return response()->json([
                    'message' => 'Acceso denegado',
                    'status' => 'error'
                ], 403);
            }

            $repartidores = Repartidor::all();

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

            /** Obtener datos de entrada */
            $validated = $request->validate([
                'id_sucursal' => 'required',
                'nombre' => 'required|unique:repartidores,nombre',
                'direccion' => 'required',
                'telefono' => 'required',
                'motocicleta_placas' => 'required|unique:repartidores,motocileta_placas'
            ]);

            try{
                /** Inicio transacción */
                DB::beginTransaction();

                /** Crear registro */
                $repartidor = Repartidor::created([
                    'id_sucursal' => $validated['id_sucursal'],
                    'nombre' => $validated['nombre'],
                    'direccion' => $validated['direccion'],
                    'telefono' => $validated['telefono'],
                    'motocicleta_placas' => $validated['matocicleta_placas']
                ]);

                DB::commit();

                return response()->json([
                    'Repartidor' => $repartidor,
                    'message' => 'Registro creado exitosamente',
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

            $repartidor = Repartidor::where('id', $id)->first;

            if(!$repartidor){
                return response()->json([
                    'message' => 'Registro no encontrado',
                    'status' => 'error'
                ], 404);
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

            $repartidor = Repartidor::find($id);

            if(!$repartidor){
                return response()->json([
                    'message' => 'Registro no encontrado',
                    'status' => 'error'
                ], 404);
            }
                
            $validated = $request->validate([
                'id_sucursal' => 'nullable|string',
                'direccion' => 'nullable|string',
                'telefono' => 'nullable|string',
                'motocicleta_placas' => 'nullable|unique:repartidores,motocicleta_placas' . $id
            ]);

            try{
                /** Inicia transacción */
                DB::beginTransaction();

                $repartidor->update(array_filter($validated));

                DB::commit();

                return response()->json([
                    'Repartidor' => $repartidor,
                    'message' => 'Registro actualizado',
                    'status' => 'success'
                ], 200);

            }catch(\Exception $e){
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

            $repartidor = Repartidor::find($id);

            if(!$repartidor){
                return response()->json([
                    'message' => 'Registro no encontrado',
                    'status' => 'error'
                ], 404);
            }

            try{
                /** Inicia transacción */
                DB::beginTransaction();

                $repartidor->delete();

                DB::commit();

                return response()->json([
                    'message' => 'Registro eliminado',
                    'status' => 'success'
                ], 200);

            }catch(\Exception $e){
                DB::rollBack();

                return response()->json([
                    'status' => 'error',
                    'message' => 'Error al eliminar el registro: ' .$e->getMessage()
                ], 200);
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
