<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ParticipationLog extends Model
{
    protected $fillable = [
        'organization_id',
        'user_id',
        'sport_id',
        'activity_type',
        'duration_minutes',
        'notes',
        'logged_on',
    ];

    protected function casts(): array
    {
        return [
            'logged_on' => 'date',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
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
