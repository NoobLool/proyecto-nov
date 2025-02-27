<?php

use App\Http\Controllers\ClienteController;
use App\Http\Controllers\InventarioController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\ProduccionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VentaRepartidorController;
use App\Http\Controllers\VistaMaquina;
use App\Http\Controllers\VistaRepartidor;
use Illuminate\Http\Request;
use Illuminate\Routing\RouteGroup;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

/** Login de usuario */
Route::post('user/login', [UserController::class, 'login']);

/** Registro de usuarios */
Route::post('user/register', [UserController::class, 'register']);

/** Bandera */
Route::get('bandera', function(){
    return response('Si funciona', 200);
});

/** Controlador de Inventario */
Route::group(['prefix' => 'user/inventario'], function(){
    Route::get('/', [InventarioController::class, 'index']);
    Route::post('/insumo', [InventarioController::class, 'storeInsumo']);
    Route::post('/producto', [InventarioController::class, 'storeProducto']);
    Route::get('/{id}', [InventarioController::class, 'show']);
    Route::put('/insumo/{id}', [InventarioController::class, 'updateInsumo']);
    Route::put('/producto/{id}', [InventarioController::class, 'updateProducto']);
    Route::delete('/{id}', [InventarioController::class, 'destroy']);  
});

/** Visualizar información de Maquinas */
Route::get('user/maquinas', [VistaMaquina::class, 'index']);

/** Controlador de Producción */
Route::group(['prefix' => 'user/produccion'], function(){
    Route::get('/', [ProduccionController::class, 'index']);
    Route::post('/store', [ProduccionController::class, 'store']);
    Route::get('/{id}', [ProduccionController::class, 'show']);
    Route::put('/{id}', [ProduccionController::class, 'update']);
    Route::delete('/{id}', [ProduccionController::class, 'destroy']);
});

/** Controlador de Clientes */
Route::group(['prefix' => 'user/clientes'], function(){
    Route::get('/', [ClienteController::class, 'index']);
    Route::post('/store', [ClienteController::class, 'store']);
    Route::get('/{id}', [ClienteController::class, 'show']);
    Route::put('/{id}', [ClienteController::class, 'update']);
    Route::delete('/{id}', [ClienteController::class, 'destroy']);
});

/** Controlador de Pedidos */
Route::group(['prefix' => 'user/pedidos'], function(){
    Route::get('/', [PedidoController::class, 'index']);
    Route::post('/store', [PedidoController::class, 'store']);
    Route::get('/{id}', [PedidoController::class, 'show']);
    Route::put('/{id}', [PedidoController::class, 'update']);
    Route::delete('/{id}', [PedidoController::class, 'destroy']);
});

/** Visualizar información de Repartidores */
Route::get('user/repartidores', [VistaRepartidor::class, 'index']);

/** Controlador de Ventas a Repartidores */
Route::group(['prefix' => 'user/ventasRepartidor'], function(){
    Route::get('/', [VentaRepartidorController::class, 'index']);
    Route::post('/store', [VentaRepartidorController::class, 'store']);
    Route::get('/{id}', [VentaRepartidorController::class, 'show']);
    Route::put('/{id}', [VentaRepartidorController::class, 'update']);
    Route::delete('/{id}', [VentaRepartidorController::class, 'destroy']);
});