<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\StoreUserRequest;
use App\Models\User;
use App\Services\AuthService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    protected $_authService;

    public function __construct()
    {
        $this->_authService = new AuthService();
    }

    public function register(StoreUserRequest $request){
        try {
            $data = $request->validated();
            $contraseña = $data['password'];
        
            $user = $this->_authService->crearUsuario($data);

            // Iniciar sesión automáticamente después del registro
            $credentials = [
                "email"     => $user["email"],
                "password"  => $contraseña
            ];
            
            // Intentar autenticar al usuario y guardar en la sesión
            if (Auth::attempt($credentials)) {
                $user_log = Auth::user();
                if ($user_log instanceof User) {

                    $token = $user_log->createToken('ZONEBOX-APP')->plainTextToken;

                    return response()->json([
                        "ok" => true,
                        'data' => [
                                'token' => $token,
                                'user'  => $user_log
                            ]
                    ]);

                } else {
                    throwValidation("Usuario no encontrado");
                }
            }
            
            throwValidation("Credenciales incorrectas");
      
        } catch (ValidationException $e) {
            return responseErrorValidation($e);

        }  catch (Exception $e) {
            return responseErrorController($e,401);
        }
    }

    public function login(LoginRequest $request){
        try {
            $credentials = $request->validated();
            
            $user = User::where('email', $credentials['email'])->first();
    
            if (!$user) throwValidation("El usuario no existe");
    
            if (Auth::attempt($credentials)) {
                $user_log = Auth::user();
                if ($user_log instanceof User) {
                    $token = $user_log->createToken('ZONEBOX-APP')->plainTextToken;
                    return response()->json([
                        "ok" => true,
                        'data' => [
                            'token' => $token,
                            'user'  => $user_log,
                        ]
                    ]);
                } else {
                    throwValidation("Usuario no encontrado");
                }
            }
            throwValidation("Credenciales incorrectas");
            
        } catch (ValidationException $e) {
            return responseErrorValidation($e);

        } catch (Exception $e) {
            return responseErrorController($e,401);
        }
    }

    
    public function logOut(Request $request)
    {
        try {   
            $request->user()->tokens()->delete();
                        

            return response()->json(["ok"=>true, 'data' => ['message' => 'Logout exitoso']],200);
        } catch (Exception $e) {
            return responseErrorController($e,400);
        }
    }

    public function getUser(Request $request){
        try {
            $user = Auth::user();
            Log::info(json_encode($user, JSON_PRETTY_PRINT));
            if(!$user) throwValidation("No existe un usuario autenticado");
            
            return response()->json([
                "ok" => true,
                'data' => $user
            ]);
            
  
        } catch (ValidationException $e) {
            Log::error($e);
            return responseErrorValidation($e);

        } catch (Exception $e) {
            Log::error($e);
            return responseErrorController($e,401);
        }
    }
    
    public function verificarToken(){
        try {
            $user = Auth::user();
            if(!$user)throw new  Exception("No existe un usuario autenticado");
            return response()->json(["ok"=>true],200);
        } catch (Exception $e) {
            return responseErrorController($e,401);
        }
    }
}