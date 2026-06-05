<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreToolRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'client_id'   => ['required', 'integer', 'exists:clients,id'],
            'name'        => ['required', 'string', 'alpha_dash', 'max:100'],
            'label'       => ['required', 'string', 'max:100'],
            'endpoint'    => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:500'],
            'keywords'    => ['required', 'string'],   // comma-separated, will be split
            'method'      => ['required', 'in:GET,POST'],
            'active'      => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.alpha_dash'    => 'Le nom de l\'outil ne peut contenir que des lettres, chiffres, tirets et underscores.',
            'endpoint.required'  => 'L\'endpoint API est obligatoire.',
            'keywords.required'  => 'Veuillez fournir au moins un mot-clé.',
        ];
    }
}
