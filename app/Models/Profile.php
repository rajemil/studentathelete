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
        'course_id',
        'year_level_id',
        'section_id',
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

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function yearLevel(): BelongsTo
    {
        return $this->belongsTo(YearLevel::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }
}
