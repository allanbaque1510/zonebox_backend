<?php

use App\Models\Pedido;
use App\Models\TipoGrupo;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;

function throwValidation(string $message, string $key = 'error') {
    throw ValidationException::withMessages([
        $key => $message,
    ]);
}
function responseErrorValidation(ValidationException $error) {
    $errors = $error->errors();  
    $firstMessage = collect($errors)->flatten()->first(); 

    $response = new \stdClass();
    $response->ok = false;
    $response->message = $firstMessage;
    return response()->json($response, 422);
}


function responseErrorController(Exception $error, $codigo = 400) {
    Log::error($error);
    $response = new \stdClass();
    $response->ok = false;
    $response->message = $error->getMessage();
    
    return response()->json($response, $codigo);
}


function generateCodeOrder() {
    $year   = date('Y');
    $numero = Pedido::where('anio', $year)->count();
    $pedido = $numero+1;
    
    return 'ZB-'. $year . str_pad($pedido, 6, '0', STR_PAD_LEFT);
}



// function saveThumblr($tipo,$nombre,$imageInput){
//     try {
//         $extension = $imageInput->getClientOriginalExtension();
//         $imageData = $imageInput;

//         // Inicializar ImageManager con GdDriver
//         $manager = new ImageManager(new GdDriver());
//         $image = $manager->read($imageData);

//         // Redimensionar la imagen manteniendo proporción
//         $image = $image->scale(width: 100);

//         switch ($extension) {
//             case 'jpeg':
//             case 'jpg':
//                 $encoded = $image->toJpeg(70); // Reducimos calidad a 70%
//                 break;
//             case 'png':
//                 $encoded = $image->toPng();
//                 break;
//             case 'webp':
//                 $encoded = $image->toWebp(77); // WebP con calidad 75%
//                 break;
//             case 'gif':
//                 $encoded = $image->toGif();
//                 break;
//             case 'bmp':
//                 $encoded = $image->toBitmap();
//                 break;
//             case 'tiff':
//                 $encoded = $image->toTiff();
//                 break;
//             default:
//                 $encoded =$imageData;
//         }
        
//         $fileName =  $nombre.'_'. now()->format('Ymd_His') . '.'.$extension;
//         $urlPath = 'thumblr/'.$tipo.'/'. $fileName;
//         $path = Storage::disk('public')->put($urlPath, $encoded);
//         return $urlPath;
//     } catch (Exception $e) {
//         return responseErrorService($e);
//     }
// }
