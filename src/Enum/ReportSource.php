<?php

declare(strict_types=1);

namespace App\Enum;

enum ReportSource: string
{
    case WEB = 'web';
    case MOBILE = 'mobile';
    case SYNC = 'sync';

    public static function values(): array
    {
        return array_map(static fn (self $case): string => $case->value, self::cases());
    }
}
