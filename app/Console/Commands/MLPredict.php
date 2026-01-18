<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\MLPredictionService;

use function Laravel\Prompts\{info, error, table};

class MLPredict extends Command
{
    protected $signature = 'ml:predict 
                            {--origen=1 : ID ciudad origen}
                            {--destino=10 : ID ciudad destino}
                            {--peso=5 : Peso en kg}
                            {--precio=50 : Precio}
                            {--empresa=1 : ID empresa envío}';
    
    protected $description = 'Probar predicción ML';

    public function handle(MLPredictionService $mlService): int
    {
        info('Realizando predicción...');
        $this->newLine();
        
        $pedido = (object) [
            'ciudad_origen_id' => $this->option('origen'),
            'ciudad_destino_id' => $this->option('destino'),
            'peso' => $this->option('peso'),
            'precio' => $this->option('precio'),
            'empresa_envio_id' => $this->option('empresa'),
        ];
        
        $resultado = $mlService->predecir($pedido);
        
        if ($resultado['success']) {
            table(
                ['Campo', 'Valor'],
                [
                    ['Días Estimados', $resultado['dias_estimados']],
                    ['Fecha Entrega', $resultado['fecha_entrega_estimada']],
                    ['Rango Mínimo', $resultado['rango_min'] . ' días'],
                    ['Rango Máximo', $resultado['rango_max'] . ' días'],
                    ['Confianza', $resultado['confianza']],
                    ['Mensaje', $resultado['mensaje'] ?? 'N/A'],
                ]
            );
            
            $this->newLine();
            info('Predicción exitosa');
            return self::SUCCESS;
        }
        
        error('Error: ' . $resultado['error']);
        return self::FAILURE;
    }
}