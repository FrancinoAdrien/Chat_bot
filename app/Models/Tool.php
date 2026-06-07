<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tool extends Model
{
    use HasFactory;

    protected $fillable = [
        'api_connection_id',
        'type',
        'file_path',
        'sheet_name',
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

    /**
     * Relations where this tool is the PRIMARY key side.
     */
    public function primaryRelations(): HasMany
    {
        return $this->hasMany(ToolRelation::class, 'primary_tool_id');
    }

    /**
     * Relations where this tool is the FOREIGN key side.
     */
    public function foreignRelations(): HasMany
    {
        return $this->hasMany(ToolRelation::class, 'foreign_tool_id');
    }

    /**
     * Get all relations involving this tool (either side).
     */
    public function allRelations()
    {
        return ToolRelation::where('primary_tool_id', $this->id)
            ->orWhere('foreign_tool_id', $this->id)
            ->get();
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

