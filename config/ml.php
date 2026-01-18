<?php
// config/ml.php

return [
    /*
    |--------------------------------------------------------------------------
    | Python Path
    |--------------------------------------------------------------------------
    |
    | Ruta al ejecutable de Python. Puede ser un entorno virtual.
    |
    */
    'python_path' => storage_path(env('ML_PYTHON_PATH', 'python3')),
    
    /*
    |--------------------------------------------------------------------------
    | Model Path
    |--------------------------------------------------------------------------
    |
    | Ubicación del modelo entrenado en storage
    |
    */
    'model_path' => storage_path('app/ml/models/delivery_model.pkl'),
    
    /*
    |--------------------------------------------------------------------------
    | Scripts Path
    |--------------------------------------------------------------------------
    |
    | Ubicación de los scripts Python
    |
    */
    'scripts_path' => storage_path('app/ml/scripts'),
    
    /*
    |--------------------------------------------------------------------------
    | Timeout
    |--------------------------------------------------------------------------
    |
    | Tiempo máximo de espera para predicciones (segundos)
    |
    */
    'prediction_timeout' => 10,
    
    /*
    |--------------------------------------------------------------------------
    | Fallback
    |--------------------------------------------------------------------------
    |
    | Valores por defecto si el modelo no está disponible
    |
    */
    'fallback' => [
        'dias_estimados' => 7.0,
        'confianza' => 'Baja',
    ],
];