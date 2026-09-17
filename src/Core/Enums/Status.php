<?php

declare(strict_types=1);

namespace App\Core\Enums;

enum Status: string
{
    case Pending = 'pending';
    case Verified = 'verified';
    case Assigned = 'assigned';
    case Working = 'working';
    case Resolved = 'resolved';
    case Rejected = 'rejected';
}