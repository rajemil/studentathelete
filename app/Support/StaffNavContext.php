<?php

namespace App\Support;

final class StaffNavContext
{
    public const PERFORMANCE = 'performance';

    public const PREDICTIVE = 'predictive';

    public const ANALYTICS = 'analytics';

    private const ALLOWED = [
        self::PERFORMANCE,
        self::PREDICTIVE,
        self::ANALYTICS,
    ];

    public static function isValid(?string $context): bool
    {
        return is_string($context) && in_array($context, self::ALLOWED, true);
    }

    public static function current(): ?string
    {
        $value = request()->query('context');

        return self::isValid($value) ? $value : null;
    }

    /**
     * @return array<string, string>
     */
    public static function query(string $context): array
    {
        return self::isValid($context) ? ['context' => $context] : [];
    }

    public static function backUrl(?string $context = null): ?string
    {
        return match ($context ?? self::current()) {
            self::PERFORMANCE => route('staff.performance_scores.hub'),
            self::PREDICTIVE => route('staff.ai_recommendations.hub'),
            self::ANALYTICS => route('analytics.index'),
            default => null,
        };
    }

    public static function backLabel(?string $context = null): string
    {
        return match ($context ?? self::current()) {
            self::PERFORMANCE => 'Back to performance scores',
            self::PREDICTIVE => 'Back to predictive recommendations',
            self::ANALYTICS => 'Back to analytics',
            default => 'Back',
        };
    }

    public static function isPerformanceActive(): bool
    {
        return request()->routeIs('staff.performance_scores.hub')
            || (request()->routeIs('sports.scores.*') && self::current() === self::PERFORMANCE);
    }

    public static function isPredictiveActive(): bool
    {
        return request()->routeIs('staff.ai_recommendations.hub')
            || (request()->routeIs('sports.team_suggestions.*') && self::current() === self::PREDICTIVE);
    }
}
