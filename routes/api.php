<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\GestionarPedidoController;
use App\Http\Controllers\GruposController;
use App\Http\Controllers\PedidoController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class,'register']);

Route::post('/login', [AuthController::class,'login'])->name('login');

Route::any('/no-autorizado', function (Request $request) {
    return response()->json([
        'message' => 'No estás autenticado'
    ], 401);
})->name('no-autorizado');



Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user',[AuthController::class,'getUser']);
    Route::get('/verificarToken',[AuthController::class,'verificarToken']);
    Route::get('/logout', [AuthController::class,'logOut']);
    
    // Rutas de Pedidos
    Route::get('pedidos/lista', [PedidoController::class, 'listaPedidos']);
    Route::get('pedidos/create', [PedidoController::class, 'create']);
    Route::apiResource('pedidos', PedidoController::class);
    
    // Rutas de Gestión de Pedidos
    Route::get('gestionarPedido/historial', [GestionarPedidoController::class, 'historial']);
    Route::post('gestionarPedido/asignar', [GestionarPedidoController::class, 'asignar']);
    Route::apiResource('gestionarPedido', GestionarPedidoController::class);
    
    // Ruta de Dashboard
    Route::get('dashboard/{idUsuario}/obtenerInformacion', [AuthController::class, 'getUser']);
});