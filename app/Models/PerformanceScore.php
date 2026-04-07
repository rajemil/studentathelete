<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class PerformanceScore extends Model
{
    protected $fillable = [
        'user_id',
        'sport_id',
        'team_id',
        'category',
        'score',
        'scored_on',
        'breakdown',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'decimal:2',
            'scored_on' => 'date',
            'breakdown' => 'array',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
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
