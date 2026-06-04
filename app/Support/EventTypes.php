<?php

namespace App\Support;

final class EventTypes
{
    public const TRAINING = 'training';

    public const GAME = 'game';

    public const TRYOUT = 'tryout';

    public const MEETING = 'meeting';

    public const INTRAMURALS = 'intramurals';

    public const PE_DAY = 'pe_day';

    public const UWEEK = 'uweek';

    public const SCHOOL_SPORTS = 'school_sports_program';

    /**
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            self::TRAINING => 'Training',
            self::GAME => 'Game / Match',
            self::TRYOUT => 'Tryout',
            self::MEETING => 'Meeting',
            self::INTRAMURALS => 'Local Intramurals',
            self::PE_DAY => 'PE Day',
            self::UWEEK => 'University Week (UWeek)',
            self::SCHOOL_SPORTS => 'School Sports Program',
        ];
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_keys(self::labels());
    }

    public static function label(string $type): string
    {
        return self::labels()[$type] ?? ucfirst(str_replace('_', ' ', $type));
    }
}
