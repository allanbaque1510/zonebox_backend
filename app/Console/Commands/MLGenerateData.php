<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

use function Laravel\Prompts\{info, error, warning, spin};

class MLGenerateData extends Command
{
    protected $signature = 'ml:generate-data {--records=800}';
    protected $description = 'Generar datos simulados para entrenamiento';

    public function handle(): int
    {
        info('Generando datos simulados...');
        
        $records = $this->option('records');
        $pythonPath = config('ml.python_path', 'py');
        $scriptPath = storage_path('app/ml/scripts/fake_data.py');
        
        if (!file_exists($scriptPath)) {
            error('Script no encontrado: ' . $scriptPath);
            return self::FAILURE;
        }
        
        $result = spin(
            fn() => $this->executeScript($pythonPath, $scriptPath),
            'Generando datos...'
        );
        
        if ($result['success']) {
            info('Datos generados exitosamente!');
            $this->components->info('Ubicación: storage/app/ml/data/pedidos_simulados.xlsx');
            return self::SUCCESS;
        }
        
        error('Error al generar datos: ' . $result['error']);
        return self::FAILURE;
    }
    
    private function executeScript(string $pythonPath, string $scriptPath): array
    {
        $process = new Process([$pythonPath, $scriptPath]);
        $process->setTimeout(120);
        
        try {
            $process->mustRun(function ($type, $buffer) {
                $this->output->write($buffer);
            });
            
            return ['success' => true];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}