<?php

namespace App\Support;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

final class RegistrationRules
{
    /**
     * @return array<string, mixed>
     */
    public static function nameFields(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:120'],
            'last_name' => ['required', 'string', 'max:120'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function passwordRequired(): array
    {
        return [
            'password' => ['required', 'confirmed', Password::defaults()],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function passwordOptional(): array
    {
        return [
            'password' => ['nullable', 'confirmed', Password::defaults()],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function studentProfileFields(bool $requireAll = true): array
    {
        $required = $requireAll ? 'required' : 'nullable';

        return [
            'birthdate' => [$required, 'date', 'before:today', 'after:'.now()->subYears(80)->toDateString()],
            'gender' => [$required, 'string', 'in:male,female,other,prefer_not_to_say,Male,Female,Non-binary,Prefer not to say'],
            'address' => [$required, 'string', 'max:255'],
            'course' => [$required, 'string', 'max:255'],
            'height_cm' => [$required, 'numeric', 'min:50', 'max:300'],
            'weight_kg' => [$required, 'numeric', 'min:10', 'max:350'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    /**
     * @return array<string, mixed>
     */
    public static function facultyPhotoField(): array
    {
        return [
            'photo' => ['nullable', 'file', 'image', 'max:5120'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function facultyProfileFields(bool $requireAll = true): array
    {
        $required = $requireAll ? 'required' : 'nullable';

        return [
            'birthdate' => [
                $required,
                'date',
                'before_or_equal:'.now()->subYears(18)->toDateString(),
                'after:'.now()->subYears(90)->toDateString(),
            ],
            'gender' => [$required, 'string', 'in:male,female,other,prefer_not_to_say,Male,Female,Non-binary,Prefer not to say'],
            'address' => [$required, 'string', 'max:255'],
            'profession' => [$required, 'string', 'max:255'],
            'field_expertise' => [$required, 'string', 'max:255'],
            'coaching_experience_years' => [$required, 'integer', 'min:0', 'max:80'],
        ];
    }

    /**
     * @param  list<int>  $allowedSportIds
     * @return array<string, mixed>
     */
    public static function sportIds(int $organizationId, array $allowedSportIds = [], bool $required = false): array
    {
        $rules = [
            'sport_ids' => [$required ? 'required' : 'sometimes', 'array'],
            'sport_ids.*' => [
                'integer',
                Rule::exists('sports', 'id')->where(
                    fn ($query) => $query->where('organization_id', $organizationId)
                ),
            ],
        ];

        if ($allowedSportIds !== []) {
            $rules['sport_ids.*'][] = Rule::in($allowedSportIds);
        }

        return $rules;
    }

    /**
     * @return array<string, mixed>
     */
    public static function sportsInterested(int $organizationId): array
    {
        return [
            'sports_interested' => ['nullable', 'array', 'max:20'],
            'sports_interested.*' => [
                'integer',
                Rule::exists('sports', 'id')->where(
                    fn ($query) => $query->where('organization_id', $organizationId)
                ),
            ],
        ];
    }

    public static function normalizeGender(string $gender): string
    {
        return match (strtolower(trim($gender))) {
            'male' => 'male',
            'female' => 'female',
            'non-binary', 'other' => 'other',
            'prefer not to say', 'prefer_not_to_say' => 'prefer_not_to_say',
            default => strtolower(trim($gender)),
        };
    }
}
