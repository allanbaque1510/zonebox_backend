<?php

namespace App\Http\Requests;


class LoginRequest extends ApiFormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'email'    => ['required', 'email'],
            'password' => ['required', 'min:6'],
        ];
    }

    public function messages()
    {
        return [
            'email.required'    => 'El correo electrónico es obligatorio.',
            'email.email'       => 'Ingrese un correo electrónico válido.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min'      => 'La contraseña debe tener al menos 6 caracteres.',
        ];
    }
}
