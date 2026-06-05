<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Tool extends Model
{
    use HasFactory;

    protected $fillable = [
        'api_connection_id',
        'name',
        'label',
        'endpoint',
        'description',
        'keywords',
        'method',
        'active',
    ];

    protected $casts = [
        'keywords' => 'array',
        'active'   => 'boolean',
    ];

    public function apiConnection(): BelongsTo
    {
        return $this->belongsTo(ApiConnection::class);
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Check if a message matches this tool's keywords.
     */
    public function matchesMessage(string $message): bool
    {
        $message = mb_strtolower($message);

        foreach ($this->keywords as $keyword) {
            if (str_contains($message, mb_strtolower($keyword))) {
                return true;
            }
        }

        return false;
    }
}
