<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class Insight extends Model
{
    protected $fillable = [
        'hash_key',
        'user_id',
        'sport_id',
        'team_id',
        'type',
        'severity',
        'title',
        'message',
        'payload',
        'computed_at',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'computed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sport(): BelongsTo
    {
        return $this->belongsTo(Sport::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}
