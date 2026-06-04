<?php

namespace App\Support;

final class PersonName
{
    public static function combine(string $firstName, string $lastName): string
    {
        return trim($firstName.' '.$lastName);
    }

    /**
     * @return array{first_name: string, last_name: string}
     */
    public static function split(?string $fullName): array
    {
        $fullName = trim((string) $fullName);
        if ($fullName === '') {
            return ['first_name' => '', 'last_name' => ''];
        }

        $parts = preg_split('/\s+/', $fullName, 2);

        return [
            'first_name' => $parts[0] ?? '',
            'last_name' => $parts[1] ?? '',
        ];
    }
}
