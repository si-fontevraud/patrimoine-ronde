<?php

declare(strict_types=1);

namespace App\Enum;

enum ReportStatus: string
{
    case NEW = 'new';
    case QUALIFIED = 'qualified';
    case ASSIGNED = 'assigned';
    case IN_PROGRESS = 'in_progress';
    case ON_HOLD = 'on_hold';
    case RESOLVED = 'resolved';
    case CLOSED = 'closed';

    public static function values(): array
    {
        return array_map(static fn (self $case): string => $case->value, self::cases());
    }
}
