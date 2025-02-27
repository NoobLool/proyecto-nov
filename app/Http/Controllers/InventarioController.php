<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Helpers\JwtAuth;
use App\Models\Insumo;
use App\Models\Producto;

class InventarioController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //Metodos de autenticación de usuario
        $hash = $request->header('Authorization', null);

        $jwtAuth = new JwtAuth();
        $checkToken = $jwtAuth->checkToken($hash);

        if($checkToken){
            //Información del usuario
            $user = $jwtAuth->checkToken($hash, true);

            //Información de los insumos
            $insumos = Insumo::where('id_sucursal', $user->id_sucursal)->get();

            //Información de los productos 
            $productos = Producto::where('id_sucursal', $user->id_sucursal)->get();

            if($insumos->isEmpty() && $productos->isEmpty()){
                return response()->json([
                    'message' => 'No existen registros',
                    'status' => 'error'
                ], 404);
            }

            return response()->json([
                'Insumos' => $insumos,
                'Productos' => $productos,
                'status' => 'success'
            ], 200);
        }else{
            //Error de autenticación
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
    public function storeInsumo(Request $request)
    {
        //Metodo de autenticación
        $hash = $request->header('Authorization', null);

        $jwtAuth = new JwtAuth();
        $checkToken = $jwtAuth->checkToken($hash);

        if($checkToken){
            //Información del usuario
            $user = $jwtAuth->checkToken($hash, true);

            $validated = $request->validate([
                'detalles' => 'required|array',
                'detalles.*.nombre' => ['required',Rule::unique('insumos', 'nombre')->where(function($query) use ($user){
                    return $query->where('id_sucursal', $user->id_sucursal);
                })],
                'detalles.*.cantidad' => 'required',
                'detalles.*.unidad_medicion' => 'required',
                'detalles.*.precio_unitario' => 'required'
            ]);

            try{
                //Inicia transacción
                DB::beginTransaction();

                $registros = [];

                foreach($validated['detalles'] as $detalle){

                    $total = $detalle['cantidad'] * $detalle['precio_unitario'];

                    $registro = Insumo::create([
                        'id_sucursal' => $user->id_sucursal,
                        'nombre' => $detalle['nombre'],
                        'cantidad' => $detalle['cantidad'],
                        'unidad_medicion' => $detalle['unidad_medicion'],
                        'precio_unitario' => $detalle['precio_unitario'],
                        'total' => $total
                    ]);

                    $registros[] = $registro;

                }

                //Fin transacción
                DB::commit();

                return response()->json([
                    'Registros' => $registros,
                    'status' => 'success'
                ], 200);

            }catch(\Exception $e){
                DB::rollback();

                return response()->json([
                    'message' => 'Error al crear el registro: ' . $e->getMessage(),
                    'status' => 'error'    
                ], 500);
            }
        }else{
            //Error de autentiación
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
    public function storeProducto(Request $request)
    {
        //Metodo de autenticación
        $hash = $request->header('Authorization', null);

        $jwtAuth = new JwtAuth();
        $checkToken = $jwtAuth->checkToken($hash);

        if($checkToken){
            //Información del usuario
            $user = $jwtAuth->checkToken($hash, true);

            $validated = $request->validate([
                'detalles' => 'required|array',
                'detalles.*.nombre' => ['required',Rule::unique('productos', 'nombre')->where(function($query) use ($user){
                    return $query->where('id_sucursal', $user->id_sucursal);
                })],
                'detalles.*.cantidad' => 'required',
                'detalles.*.unidad_medicion' => 'required'
            ]);

            try{
                //Inicia transacción
                DB::beginTransaction();

                $registros = [];

                foreach($validated['detalles'] as $detalle){

                    $registro = Producto::create([
                        'id_sucursal' => $user->id_sucursal,
                        'nombre' => $detalle['nombre'],
                        'cantidad' => $detalle['cantidad'],
                        'unidad_medicion' => $detalle['unidad_medicion']
                    ]);

                    $registros[] = $registro; 

                }

                //Fin transacción
                DB::commit();

                return response()->json([
                    'Registros' => $registros,
                    'status' => 'success'
                ], 200);

            }catch(\Exception $e){
                DB::rollback();

                return response()->json([
                    'message' => 'Error al crear el registro: ' . $e->getMessage(),
                    'status' => 'error'    
                ], 500);
            }
        }else{
            //Error de autentiación
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
            //Información de usuario
            $user = $jwtAuth->checkToken($hash, true);

            //Obtener a que tipo de elemento se quiere acceder
            $validated = $request->validate([
                'tipo' => 'required'
            ]);

            if($validated['tipo'] === 'Insumo'){
                //Información del insumo
                $insumo = Insumo::where('id', $id)->where('id_sucursal', $user->id_sucursal)->first();

                if(!$insumo){
                    return response()->json([
                        'message' => 'Insumo no encontrado',
                        'status' => 'error'
                    ], 404);
                }

                return response()->json([
                    'Insumo' => $insumo,
                    'status' => 'success'
                ], 200);

            }elseif($validated['tipo'] === 'Producto'){
                //Información del producto
                $producto = Producto::where('id', $id)->where('id_sucursal', $user->id_sucursal)->first();

                if(!$producto){
                    return response()->json([
                        'message' => 'Producto no encontrado',
                        'status' => 'error'
                    ], 404);
                }

                return response()->json([
                    'Producto' => $producto,
                    'status' => 'success'
                ], 200);
            }

        }else{
            //Error de autenticación
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
    public function updateInsumo(Request $request, $id)
    {
        //Metodo de autenticación
        $hash = $request->header('Authorization', null);

        $jwtAuth = new JwtAuth();
        $checkToken = $jwtAuth->checkToken($hash);

        if($checkToken){
            //Datos de usuario
            $user = $jwtAuth->checkToken($hash, true);

            $validated = $request->validate([
                'cantidad' => 'required',
                'precio_unitario' => 'required'     
            ]);

            try{
                //Inicia transacción
                DB::beginTransaction();
                
                $insumo = Insumo::where( 'id', $id)
                    ->where('id_sucursal', $user->id_sucursal)
                    ->first();

                $total = $validated['cantidad'] * $validated['precio_unitario'];
                
                if(!$insumo){
                    return response()->json([
                        'message' => 'Registro no encontrado',
                        'status' => 'error'
                    ], 404);
                }

                $insumo->update([
                    'cantidad' => $validated['cantidad'],
                    'precio_unitario' => $validated['precio_unitario'],
                    'total' => $total
                ]);

                DB::commit();

                return response()->json([
                    'Insumo' => $insumo,
                    'status' => 'success'
                ], 200);

            }catch (\Exception $e){
                DB::rollback();

                return response()->json([
                    'message' => 'Error al actualizar el registro: ' . $e->getMessage(),
                    'status' => 'error'
                ], 200);
            }

        }else{
            //Error autenticación
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
    public function updateProducto(Request $request, $id)
    {
        //Metodo de autenticación
        $hash = $request->header('Authorization', null);

        $jwtAuth = new JwtAuth();
        $checkToken = $jwtAuth->checkToken($hash);

        if($checkToken){
            //Datos de usuario
            $user = $jwtAuth->checkToken($hash, true);

            $validated = $request->validate([
                'cantidad' => 'required'     
            ]);

            try{
                //Inicia transacción
                DB::beginTransaction();
                
                $producto = Producto::where( 'id', $id)
                    ->where('id_sucursal', $user->id_sucursal)
                    ->first();
                
                if(!$producto){
                    return response()->json([
                        'message' => 'Registro no encontrado',
                        'status' => 'error'
                    ], 404);
                }

                $producto->update([
                    'cantidad' => $validated['cantidad']
                ]);

                DB::commit();

                return response()->json([
                    'Insumo' => $producto,
                    'status' => 'success'
                ], 200);

            }catch (\Exception $e){
                DB::rollback();

                return response()->json([
                    'message' => 'Error al actualizar el registro: ' . $e->getMessage(),
                    'status' => 'error'
                ], 200);
            }

        }else{
            //Error autenticación
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
            //Información usuario
            $user = $jwtAuth->checkToken($hash, true);

            $validated = $request->validate([
                'tipo' => 'required'
            ]);

            if($validated['tipo'] === 'Insumo'){
                //Información del insumo
                $insumo = Insumo::where('id', $id)
                    ->where('id_sucursal', $user->id_sucursal)
                    ->first();

                //Elemento no encontrado
                if(!$insumo){
                    return response()->json([
                        'message' => 'Insumo no encotrado',
                        'status' => 'error'
                    ], 404);
                }else{
                    //Se elimina el registro
                    $insumo->delete();

                    return response()->json([
                        'Insumo' => $insumo,
                        'message' => 'Registro eliminado',
                        'status' => 'success'
                    ], 200);
                }

            }elseif($validated['tipo'] === 'Producto'){
                //Información del producto
                $producto = Producto::where('id', $id)
                    ->where('id_sucursal', $user->id_sucursal)
                    ->first();

                //Elemento no encontrado
                if(!$producto){
                    return response()->json([
                        'message' => 'Producto no encotrado',
                        'status' => 'error'
                    ], 404);
                }else{
                    //Se elimina el registro
                    $producto->delete();

                    return response()->json([
                        'Insumo' => $producto,
                        'message' => 'Registro eliminado',
                        'status' => 'success'
                    ], 200);
                }
            }

        }else{
            return response()->json([
                'message' => 'Login incorrecto',
                'status' => 'error'
            ], 401);
        }
    }
}
