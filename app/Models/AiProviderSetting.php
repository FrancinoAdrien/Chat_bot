<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiProviderSetting extends Model
{
    protected $fillable = [
        'provider',
        'api_key',
        'model',
        'is_active',
        'verified_at',
    ];

    protected $casts = [
        'is_active'   => 'boolean',
        'verified_at' => 'datetime',
    ];

    protected $hidden = ['api_key'];

    /**
     * Get the masked key for display (show only last 4 chars).
     */
    public function getMaskedKeyAttribute(): ?string
    {
        if (!$this->api_key) return null;
        return '••••••••••••••••' . substr($this->api_key, -4);
    }

    /**
     * Get the active cloud provider setting (non-ollama).
     */
    public static function activeCloud(): ?self
    {
        return self::where('is_active', true)
            ->where('provider', '!=', 'ollama')
            ->whereNotNull('api_key')
            ->whereNotNull('verified_at')
            ->first();
    }

    /**
     * Provider display labels.
     */
    public static function providerLabels(): array
    {
        return [
            'groq'   => 'Groq (Llama 3 — Gratuit & Rapide)',
            'openai' => 'OpenAI (GPT-4o)',
            'gemini' => 'Google Gemini',
        ];
    }

    /**
     * Default models per provider.
     */
    public static function defaultModel(string $provider): string
    {
        return match($provider) {
            'groq'   => 'llama-3.3-70b-versatile',
            'openai' => 'gpt-4o-mini',
            'gemini' => 'gemini-1.5-flash',
            default  => 'llama-3.3-70b-versatile',
        };
    }
}
