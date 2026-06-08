<?php

namespace App\Models;

use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AthleteStatistic extends Model
{
    use BelongsToOrganization;

    protected $fillable = [
        'organization_id',
        'user_id',
        'sport_id',
        'period_start',
        'period_end',
        'avg_performance_score',
        'performance_metrics',
        'sessions_attended',
        'sessions_missed',
        'attendance_rate',
        'fatigue_score',
        'injury_risk',
        'health_notes',
        'computed_at',
    ];

    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'period_end' => 'date',
            'avg_performance_score' => 'decimal:2',
            'performance_metrics' => 'array',
            'attendance_rate' => 'decimal:2',
            'fatigue_score' => 'decimal:2',
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
}
