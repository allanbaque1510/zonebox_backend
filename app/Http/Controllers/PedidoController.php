<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use App\Services\PedidoService;
use Exception;
use Illuminate\Http\Request;

class PedidoController extends Controller
{

    public function __construct(private readonly PedidoService $_pedidoService){}

    public function index()
    {
        try {   
            $data = $this->_pedidoService->index();
            return response()->json(["ok"=>true, 'data' => $data],200);
        } catch (Exception $e) {
            return responseErrorController($e,400);
        }
    }
    
    public function listaPedidos()
    {
        try {   
            $data = $this->_pedidoService->listaPedidos();
            return response()->json(["ok"=>true, 'data' => $data],200);
        } catch (Exception $e) {
            return responseErrorController($e,400);
        }
    }

    public function create(Request $request)
    {
        try {   
            $data = $this->_pedidoService->create($request);
            return response()->json(["ok"=>true, 'data' => $data],200);
        } catch (Exception $e) {
            return responseErrorController($e,400);
        }
    }

    public function show( Pedido $pedido)
    {
        try {   
            $data = $this->_pedidoService->show($pedido);
            return response()->json(["ok"=>true, 'data' => $data],200);
        } catch (Exception $e) {
            return responseErrorController($e,400);
        }
    }

    public function store(Request $request)
    {
        try {   
            $data = $this->_pedidoService->store($request);
            return response()->json(["ok"=>true, 'data' => $data],200);
        } catch (Exception $e) {
            return responseErrorController($e,400);
        }
    }

    public function update(Pedido $pedido,Request $request)
    {
        try {   
            $data = $this->_pedidoService->update( $pedido, $request);
            return response()->json(["ok"=>true, 'data' => $data],200);
        } catch (Exception $e) {
            return responseErrorController($e,400);
        }
    }

    public function destroy(Pedido $pedido)
    {
        try {   
            $data = $this->_pedidoService->destroy($pedido);
            return response()->json(["ok"=>true, 'data' => $data],200);
        } catch (Exception $e) {
            return responseErrorController($e,400);
        }
    }



}