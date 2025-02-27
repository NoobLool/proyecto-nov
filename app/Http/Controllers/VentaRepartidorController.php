<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helpers\JwtAuth;
use App\Models\DetalleVentaRepartidor;
use App\Models\VentaRepartidor;
use App\Models\Producto;

class VentaRepartidorController extends Controller
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

            $ventas = VentaRepartidor::where('id_sucursal', $user->id_sucursal)
                ->get();

            if($ventas->isEmpty()){
                return response()->json([
                    'message' => 'Sin registros',
                    'status' => 'error'
                ], 404);
            }

            return response()->json([
                'Ventas' => $ventas,
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
        //Metodo de autenticación
        $hash = $request->header('Authorization', null);

        $jwtAuth = new JwtAuth();
        $checkToken = $jwtAuth->checkToken($hash);

        if($checkToken){
            /** Datos de usuario */
            $user = $jwtAuth->checkToken($hash, true);

            /** Datos de entrada */
            $validated = $request->validate([
                'id_repartidor' => 'required',
                'detalles' => 'required|array',
                'detalles.*.producto' => 'required',
                'detalles.*.cantidad' => 'required',
                'detalles.*.precio_unitario' => 'required'
            ]);

            try {
                //Inicia transacción
                DB::beginTransaction();

                $total = 0;

                foreach($validated['detalles'] as $detalle){
                    $total += $detalle['cantidad'] * $detalle['precio_unitario'];
                }

                $venta = VentaRepartidor::create([
                    'id_repartidor' => $validated['id_repartidor'],
                    'id_sucursal' => $user->id_sucursal,
                    'total' => $total
                ]);

                /** Se agrupan los productos a registrar en una sola consulta */
                $nombreProductos = array_column($validated['detalles'], 'producto');

                /** Se buscan los registros */
                $productos = Producto::whereIn('nombre', $nombreProductos)
                    ->whereHas('user', function($query) use ($user){
                        $query->where('id_sucursal', $user->id_sucursal);
                    })
                    ->get()->keyBy('nombre');
                    
                /** Creación de detalles */
                foreach($validated['detalles'] as $detalle){
                    /** Existe el registro */
                    if(!isset($productos[$detalle['producto']])){
                        throw new \Exception("Producto '{$detalle['producto']}' no encontrado");
                    }

                    $subtotal = $detalle['cantidad'] * $detalle['precio_unitario'];

                    DetalleVentaRepartidor::create([
                        'id_venta_repartidor' => $venta->id,
                        'producto' => $detalle['producto'],
                        'cantidad' => $detalle['cantidad'],
                        'precio_unitario' => $detalle['precio_unitario'],
                        'subtotal' => $subtotal
                    ]);
                }

                DB::commit();

                return response()->json([
                    'message' => 'success',
                    'Venta' => $venta->load('detalles'),
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
            /** Datos de usuario */
            $user = $jwtAuth->checkToken($hash, true);

            $venta = VentaRepartidor::where('id', $id)
                ->where('id_sucursal', $user->id_sucursal)
                ->with(['repartidor','detalles'])
                ->first();

            if(!$venta){
                return response()->json([
                    'message' => 'Registro no encontrado',
                    'status' => 'error' 
                ], 404);
            }

            return response()->json([
                'Venta' => $venta,
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
        return response()->json([
            'message' => 'Método no permitido',
            'status' => 'error'
        ], 405);
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
            /** Datos de usuario */
            $user = $jwtAuth->checkToken($hash, true);

            try {
                //Inicia transacción
                Db::beginTransaction();

                /** Se busca el registro */
                $venta = VentaRepartidor::where('id', $id)
                    ->where('id_sucursal', $user->id_sucursal)
                    ->first();

                /** Se elimina registro */
                $venta->delete();

                DB::commit();

                return response()->json([
                    'Venta' => $venta,
                    'message' => 'Registro eliminado',
                    'status' => 'success'
                ], 200);

            } catch (\Exception $e) {
                DB::rollBack();

                return response()->json([
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
