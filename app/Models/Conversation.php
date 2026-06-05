<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'api_connection_id',
        'user_message',
        'ai_response',
        'tool_used',
        'tool_data',
        'response_time_ms',
    ];

    protected $casts = [
        'tool_data' => 'array',
    ];

    public function apiConnection(): BelongsTo
    {
        return $this->belongsTo(ApiConnection::class);
    }
}
