<?php

namespace App\Enums;

/**
 * Roles enum
 */
enum Roles: string
{
    case ADMIN = 'admin';

    case AUTHOR = 'author';

    /**
     * Return an array of values for the given enum.
     *
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
