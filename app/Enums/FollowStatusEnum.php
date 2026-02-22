<?php

namespace App\Enums;

enum FollowStatusEnum: string
{
    case Pending = 'pending';
    case Accepted = 'accepted';
    case Rejected = 'rejected';

    public static function getValues(): array
    {
        return array_map(fn($case) => $case->value, self::cases());
    }
}
