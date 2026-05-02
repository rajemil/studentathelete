<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sport extends Model
{
    protected $fillable = [
        'organization_id',
        'instructor_user_id',
        'name',
        'slug',
        'description',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_user_id');
    }

    public function students(): BelongsToMany
    {
        // Only student participants (faculty sport assignments use separate admin UI logic)
        return $this->belongsToMany(User::class)->withTimestamps()
            ->where('users.role', 'student');
    }

    public function teams(): HasMany
    {
        return $this->hasMany(Team::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }
}
