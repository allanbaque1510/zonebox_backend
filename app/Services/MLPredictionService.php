<?php

namespace App\Services;

use Symfony\Component\Process\Process;
use Illuminate\Support\Facades\{Log, Cache};
use Illuminate\Contracts\Cache\Repository as CacheContract;

class MLPredictionService
{
    public function __construct(
        private readonly CacheContract $cache,
        private string $pythonPath = 'py',
        private int $timeout = 10
    ) {
        $this->pythonPath = config('ml.python_path', 'py');
        $this->timeout = config('ml.prediction_timeout', 10);
    }
    
    /**
     * Verifica si el modelo está disponible
     */
    public function modelDisponible(): bool
    {
        return file_exists(config('ml.model_path'));
    }
    
    /**
     * Realiza una predicción
     */
    public function predecir(object $pedido): array
    {
        if (!$this->modelDisponible()) {
            Log::warning('Modelo ML no disponible');
            return $this->fallbackPrediction();
        }
        
        try {
            $data = $this->prepararDatos($pedido);
            $cacheKey = $this->generarCacheKey($data);
            
            return $this->cache->remember(
                $cacheKey,
                now()->addHour(),
                fn() => $this->ejecutarPrediccion($data)
            );
            
        } catch (\Exception $e) {
            Log::error('Error en predicción ML', [
                'error' => $e->getMessage(),
                'pedido_id' => $pedido->id ?? null
            ]);
            
            return $this->fallbackPrediction();
        }
    }
    
    /**
     * Prepara los datos para la predicción
     */
    private function prepararDatos(object $pedido): array
    {
        return [
            'ciudad_origen_id' => $pedido->cliente->casillero->id_ciudad,
            'ciudad_destino_id' => $pedido->id_ciudad_destino,
            'peso_kg' => $pedido->peso ?? 1.0,
            'precio' => $pedido->precio ?? 0,
            'empresa_envio_id' => $pedido->id_empresa_envio,
            'fecha_envio' => now()->format('Y-m-d'),
        ];
    }
    
    /**
     * Genera clave de cache
     */
    private function generarCacheKey(array $data): string
    {
        return 'ml_pred_' . md5(json_encode($data));
    }
    
    /**
     * Ejecuta el script Python
     */
    private function ejecutarPrediccion(array $data): array
    {
        $scriptPath = storage_path('app/ml/scripts/predict.py');
        
        $process = Process::fromShellCommandline(
            sprintf('%s %s', $this->pythonPath, escapeshellarg($scriptPath))
        );
        
        $process->setInput(json_encode($data));
        $process->setTimeout($this->timeout);
        
        try {
            $process->mustRun();
            
            $resultado = json_decode($process->getOutput(), true);
            
            if (!$resultado || !($resultado['success'] ?? false)) {
                Log::warning('Predicción ML con error', [
                    'error' => $resultado['error'] ?? 'unknown'
                ]);
                return $this->fallbackPrediction();
            }
            
            return $resultado;
            
        } catch (\Exception $e) {
            Log::error('Error ejecutando predicción', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
    
    /**
     * Predicción por defecto (fallback)
     */
    private function fallbackPrediction(): array
    {
        $diasBase = config('ml.fallback.dias_estimados', 7.0);
        
        return [
            'success' => false,
            'dias_estimados' => $diasBase,
            'fecha_entrega_estimada' => now()->addDays($diasBase)->format('Y-m-d'),
            'rango_min' => round($diasBase * 0.85, 1),
            'rango_max' => round($diasBase * 1.15, 1),
            'confianza' => config('ml.fallback.confianza', 'Baja'),
            'mensaje' => 'Usando estimación por defecto',
            'using_fallback' => true,
        ];
    }
    
    /**
     * Obtiene métricas del modelo
     */
    public function obtenerMetricas(): ?array
    {
        $metricsPath = storage_path('app/ml/models/metrics.json');
        
        if (!file_exists($metricsPath)) {
            return null;
        }
        
        return json_decode(file_get_contents($metricsPath), true);
    }
    
    /**
     * Limpia el cache de predicciones
     */
    public function limpiarCache(): bool
    {
        return $this->cache->flush();
    }
}