<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($this->user()->id)],
        ];
    }

    public function messages(): array
    {
        return [
            'required' => 'El campo :attribute es obligatorio.',
            'string' => 'El campo :attribute debe ser un texto.',
            'max' => 'El campo :attribute no puede superar los :max caracteres.',
            'email.email' => 'Introduce un correo electrónico válido.',
            'email.lowercase' => 'El correo electrónico debe estar en minúsculas.',
            'email.unique' => 'Este correo electrónico ya está registrado.',
        ];
    }

    public function attributes(): array
    {
        return ['name' => 'nombre completo', 'email' => 'correo electrónico'];
    }
}
