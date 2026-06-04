<?php

namespace App\Models;

use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Carbon\Carbon;

class Profile extends Model
{
    use BelongsToOrganization;

    protected static function organizationScopeRelation(): ?string
    {
        return 'user';
    }

    /**
     * Computed from birthdate / height+weight — not stored in the database.
     *
     * @var list<string>
     */
    protected $appends = [
        'age',
        'bmi',
    ];

    protected $fillable = [
        'user_id',
        'birthdate',
        'gender',
        'address',
        'course',
        'photo_path',
        'height_cm',
        'weight_kg',
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
            'fatigue_score' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

<<<<<<< Updated upstream
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
=======
    protected function age(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => 
                isset($attributes['birthdate']) && $attributes['birthdate'] 
                    ? Carbon::parse($attributes['birthdate'])->age 
                    : null,
        );
    }

    protected function bmi(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value, array $attributes) {
                if (empty($attributes['height_cm']) || empty($attributes['weight_kg']) || $attributes['height_cm'] <= 0) {
                    return null;
                }
                $heightM = $attributes['height_cm'] / 100.0;
                return round($attributes['weight_kg'] / ($heightM ** 2), 2);
            }
        );
>>>>>>> Stashed changes
    }
}
