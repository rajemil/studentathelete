<?php

namespace App\Models;

use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompetitionRecord extends Model
{
    use BelongsToOrganization;

    protected $fillable = [
        'organization_id',
        'event_id',
        'sport_id',
        'team_id',
        'user_id',
        'competition_name',
        'competed_on',
        'placement',
        'is_mvp',
        'stats',
        'result_notes',
    ];

    protected function casts(): array
    {
        return [
            'competed_on' => 'date',
            'is_mvp' => 'boolean',
            'stats' => 'array',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function sport(): BelongsTo
    {
        return $this->belongsTo(Sport::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
