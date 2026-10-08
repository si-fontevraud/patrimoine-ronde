<?php

declare(strict_types=1);

namespace App\Enum;

enum IncidentWaitReason: string
{
    case IT = 'it';
    case TECHNICAL = 'technical';
    case VENDOR = 'vendor';
    case OTHER = 'other';
}

