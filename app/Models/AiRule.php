<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class AiRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'instruction',
        'target_type',
        'target_value',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Scope a query to only include active rules.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Retrieve all rules that apply to a specific User.
     */
    public static function getRulesForUser(User $user): Collection
    {
        return self::active()
            ->where(function ($query) use ($user) {
                // Rule applies to everyone
                $query->where('target_type', 'all')
                // OR Rule applies to this specific user ID
                      ->orWhere(function ($q) use ($user) {
                          $q->where('target_type', 'user')
                            ->where('target_value', (string) $user->id);
                      })
                // OR Rule applies to this user's poste (job title)
                      ->orWhere(function ($q) use ($user) {
                          $q->where('target_type', 'poste')
                            ->where('target_value', $user->poste);
                      });
            })
            ->get();
    }
}
