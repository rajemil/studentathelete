<?php

namespace App\Services\Sports;

use App\Models\Sport;
use App\Models\User;

final class SportApplicationQualificationService
{
    /**
     * @return array{passed: bool, reasons: list<string>}
     */
    public function evaluate(User $student, Sport $sport): array
    {
        $profile = $student->profile;
        $reasons = [];
        $hasAnyRule = $sport->qual_min_age !== null
            || $sport->qual_max_age !== null
            || $sport->qual_min_height_cm !== null
            || (is_array($sport->qual_allowed_genders) && count($sport->qual_allowed_genders) > 0);

        if (! $hasAnyRule) {
            return ['passed' => true, 'reasons' => ['No eligibility rules are set for this sport.']];
        }

        $age = $profile?->age;
        if ($sport->qual_min_age !== null) {
            if ($age === null) {
                $reasons[] = 'Minimum age requires a birthdate on your profile.';
            } elseif ($age < (int) $sport->qual_min_age) {
                $reasons[] = 'Age is below the minimum for this sport.';
            }
        }
        if ($sport->qual_max_age !== null) {
            if ($age === null) {
                $reasons[] = 'Maximum age requires a birthdate on your profile.';
            } elseif ($age > (int) $sport->qual_max_age) {
                $reasons[] = 'Age is above the maximum for this sport.';
            }
        }

        if ($sport->qual_min_height_cm !== null) {
            $h = $profile?->height_cm;
            if ($h === null) {
                $reasons[] = 'Minimum height requires height (cm) on your profile.';
            } elseif ((float) $h < (float) $sport->qual_min_height_cm) {
                $reasons[] = 'Height is below the minimum for this sport.';
            }
        }

        $allowed = $sport->qual_allowed_genders;
        if (is_array($allowed) && count($allowed) > 0) {
            $g = $profile?->gender;
            if ($g === null || $g === '') {
                $reasons[] = 'Gender restrictions require gender on your profile.';
            } elseif (! in_array($g, $allowed, true)) {
                $reasons[] = 'Gender is not within the allowed list for this sport.';
            }
        }

        if ($reasons === []) {
            return ['passed' => true, 'reasons' => ['You meet the published eligibility rules for this sport.']];
        }

        return ['passed' => false, 'reasons' => $reasons];
    }
}
