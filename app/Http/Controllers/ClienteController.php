<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helpers\JwtAuth;
use App\Models\Cliente;

class ClienteController extends Controller
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

            $clientes = Cliente::where('id_sucursal', $user->id_sucursal)->get();

            if($clientes->isEmpty()){
                return response()->json([
                    'message' => 'Registro no encontrado',
                    'status' => 'error'
                ], 404);
            }

            return response()->json([
                'Clientes' => $clientes,
                'status' => 'success'
            ], 200);

        }else{
            /** Error autenticación */
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
        //Metodo de autenticación
        $hash = $request->header('Authorization', null);

        $jwtAuth = new JwtAuth();
        $checkToken = $jwtAuth->checkToken($hash);

        if($checkToken){
            /** Datos de usuario */
            $user = $jwtAuth->checkToken($hash, true);

            $validated = $request->validate([
                'nombre' => 'required',
                'direccion' => 'required',
                'telefono' => 'required'
            ]);

            try {
                /** Inicia transacción */
                DB::beginTransaction();

                $cliente = Cliente::create([
                    'id_sucursal' => $user->id_sucursal,
                    'nombre' => $validated['nombre'],
                    'direccion' => $validated['direccion'],
                    'telefono' => $validated['telefono']
                ]);

                DB::commit();

                return response()->json([
                    'message' => 'Registro creado con exito',
                    'Cliente' => $cliente,
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
        //Metodo de autenticación
        $hash = $request->header('Authorization', null);

        $jwtAuth = new JwtAuth();
        $checkToken = $jwtAuth->checkToken($hash);

        if($checkToken){
            /** Busqueda de registro */
            $cliente = Cliente::where('id', $id)->first();

            if(!$cliente){
                return response()->json([
                    'message' => 'Registro no encontrado',
                    'status' => 'error'
                ], 404);
            }

            return response()->json([
                'Cliente' => $cliente,
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
        //Metodo de autenticación
        $hash = $request->header('Authorization', null);

        $jwtAuth = new JwtAuth();
        $checkToken = $jwtAuth->checkToken($hash);

        if($checkToken){
            /** Datos de entrada */
            $validated = $request->validate([
                'direccion' => 'required',
                'telefono' => 'required'
            ]);

            try {
                /** Inicia transacción */
                DB::beginTransaction();

                $cliente = Cliente::where('id', $id)->first();

                if(!$cliente){
                    return response()->json([
                        'message' => 'Registro no encontrado',
                        'status' => 'error'
                    ], 404);
                }

                $cliente->update([
                    'direccion' => $validated['direccion'],
                    'telefono' => $validated['telefono']
                ]);

                DB::commit();

                return response()->json([
                    'message' => 'Registro actualizado con exito',
                    'Cliente' => $cliente,
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
            /** Error de autorización */
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
        //Metodo de autenticación
        $hash = $request->header('Authorization', null);

        $jwtAuth = new JwtAuth();
        $checkToken = $jwtAuth->checkToken($hash);

        if($checkToken){
            /** Registo a eliminar */
            $cliente = Cliente::where('id', $id)->first();

            if(!$cliente){
                return response()->json([
                    'message' => 'Registro no encontrado',
                    'status' => 'error'
                ], 404);
            }

            $cliente->delete();

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
