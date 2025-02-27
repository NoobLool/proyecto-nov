<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helpers\JwtAuth;
use App\Models\Cliente;
use App\Models\Pedido;
use App\Models\DetallePedido;
use App\Models\EstadoPedido;
use App\Models\Producto;


class PedidoController extends Controller
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
            //Datos de usuario
            $user = $jwtAuth->checkToken($hash, true);

            $pedidos = Pedido::where('id_sucursal', $user->id_sucursal)
                ->with([
                    'cliente' => function($query){
                        $query->select('id', 'nombre');
                    },
                    'estado' => function($query){
                        $query->select('id', 'id_pedido', 'estado', 'saldo_pendiente', 'saldo_abonado', 'updated_at');
                    }
                ])->get();

            if($pedidos->isEmpty()){
                return response()->json([
                    'message' => 'Sin registros',
                    'status' => 'error'
                ], 404);
            }

            return response()->json([
                'pedidos' => $pedidos,
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
    public function store(Request $request)
    {
        //Metodo de autenticación
        $hash = $request->header('Authorization', null);

        $jwtAuth = new JwtAuth();
        $checkToken = $jwtAuth->checkToken($hash);

        if($checkToken){
            //Se obtiene los datos del usuario
            $user = $jwtAuth->checkToken($hash, true);

            //Obtener datos de entrada y validarlos
            $validated = $request->validate([
                'id_cliente' => 'required',
                'tipo_pago' => 'required',
                'detalles' => 'required|array',
                'detalles.*.producto' => 'required',
                'detalles.*.cantidad' => 'required',
                'detalles.*.precio_unitario' => 'required'
            ]);

            try{
                
                //Se inicia la transacción
                DB::beginTransaction();

                $saldoNegativo = 'Cuenta sin pagar';
                $saldoPositivo = 'Cuenta pagada';

                //Se busca la información del cliente
                $cliente = Cliente::where('id', $validated['id_cliente'])
                    ->where('id_sucursal', $user->id_sucursal)
                    ->first();

                if(!$cliente){
                    return response()->json([
                        'message' => 'El cliente no existe',
                        'status' => 'error'
                    ], 404);
                }

                //Se calcula el total del pedido
                $total = 0;
                foreach ($validated['detalles'] as $detalles) {
                    $total += $detalles['cantidad'] * $detalles['precio_unitario'];
                }

                //Crear los datos de PedidoCliente
                $pedidoCliente = Pedido::create([
                    'id_sucursal' => $user->id_sucursal,
                    'id_cliente' => $validated['id_cliente'],
                    'tipo_pago' => $validated['tipo_pago'],
                    'total' => $total
                ]);

                //Se obtiene el nombre de los productos que se planea agregar, esto en una sola columna
                $nombreProductos = array_column($validated['detalles'], 'producto');

                //Se busca el producto en la tabla Productos
                $productos = Producto::whereIn('nombre', $nombreProductos)
                    ->where('id_sucursal', $user->id_sucursal)
                    ->get()->keyBy('nombre');

                foreach ($validated ['detalles'] as $detalles){
                    //Verificar que el producto exista, en caso de que no devolver error
                    if(!isset($productos[$detalles['producto']])){
                        throw new \Exception("Producto '{$detalles['producto']}' no encontrado");
                    }

                    $producto = $productos[$detalles['producto']];

                    //Calcular la nueva cantidad
                    $nuevaCantidad = $producto->cantidad - $detalles['cantidad'];

                    //Si la nueva cantidad sobrepasa a la existencia del inventario, se muestra un error
                    if($nuevaCantidad < 0){
                        throw new \Exception("Cantidad insuficiente en el inventario para el producto");
                    }

                    //Actualizar la cantidad del producto
                    $producto->cantidad = $nuevaCantidad;
                    $producto->save();

                    //Se calcula el subtotal
                    $subtotal = $detalles['cantidad'] * $detalles['precio_unitario'];

                    DetallePedido::create([
                        'id_pedido' => $pedidoCliente->id,
                        'producto' => $detalles['producto'],
                        'cantidad' => $detalles['cantidad'],
                        'precio_unitario' => $detalles['precio_unitario'],
                        'subtotal' => $subtotal
                    ]);
                }

                //Se crea la información de Estado, según el tipo de cliente
                $estadoPedidoClienteData = [
                    'id_pedido' => $pedidoCliente->id,
                    'saldo_pendiente' => $total,
                    'saldo_abonado' => 0
                ];

                if($validated['tipo_pago'] === 'Pago a meses'){
                    $estadoPedidoClienteData['estado'] = $saldoNegativo;
                }elseif($validated['tipo_pago'] === 'Contado'){
                    $estadoPedidoClienteData['estado'] = $saldoPositivo;
                }

                EstadoPedido::create($estadoPedidoClienteData);

                DB::commit();

                return response()->json([
                    'status' => 'success',
                    'message' => 'Información de pedido creada con exito',
                    'Pedido' => $pedidoCliente->load('detalles')
                ], 200);
                
            }catch(\Exception $e){
                DB::rollback();

                return response()->json([
                    'status' => 'error',
                    'message' => 'Error al crear el registro: ' . $e->getMessage()
                ], 500);
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
            //Se obtienen los datos del usuario
            $user = $jwtAuth->checkToken($hash, true);

            //Encontrar el registro por el id
            $pedido = Pedido::where('id', $id)
                ->where('id_sucursal', $user->id_sucursal)
                ->with([
                'cliente' => function($query){
                    $query->select('id', 'nombre');
                },
                'detalles' => function($query){
                    $query->select('id', 'id_pedido', 'producto', 'cantidad', 'precio_unitario', 'subtotal');
                },
                'estado' => function($query){
                    $query->select('id', 'id_pedido', 'estado', 'saldo_pendiente', 'saldo_abonado', 'updated_at');
                }
                ])->first();

            //En caso de no existir el registro
            if(!$pedido){
                return response()->json([
                    'status' => 'error',
                    'message' => 'Registro no encontrado'
                ], 404);
            }

            //Si el registro existe, se devuelven los datos
            return response()->json([
                'Pedido' => $pedido,
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
            //Validar datos de entrada
            $validated = $request->validate([
                'saldo_abonado' => 'required'
            ]);

            try{
                DB::beginTransaction();

                $saldoPositivo = 'Cuenta pagada';

                $saldoaPagar = $validated['saldo_abonado'];

                $estadoPedidoCliente = EstadoPedido::where('id_pedido', $id)
                    ->first();

                if(!$estadoPedidoCliente){
                    return response()->json([
                        'message' => 'Registro no encontrado',
                        'status' => 'error'
                    ], 404);
                }

                $saldoActual = $estadoPedidoCliente->saldo_abonado;
                $saldoPendiente = $estadoPedidoCliente->saldo_pendiente;

                $nuevoSaldoAbonado = $saldoActual + $saldoaPagar;
                $nuevoSaldoPendiente = $saldoPendiente - $saldoaPagar;

                if($nuevoSaldoPendiente < 0){
                    return response()->json([
                        'message' => 'El monto abonado excede el saldo pendiente',
                        'status' => 'error'
                    ], 400);
                }

                $estadoPedidoCliente->update([
                    'saldo_pendiente' => $nuevoSaldoPendiente,
                    'saldo_abonado' => $nuevoSaldoAbonado
                ]);

                // Usar una tolerancia para la comparación de números decimales
                $tolerancia = 0.01;

                if (abs($nuevoSaldoAbonado - $nuevoSaldoPendiente) < $tolerancia) {
                    $estadoPedidoCliente->update([
                        'estado' => $saldoPositivo
                    ]);
                }

                DB::commit();

                $data = array(
                    'message' => 'Saldo abonado con éxito',
                    'status' => 'success',
                    'EstadoPedidoCliente' => $estadoPedidoCliente
                );

                return response()->json($data, 200);

            }catch(\Exception $e){
                //En caso de algun error, no se efectua ningun cambio y se devuelve mensaje
                DB::rollback();

                return response()->json([
                    'status' => 'error',
                    'message' => 'Error al actualizar el registro: ' . $e->getMessage()
                ], 500);
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
            //Se obtiene la información del usuario
            $user = $jwtAuth->checkToken($hash, true);

            try{
                //Inicio de transaccion
                DB::beginTransaction();

                //Obtener el registro 
                $pedidoCliente = Pedido::where('id', $id)
                    ->where('id_sucursal', $user->id_sucursal)
                    ->first();

                // Verificar si el pedidoCliente existe
                if (!$pedidoCliente) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'PedidoCliente no encontrado'
                    ], 404);
                }

                $detalles = DetallePedido::where('id_pedido', $pedidoCliente->id)->get();

                foreach($detalles as $detalle){
                    //Se restaura las existencias del producto
                    $producto = Producto::where('nombre', $detalle->producto)
                        ->where('id_sucursal', $user->id_sucursal)
                        ->first();

                    //Si se encuentra el registro, se retorna la cantidad que existia con anterioridad respecto a la cantidad de productos
                    if ($producto) {
                        $producto->cantidad += $detalle->cantidad;
                        $producto->save();
                    }
                }

                // Eliminar el registro
                $pedidoCliente->delete();

                // Confirmar la transacción
                DB::commit();
                
                $data = array(
                    'PedidoCliente' => $pedidoCliente,
                    'status' => 'success',
                    'message' => 'Registro eliminado con éxito'
                );

                return response()->json($data, 200);

            }catch(\Exception $e){
                //Revertir la transacción en caso de error
                DB::rollback();

                return response()->json([
                    'status' => 'error',
                    'message' => 'Error al eliminar los datos: ' . $e->getMessage()
                ], 500);
            }
        }else{
            //Error de autenticación
            return response()->json([
                'message' => 'Login incorrecto',
                'status' => 'error'
            ], 401);
        }
    }
}
