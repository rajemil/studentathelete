<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Team extends Model
{
    protected $fillable = [
        'organization_id',
        'name',
        'sport_id',
        'primary_coach_id',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function sport(): BelongsTo
    {
        return $this->belongsTo(Sport::class);
    }

    public function primaryCoach(): BelongsTo
    {
        return $this->belongsTo(User::class, 'primary_coach_id');
    }

    /**
     * Ranked student members.
     */
    public function students(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'team_memberships')
            ->withPivot(['rank', 'joined_on', 'left_on'])
            ->withTimestamps()
            ->orderBy('team_memberships.rank');
    }

    public function coachAssignments(): HasMany
    {
        return $this->hasMany(CoachAssignment::class);
    }

    public function playerStats(): HasMany
    {
        return $this->hasMany(PlayerStat::class);
    }

    public function performanceScores(): HasMany
    {
        return $this->hasMany(PerformanceScore::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }
}
