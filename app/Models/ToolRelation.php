<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ToolRelation extends Model
{
    use HasFactory;

    protected $fillable = [
        'api_connection_id',
        'primary_tool_id',
        'primary_field',
        'foreign_tool_id',
        'foreign_field',
    ];

    public function apiConnection(): BelongsTo
    {
        return $this->belongsTo(ApiConnection::class);
    }

    public function primaryTool(): BelongsTo
    {
        return $this->belongsTo(Tool::class, 'primary_tool_id');
    }

    public function foreignTool(): BelongsTo
    {
        return $this->belongsTo(Tool::class, 'foreign_tool_id');
    }
}
