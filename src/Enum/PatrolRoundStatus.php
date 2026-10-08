<?php

declare(strict_types=1);

namespace App\Enum;

enum PatrolRoundStatus: string
{
    case DRAFT = 'draft';
    case STARTED = 'started';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
}

