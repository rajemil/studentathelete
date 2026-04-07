<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Sport extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
    ];

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
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
