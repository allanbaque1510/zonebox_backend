<?php

namespace App\Services;

use App\Models\Ciudad;
use App\Models\EmpresaEnvio;
use App\Models\EstadoPedido;
use App\Models\HistorialEstados;
use App\Models\Pais;
use App\Models\Pedido;
use App\Models\Tienda;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class PedidoService
{
    public function __construct( private readonly MLPredictionService $_mlPredictionService ) { }

    public function index () {
      try {
            $usuario            = Auth::user();
            $estadoInicial      = EstadoPedido::where('codigo', 'CREADO')->first();
            $countPendientes    = Pedido::where('id_cliente', $usuario->id)->where('id_estado', $estadoInicial->id)->count();
            $countEnTransito    = Pedido::where('id_cliente', $usuario->id)
                                    ->where('id_estado', '!=', $estadoInicial->id)
                                    ->whereNull('fecha_entrega')
                                    ->count();

            $countEntregados    = Pedido::where('id_cliente', $usuario->id)->whereNotNull('fecha_entrega')->count();

            $dataPedido = Pedido::where('id_cliente', $usuario->id)->whereNull('fecha_entrega')->get();
            
            $data = $dataPedido->transform(function($pedido) use ($usuario) {
                return [
                    'id'                => $pedido->id,
                    'color'             => $pedido->estado->color,
                    'codigo'            => $pedido->codigo,
                    'codigo_estado'     => $pedido->estado->codigo,
                    'estado'            => $pedido->estado->nombre,
                    'origen'            => $usuario->casillero->nombreCiudad,
                    'origenFlag'        => $usuario->casillero->ciudad->pais->flag,
                    'destino'           => $pedido->ciudadDestino->nombreCiudad,
                    'destinoFlag'       => $pedido->ciudadDestino->pais->flag,
                    'diasEstimados'     => $this->calcularDiasEstimados($pedido->fecha_entrega_estimada),
                ];
            });
          
            return [
                'pedidos' => $data, 
                'contadorPendientes' => $countPendientes, 
                'contadorEntregados' => $countEntregados, 
                'contadorEnTransito' => $countEnTransito, 
            ];
        } catch (ValidationException $e) {
            throw new ValidationException($e->getMessage());
        }  catch (Exception $e) {
            Log::error($e);
            throw new Exception($e->getMessage());
        }   
    }
    private function calcularDiasEstimados($fechaEntregaEstimada){
        $fechaActual = now();
        $fechaEntregaEstimada = Carbon::parse($fechaEntregaEstimada)->startOfDay();
        $diferencia = (int) $fechaActual->diffInDays($fechaEntregaEstimada, false);
        Log::info("Diferencia de días: " . $diferencia);
        $diferencia = $diferencia >= 0 ? $diferencia : 0;

        return match (true) {
            $diferencia === 0 => 'Hoy',
            $diferencia > 1   => $diferencia . ' días',
            $diferencia === 1   => $diferencia . ' día',
            default              => 'Vencido'
        };
    }
    public function listaPedidos () {
      try {
            $usuario    = Auth::user();
            $dataPedido = Pedido::where('id_cliente', $usuario->id)->get();
            $estados    = EstadoPedido::select('nombre')->orderBy('orden')->get()->pluck('nombre');
            $estados->prepend('Todos');

            $data       = $dataPedido->transform(function($pedido) use ($usuario) {
                return [
                    'id'            => $pedido->id,
                    'color'         => $pedido->estado->color,
                    'codigo'        => $pedido->codigo,
                    'estado'        => $pedido->estado->nombre,
                    'codigo_estado' => $pedido->estado->codigo,
                    'fecha'         => $pedido->created_at->format('Y-m-d'),
                    'origen'        => $usuario->casillero->nombreCiudad,
                    'destino'       => $pedido->ciudadDestino->nombreCiudad,
                    'precio'        => $pedido->precio,
                ];
            });
          
            return ['pedidos' => $data, 'estados' => $estados];
        } catch (ValidationException $e) {
            throw new ValidationException($e->getMessage());
        }  catch (Exception $e) {
            Log::error($e);
            throw new Exception($e->getMessage());
        }   
    }


    public function show ( Pedido $pedido) {
      try {
            $prediccion = $this->_mlPredictionService->predecir($pedido);
            Log::info(json_encode( $prediccion, JSON_PRETTY_PRINT ));
            $data = $pedido->load('empresaEnvio', 'tienda', 'ciudadDestino', 'historialEstados','estado');
            return $data; 

        } catch (ValidationException $e) {
            throw new ValidationException($e->getMessage());
        }  catch (Exception $e) {
            throw new Exception($e->getMessage());
        }   
    }

    public function create () {
        try {
            $pais = Pais::where('activo', true)->where('codigo', 'EC')->first();

            $data = [
                'ciudades'          => Ciudad::where('id_pais', $pais->id)->where('activo', true)->get(),
                'tiendas'           => Tienda::where('activo', true)->get(),
                'empresas_envio'    => EmpresaEnvio::where('activo', true)->get(),
            ];

            return $data;

        } catch (ValidationException $e) {
            throw new ValidationException($e->getMessage());
        }  catch (Exception $e) {
            throw new Exception($e->getMessage());
        } 
    }

    public function actualizarEstado ($idEstado, $pedido) {
        try {

            DB::transaction(function () use ($idEstado, $pedido) {
                if($idEstado == $pedido->id_estado) {
                    throwValidation("El pedido ya se encuentra en el estado seleccionado");
                }

                $estado = EstadoPedido::find($idEstado);
                if (!$estado)  throwValidation("Estado no encontrado");
                
                $historialAnterior = HistorialEstados::where('id_pedido', $pedido->id)
                ->where('id_estado', $pedido->id_estado)
                ->first();

                if ( ! $historialAnterior )  throwValidation("Estado anterior no encontrado");

                
                
                
                $historialAnterior->fecha = now();
                $historialAnterior->completado = true;
                $historialAnterior->save();
                
                $ultimoEstado       = $estado->codigo === 'ENTREGADO';
                $realizarPrediccion = $estado->codigo === 'EN_GESTION';

                if( $realizarPrediccion ){
                    $prediccion = $this->_mlPredictionService->predecir($pedido);
                    if(array_key_exists('fecha_entrega_estimada', $prediccion)){
                        $pedido->fecha_entrega_estimada = $prediccion['fecha_entrega_estimada'];
                    }
                }
                
                $pedido->id_estado = $estado->id;
                $pedido->fecha_entrega = $ultimoEstado ? now() : null;
                $pedido->save();
                

                $historial = [
                    'id_pedido'     => $pedido->id,
                    'id_estado'     => $estado->id,
                    'completado'    => $ultimoEstado,
                    'fecha'         => $ultimoEstado ? now() : null,
                ];

                HistorialEstados::create($historial);
                
            });
            return $pedido;
            

        } catch (ValidationException $e) {
            throwValidation($e->getMessage());
        }  catch (Exception $e) {
            throw new Exception($e->getMessage());
        } 
    }


    public function update () {
        try {
          

        } catch (ValidationException $e) {
            throw new ValidationException($e->getMessage());
        }  catch (Exception $e) {
            throw new Exception($e->getMessage());
        } 
    }

    public function store ($request) {
        try {
            $dataRequest    = $request->all();
            $codigo         = generateCodeOrder();
            $idUsuario      = Auth::user()->id;
            $estado         = EstadoPedido::where('codigo', 'CREADO')->first();

            $data = [
                'codigo'            => $codigo,
                'numero_tracking'   => $dataRequest['numero_tracking'],
                'descripcion'       => $dataRequest['descripcion'],
                'id_usuario'        => $idUsuario,
                'id_estado'         => $estado->id,
            ];

            return Pedido::create($data);

        } catch (ValidationException $e) {
            throwValidation($e->getMessage());
        }  catch (Exception $e) {
            throw new Exception($e->getMessage());
        } 
    }

     public function destroy () {
        try {
          

        } catch (ValidationException $e) {
            throw new ValidationException($e->getMessage());
        }  catch (Exception $e) {
            throw new Exception($e->getMessage());
        } 
    }


}