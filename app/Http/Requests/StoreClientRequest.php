<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:100'],
            'api_url'     => ['required', 'url', 'max:255'],
            'api_key'     => ['required', 'string', 'min:8', 'max:512'],
            'description' => ['nullable', 'string', 'max:500'],
            'active'      => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'    => 'Le nom du client est obligatoire.',
            'api_url.required' => "L'URL de l'API est obligatoire.",
            'api_url.url'      => "L'URL de l'API doit être une URL valide (ex: https://app.example.com).",
            'api_key.required' => "La clé API est obligatoire.",
            'api_key.min'      => "La clé API doit contenir au moins 8 caractères.",
        ];
    }
}
