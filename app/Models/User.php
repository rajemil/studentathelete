<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'role',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class);
    }

    /**
     * Sports the (student) user participates in.
     */
    public function sports(): BelongsToMany
    {
        return $this->belongsToMany(Sport::class)->withTimestamps();
    }

    /**
     * Teams the (student) user is a member of (ranked via pivot).
     */
    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'team_memberships')
            ->withPivot(['rank', 'joined_on', 'left_on'])
            ->withTimestamps();
    }

    /**
     * Teams where the user is the primary coach.
     */
    public function primaryCoachedTeams(): HasMany
    {
        return $this->hasMany(Team::class, 'primary_coach_id');
    }

    /**
     * Coach assignments across teams.
     */
    public function coachAssignments(): HasMany
    {
        return $this->hasMany(CoachAssignment::class, 'coach_id');
    }

    public function playerStats(): HasMany
    {
        return $this->hasMany(PlayerStat::class);
    }

    public function performanceScores(): HasMany
    {
        return $this->hasMany(PerformanceScore::class);
    }

    public function trainingRecommendations(): HasMany
    {
        return $this->hasMany(TrainingRecommendation::class);
    }
}
