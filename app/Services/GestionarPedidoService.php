<?php

namespace App\Services;

use App\Models\Ciudad;
use App\Models\EmpresaEnvio;
use App\Models\EstadoPedido;
use App\Models\Pais;
use App\Models\Pedido;
use App\Models\Tienda;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class GestionarPedidoService
{

    public function __construct (
        private readonly PedidoService $_pedidoService

    ) {
    }

    public function index () {
      try {
            $usuario  = Auth::user();
            
            $estadoFinal = EstadoPedido::where('codigo', 'ENTREGADO')->first(); 

            $sqlPedidosPedientes = Pedido::whereNull('id_usurio_gestion');
            $sqlPedidosEnGestion = Pedido::where('id_usurio_gestion' , $usuario->id)->where('id_estado', '!=', $estadoFinal->id);
            $sqlPedidosCompletados = Pedido::where('id_usurio_gestion' , $usuario->id)->where('id_estado', $estadoFinal->id);
            
            $data =  [
                'countPedidosPendientes'    => $sqlPedidosPedientes->count(),
                'countPedidosEnGestion'     => $sqlPedidosEnGestion->count(),
                'countPedidosCompletados'   => $sqlPedidosCompletados->count(),

                'pedidosPendientes'         => $this->pedidoTransform($sqlPedidosPedientes->get()),
                'pedidosEnGestion'          => $this->pedidoTransform($sqlPedidosEnGestion->get()),
            ];
            return $data;
        } catch (ValidationException $e) {
            throwValidation($e->getMessage());
        }  catch (Exception $e) {
            throw new Exception($e->getMessage());
        }   
    }
    

    public function historial () {
      try {
            $usuario  = Auth::user();
            
            $data = Pedido::where('id_usurio_gestion' , $usuario->id);//->whereNotNull('fecha_entrega');
            
            return [
                'data'  => $data->get(),
            ];
        } catch (ValidationException $e) {
            throwValidation($e->getMessage());
        }  catch (Exception $e) {
            throw new Exception($e->getMessage());
        }   
    }
    

    public function asignar ($request) {
      try {
            $usuario  = Auth::user();
            $idPedido = $request->input('pedido');
            
            $pedido = Pedido::find($idPedido);
            $idEstado = EstadoPedido::where('codigo', 'EN_GESTION')->first()->id;
            if( ! empty($pedido->id_usurio_gestion)) throwValidation("El pedido ya ha sido asignado a otro usuario");
            $pedidoActualizado = $this->_pedidoService->actualizarEstado($idEstado, $pedido);

            $pedidoActualizado->id_usurio_gestion = $usuario->id;
            $pedidoActualizado->save();

            return $this->show($pedidoActualizado);

        } catch (ValidationException $e) {
            throwValidation($e->getMessage());
        }  catch (Exception $e) {
            throw new Exception($e->getMessage());
        }   
    }
    private function pedidoTransform($dataPedido ) {

        return $dataPedido->transform(function($pedido) {
                return [
                    'id'                => $pedido->id,
                    'codigo'            => $pedido->codigo,
                    'cliente'           => $pedido->cliente->primer_nombre . ' ' . $pedido->cliente->primer_apellido,
                    'fecha'             => $pedido->created_at->format('d/m/Y H:i'),

                    'estado'            => $pedido->estado->nombre,
                    'codigo_estado'     => $pedido->estado->codigo,
                    'color_estado'      => $pedido->estado->color,
                    'origen'            => $pedido->cliente->casillero->nombreCiudad,
                    'origenFlag'        => $pedido->cliente->casillero->ciudad->pais->flag,
                    'destino'           => $pedido->ciudadDestino->nombreCiudad,
                    'destinoFlag'       => $pedido->ciudadDestino->pais->flag,
                ];
            });
    }

    public function show ( Pedido $pedido) {
      try {
            $data = $pedido->load('empresaEnvio', 'tienda', 'ciudadDestino', 'historialEstados','estado');
            $data->nombreCliente = $pedido->cliente->primer_nombre . ' ' . $pedido->cliente->primer_apellido;
            $data->estados_disponibles = EstadoPedido::orderBy('orden')->get();
            return $data; 

        } catch (ValidationException $e) {
            throwValidation($e->getMessage());
        }  catch (Exception $e) {
            throw new Exception($e->getMessage());
        }   
    }


    public function update (Request $request, Pedido $pedido) {
        try {
            $idEstado   = $request->input('estado_id');
            $peso       = $request->input('peso');
            $precio     = $request->input('precio');
            
            $pedidoActualizado = $this->_pedidoService->actualizarEstado($idEstado, $pedido);
            $pedidoActualizado->peso = $peso;
            $pedidoActualizado->precio = $precio;
            $pedidoActualizado->save();

            return $pedidoActualizado;
        } catch (ValidationException $e) {
            throwValidation($e->getMessage());
        }  catch (Exception $e) {
            throw new Exception($e->getMessage());
        } 
    }

    public function store ($request) {
        try {
           return null;
            
        } catch (ValidationException $e) {
            throwValidation($e->getMessage());
        }  catch (Exception $e) {
            throw new Exception($e->getMessage());
        } 
    } 

      public function destroy ($request) {
        try {
           return null;
            
        } catch (ValidationException $e) {
            throwValidation($e->getMessage());
        }  catch (Exception $e) {
            throw new Exception($e->getMessage());
        } 
    } 

}