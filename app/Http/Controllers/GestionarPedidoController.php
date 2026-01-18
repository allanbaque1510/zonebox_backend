<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use App\Services\GestionarPedidoService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class GestionarPedidoController extends Controller
{
    public function __construct(private readonly GestionarPedidoService $_gestionarPedidoService){}

    public function index()
    {
        try {   
            $data = $this->_gestionarPedidoService->index();
            return response()->json(["ok"=>true, 'data' => $data],200);
        } catch (Exception $e) {
            return responseErrorController($e,400);
        }
    }

    public function asignar(Request $request)
    {
        try {   
            $data = $this->_gestionarPedidoService->asignar($request);
            return response()->json(["ok"=>true, 'data' => $data],200);
        
        } catch (ValidationException $e) {
            return responseErrorValidation($e);

        } catch (Exception $e) {
            return responseErrorController($e,401);
        }
    }
    
   public function historial(Request $request)
    {
        try {   
            $data = $this->_gestionarPedidoService->historial($request);
            return response()->json(["ok"=>true, 'data' => $data],200);
        
        } catch (ValidationException $e) {
            return responseErrorValidation($e);

        } catch (Exception $e) {
            return responseErrorController($e,401);
        }
    }
    

    public function show( Pedido $gestionarPedido)
    {
        try {   
            Log::info(json_encode($gestionarPedido, JSON_PRETTY_PRINT));
            $data = $this->_gestionarPedidoService->show($gestionarPedido);
            return response()->json(["ok"=>true, 'data' => $data],200);
        } catch (Exception $e) {
            return responseErrorController($e,400);
        }
    }

    public function store(Request $request)
    {
        try {   
            $data = $this->_gestionarPedidoService->store($request);
            return response()->json(["ok"=>true, 'data' => $data],200);
        } catch (Exception $e) {
            return responseErrorController($e,400);
        }
    }

    public function update(Pedido $gestionarPedido,Request $request)
    {
        try {   
            $data = $this->_gestionarPedidoService->update(  $request , $gestionarPedido);
            return response()->json(["ok"=>true, 'data' => $data],200);

        } catch (ValidationException $e) {
            $dataerror = responseErrorValidation($e);
            Log::info(json_encode($dataerror, JSON_PRETTY_PRINT));
            return $dataerror;

        } catch (Exception $e) {
            return responseErrorController($e,400);
        }
    }

    public function destroy(Pedido $gestionarPedido)
    {
        try {   
            $data = $this->_gestionarPedidoService->destroy($gestionarPedido);
            return response()->json(["ok"=>true, 'data' => $data],200);
        } catch (Exception $e) {
            return responseErrorController($e,400);
        }
    }



}