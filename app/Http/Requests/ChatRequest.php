<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChatRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'connection_id' => ['required', 'exists:api_connections,id'],
            'message'       => ['required', 'string', 'max:5000'],
            'session_id'    => ['nullable', 'integer'],
        ];
    }
}
