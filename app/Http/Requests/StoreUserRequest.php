<?php

namespace App\Http\Requests;


class StoreUserRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre'           => ['required', 'string'],
            'apellido'         => ['required', 'string'],
            'telefono'         => ['required', 'string', 'unique:users,telefono'],
            'cedula'           => ['required', 'string', 'unique:users,cedula'],
            'email'            => ['required', 'email', 'unique:users,email'],
            'password'         => ['required', 'min:6'],
            'confirmPassword'  => ['required', 'same:password'],
            'tipoUsuario'      => ['required', 'string', 'exists:tipo_usuario,codigo'],
        ];
    }

    public function messages(): array
    {
        return [
            'required'                    => 'El campo :attribute es obligatorio.',
            'string'                      => 'El campo :attribute debe ser una cadena de texto.',

            'telefono.unique'             => 'El número de teléfono ya está registrado.',
            'cedula.unique'               => 'El número de cédula ya está registrado.',

            'email.unique'                => 'El correo electrónico ya está en uso.',
            'email.email'                 => 'Ingrese un correo válido.',

            'password.min'                => 'La contraseña debe tener al menos :min caracteres.',

            'confirmPassword.same'        => 'Las contraseñas no coinciden.',

            'tipoUsuario.exists'          => 'El tipo de usuario seleccionado no existe.',

            'min'                         => 'El campo :attribute debe tener al menos :min caracteres.',
            'max'                         => 'El campo :attribute no debe superar los :max caracteres.',
            'unique'                      => 'El valor del campo :attribute ya está registrado.',
            'exists'                      => 'El valor seleccionado para :attribute no es válido.',
        ];
    }

    public function attributes(): array
    {
        return [
            'nombre'           => 'nombre',
            'apellido'         => 'apellido',
            'telefono'         => 'teléfono',
            'cedula'           => 'cédula',
            'email'            => 'correo electrónico',
            'password'         => 'contraseña',
            'confirmPassword'  => 'confirmación de contraseña',
            'tipoUsuario'      => 'tipo de usuario',
        ];
    }
}
