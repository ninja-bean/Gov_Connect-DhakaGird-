<?php

declare(strict_types=1);

namespace App\Core\Enums;

enum Category: string
{
    case Police = 'police';
    case Medical = 'medical';
    case Fire = 'fire';
    case Gov = 'gov';
    case Other = 'other';
}