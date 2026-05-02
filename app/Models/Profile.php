<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Profile extends Model
{
    protected $fillable = [
        'user_id',
        'birthdate',
        'age',
        'gender',
        'address',
        'photo_path',
        'height_cm',
        'weight_kg',
        'bmi',
        'fatigue_score',
        'injury_risk',
        'sports_interested',
        'field_expertise',
        'achievements',
        'profession',
        'coaching_experience_years',
    ];

    protected function casts(): array
    {
        return [
            'birthdate' => 'date',
            'sports_interested' => 'array',
            'height_cm' => 'decimal:2',
            'weight_kg' => 'decimal:2',
            'bmi' => 'decimal:2',
            'fatigue_score' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
