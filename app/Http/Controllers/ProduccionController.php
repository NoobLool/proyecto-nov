<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helpers\JWTAuth;
use App\Models\Insumo;
use App\Models\Producto;
use App\Models\DetalleProduccion;
use App\Models\Produccion;

class ProduccionController extends Controller
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
            /** Datos del usuario */
            $user = $jwtAuth->checkToken($hash, true);

            /** Informacion de produccion */
            $produccion = Produccion::where('id_sucursal', $user->id_sucursal)->get();

            if($produccion->isEmpty()){
                return response()->json([
                    'message' => 'Sin registros',
                    'status' => 'error'
                ], 404);
            }else{
                return response()->json([
                    'produccion' => $produccion,
                    'status' => 'success'
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
            /** Información de usuario */
            $user = $jwtAuth->checkToken($hash, true);

            /** Datos de entrada */
            $validate = $request->validate([
                'id_maquina' => 'required|exists:maquinas,id',
                'insumo' => 'required',
                'producto' => 'required',
                'cantidad_insumo' => 'required',
                'cantidad_producto' => 'required'
            ]);

            try{
                /** Inicia de transacción */
                DB::beginTransaction();

                /** Crear registro de producción */
                $produccion = Produccion::create([
                    'id_sucursal' => $user->id_sucursal,
                    'id_maquina' => $validate['id_maquina']
                ]);

                /** Crear registro de detalles de producción */
                $detalles = DetalleProduccion::create([
                    'id_produccion' => $produccion->id,
                    'insumo' => $validate['insumo'],
                    'producto' => $validate['producto'],
                    'cantidad_insumo' => $validate['cantidad_insumo'],
                    'cantidad_producto' => $validate['cantidad_producto']
                ]);

                /** Actualizar cantidad de insumos  */
                $insumo = Insumo::where('nombre', $validate['insumo'])
                    ->where('id_sucursal', $user->id_sucursal)
                    ->first();

                if($insumo){
                    $insumo->cantidad -= $validate['cantidad_insumo'];
                    $insumo->save();
                }else{
                    throw new \Exception (" Elemento no encontrado ");
                }

                /** Actualizar cantidad de productos */
                $producto = Producto::where('nombre', $validate['producto'])
                    ->where('id_sucursal', $user->id_sucursal)
                    ->first();

                if($producto){
                    $producto->cantidad += $validate['cantidad_producto'];
                    $producto->save();
                }else{
                    throw new \Exception (" Elemento no encontrado ");
                }

                /** Confirma la transacción */
                DB::commit();

                return response()->json([
                    'produccion' => $produccion,
                    'detalles' => $detalles,
                    'status' => 'success'
                ], 200);

            }catch(\Exception $e){
                DB::rollback();

                return response()->json([
                    'status' => 'error',
                    'message' => 'Error al crear el registro: ' . $e->getMessage()
                ], 500);
            }

        }else{
            /** Error de autenticación */
            return response()->json([
                'message' => 'login incorrecto',
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
            //Información del usuario
            $user = $jwtAuth->checkToken($hash, true);

            $produccion = Produccion::where('id', $id)
                ->where('id_sucursal', $user->id_sucursal)
                ->with(['maquinas','detalles'])
                ->first();

            if(!$produccion){
                return response()->json([
                    'message' => 'Datos no encontrados',
                    'status' => 'error'
                ], 404);
            }else{
                return response()->json([
                    'Produccion' => $produccion,
                    'status' => 'success'
                ], 200);
            }
        }else{
            /** Error de autenticación */
            return response()->json([
                'message' => 'login incorrecto',
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
        return response()->json([
            'message' => 'Metodo no permitido',
            'status' => 'error'
        ], 403);
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
            /** Información de usuario */
            $user = $jwtAuth->checkToken($hash, true);

            try{
                /** Inicia la transacción */
                DB::beginTransaction();

                /** Se busca el registro */
                $produccion = Produccion::where('id', $id)
                    ->where('id_sucursal', $user->id_sucursal)
                    ->first();

                if(!$produccion){
                    return response()->json([
                        'message' => 'Registro no encontrado',
                        'status' => 'error'
                    ], 404);
                }

                /** Detalles vinculados al registro */
                $detalles = $produccion->detalles;

                /** Restaura las cantidades de insumos y productos */
                foreach ($detalles as $detalle){
                    $insumo = Insumo::where('nombre', $detalle->insumo)
                        ->where('id_sucursal', $user->id_sucursal)
                        ->first();
                    $producto = Producto::where('nombre', $detalle->producto)
                        ->where('id_sucursal', $user->id_sucursal)
                        ->first();
                    if($insumo && $producto){
                        $insumo->cantidad += $detalle->cantidad_insumo;
                        $insumo->save();
                        $producto->cantidad -= $detalle->cantidad_producto;
                        $producto->save();
                    }
                }

                /** Eliminar registro */
                $produccion->delete();

                /** Confirma transacción */
                DB::commit();

                return response()->json([
                    'produccion' => $produccion,
                    'message' => 'Registrado elimnado con exito',
                    'status' => 'success'
                ], 200);

            }catch(\Exception $e){
                /** Revertir la transacción */
                DB::rollBack();

                return response()->json([
                    'status' => 'error',
                    'message' => 'Error al eliminar los datos: ' . $e->getMessage()
                ], 200);
            }
        }else{
            /** Error de auteticación */
            return response()->json([
                'message' => 'Login incorrecto',
                'satutus' => 'error'
            ], 401);
        }
    }
}
