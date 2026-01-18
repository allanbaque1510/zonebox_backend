<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use function Laravel\Prompts\{info, warning, table, note};

class MLInfo extends Command
{
    protected $signature = 'ml:info';
    protected $description = 'Mostrar información del modelo ML';

    public function handle(): int
    {
        info('Información del Modelo ML');
        $this->newLine();
        
        $modelPath = storage_path('app/ml/models/delivery_model.pkl');
        $dataPath = storage_path('app/ml/data/pedidos_simulados.xlsx');
        $metricsPath = storage_path('app/ml/models/metrics.json');
        
        $modelExists = file_exists($modelPath);
        $dataExists = file_exists($dataPath);
        
        table(
            ['Componente', 'Estado', 'Ubicación'],
            [
                [
                    'Modelo Entrenado',
                    $modelExists ? '✅ Disponible' : '❌ No existe',
                    $modelPath
                ],
                [
                    'Datos de Entrenamiento',
                    $dataExists ? '✅ Disponible' : '❌ No existe',
                    $dataPath
                ],
            ]
        );
        
        if ($modelExists && file_exists($metricsPath)) {
            $metrics = json_decode(file_get_contents($metricsPath), true);
            
            $this->newLine();
            info('Métricas del Modelo:');
            
            table(
                ['Métrica', 'Valor'],
                [
                    ['Tipo', $metrics['model_type'] ?? 'N/A'],
                    ['Error Promedio (MAE)', round($metrics['mae'] ?? 0, 2) . ' días'],
                    ['RMSE', round($metrics['rmse'] ?? 0, 2) . ' días'],
                    ['R² Score', round($metrics['r2'] ?? 0, 4)],
                    ['Entrenado', $metrics['trained_at'] ?? 'N/A'],
                ]
            );
        }
        
        if (!$modelExists) {
            $this->newLine();
            warning('Para entrenar el modelo:');
            note('1. php artisan ml:generate-data');
            note('2. php artisan ml:train');
        }
        
        return self::SUCCESS;
    }
}