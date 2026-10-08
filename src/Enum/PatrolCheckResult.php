<?php

declare(strict_types=1);

namespace App\Enum;

enum PatrolCheckResult: string
{
    case OK = 'ok';
    case OBSERVATION = 'observation';
    case INCIDENT = 'incident';
    case NOT_CHECKED = 'not_checked';
}

