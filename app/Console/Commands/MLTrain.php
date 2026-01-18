<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

use function Laravel\Prompts\{info, error, warning, table, note};

class MLTrain extends Command
{
    protected $signature = 'ml:train';
    protected $description = 'Entrenar el modelo de predicción ML';

    public function handle(): int
    {
        info('Iniciando entrenamiento del modelo...');
        
        $dataPath = storage_path('app/ml/data/pedidos_simulados.xlsx');
        
        if (!file_exists($dataPath)) {
            error('No hay datos de entrenamiento.');
            note('Ejecuta primero: php artisan ml:generate-data');
            return self::FAILURE;
        }
        
        $pythonPath = config('ml.python_path', 'py');
        $scriptPath = storage_path('app/ml/scripts/train_model.py');
        
        if (!file_exists($scriptPath)) {
            error('Script de entrenamiento no encontrado');
            return self::FAILURE;
        }
        
        warning('Esto puede tomar varios minutos...');
        $this->newLine();
        
        $process = new Process([$pythonPath, $scriptPath]);
        $process->setTimeout(600);
        
        try {
            $process->mustRun(function ($type, $buffer) {
                $this->output->write($buffer);
            });
            
            $this->newLine();
            info('Modelo entrenado exitosamente!');
            
            // Mostrar métricas
            $this->displayMetrics();
            
            return self::SUCCESS;
            
        } catch (\Exception $e) {
            error('Error al entrenar: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
    
    private function displayMetrics(): void
    {
        $metricsPath = storage_path('app/ml/models/metrics.json');
        
        if (!file_exists($metricsPath)) {
            return;
        }
        
        $metrics = json_decode(file_get_contents($metricsPath), true);
        
        $this->newLine();
        
        table(
            ['Métrica', 'Valor'],
            [
                ['Modelo', $metrics['model_type'] ?? 'N/A'],
                ['Error Promedio (MAE)', round($metrics['mae'], 2) . ' días'],
                ['Error Cuadrático (RMSE)', round($metrics['rmse'], 2) . ' días'],
                ['Precisión (R²)', round($metrics['r2'], 4)],
                ['CV MAE', round($metrics['cv_mae'], 2) . ' días'],
                ['Entrenado', $metrics['trained_at'] ?? 'N/A'],
            ]
        );
        
        $this->newLine();
        note('Modelo guardado en: storage/app/ml/models/delivery_model.pkl');
    }
}