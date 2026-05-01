<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Organization extends Model
{
    protected $fillable = [
        'name',
        'slug',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function sports(): HasMany
    {
        return $this->hasMany(Sport::class);
    }

    public static function defaultId(): int
    {
        static $id;

        if ($id === null) {
            $id = (int) static::query()->where('slug', 'default')->value('id');
            if ($id === 0) {
                throw new \RuntimeException('Default organization is missing. Run migrations.');
            }
        }

        return $id;
    }
}
