<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiConnection extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'base_url',
        'login_url',
        'auth_token',
        'is_authenticated',
        'authenticated_at',
        'description',
        'active',
    ];

    protected $casts = [
        'is_authenticated'  => 'boolean',
        'active'            => 'boolean',
        'authenticated_at'  => 'datetime',
    ];

    /**
     * Always hide the raw auth token from JSON serialization.
     */
    protected $hidden = ['auth_token'];

    /**
     * Build the full URL for a given endpoint path.
     */
    public function buildUrl(string $endpoint): string
    {
        return rtrim($this->base_url, '/') . '/' . ltrim($endpoint, '/');
    }

    /**
     * Whether this connection has a login endpoint configured.
     */
    public function hasLoginUrl(): bool
    {
        return ! empty($this->login_url);
    }

    /**
     * Mask the token for display purposes.
     */
    public function getMaskedTokenAttribute(): ?string
    {
        if (! $this->auth_token) {
            return null;
        }
        return '••••••••' . substr($this->auth_token, -6);
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeAuthenticated($query)
    {
        return $query->where('is_authenticated', true)->whereNotNull('auth_token');
    }
}
