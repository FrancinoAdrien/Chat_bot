<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChatSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'api_connection_id',
        'title',
        'last_message_at',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function apiConnection(): BelongsTo
    {
        return $this->belongsTo(ApiConnection::class);
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class)->orderBy('created_at');
    }

    /**
     * Generate a clean short title from user message (max 50 chars)
     */
    public static function titleFromMessage(string $message): string
    {
        $title = trim(preg_replace('/\s+/', ' ', $message));
        return mb_strlen($title) > 50 ? mb_substr($title, 0, 47) . '...' : $title;
    }
}
