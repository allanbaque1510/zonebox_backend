<?php

namespace App\Services;

use App\Models\Casillero;
use App\Models\Ciudad;
use App\Models\TipoUsuario;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AuthService
{

    public function crearUsuario( $data )
    {
        try {
            $user  = null;
            DB::transaction(function () use ($data, &$user) {
                $tipo = TipoUsuario::where('codigo', $data['tipoUsuario'])->first();
                // Crear usuario con contraseña hasheada
                $data['nombre']             = strtoupper($data['nombre']);
                $data['apellido']           = strtoupper($data['apellido']);
                $data['password']           = Hash::make($data['password']);
                $data['id_tipo_usuario']    = $tipo->id;
                
                unset($data['confirmPassword']);
                unset($data['tipoUsuario']);
                
                $user = User::create($data);
                
                $this->crearCasillero($user);
            });

            return $user;
        } catch (ValidationException $e) {
            throw new ValidationException($e->getMessage());
        }  catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
    private function crearCasillero($user){
        try {
            
            if( $user->tipoUsuario->codigo !== 'CLIENT' ) return null;

            $dataDireccion = $this->obtenerDireccionAleatoria();
            $codigo = $this->generarCodigoCasillero();
            $telefono = $this->generarTelefonoMiami();
            $ciudad = Ciudad::where('codigo', $dataDireccion['ciudad'])->first();

            $data = [
                'id_usuario'    => $user->id,
                'id_ciudad'     => $ciudad->id,
                'codigo'        => $codigo,
                'direccion'     => $dataDireccion['direccion'],
                'estado'        => $dataDireccion['estado'],
                'zip'           => $dataDireccion['zip'],
                'telefono'      => $telefono,
            ];

            return Casillero::create($data);
            
        } catch (ValidationException $e) {
            throw new ValidationException($e->getMessage());
        }  catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
    private function obtenerDireccionAleatoria() {
        $streets = [
            'NW 98th Street', 'NW 107th Ave', 'SW 8th Street',
            'Biscayne Blvd', 'NW 36th Street', 'NW 25th Street',
            'NW 7th Ave', 'Coral Way', 'Flagler Street'
        ];

        $zips = ['33101','33125','33126','33127','33128','33129','33130','33131','33132','33133'];

        $streetNumber = rand(100, 9999);

        return [
            'direccion' => "{$streetNumber} " . $streets[array_rand($streets)],
            'ciudad'    => 'MIA',
            'estado'    => 'FL',
            'zip'       => $zips[array_rand($zips)],
        ];
    }
    private function generarCodigoCasillero() {
        $number = rand(0, 999999);
        $formatted = str_pad($number, 6, '0', STR_PAD_LEFT);

        return "ZON{$formatted}";
    }

    private function generarTelefonoMiami() {
        $prefix = '786';
        $number = rand(1000000, 9999999);

        return "+1 ({$prefix}) {$number}";
    }
}
