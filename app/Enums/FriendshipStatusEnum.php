<?php

namespace App\Enums;

enum FriendshipStatusEnum: string
{
    case Pending = 'pending';
    case Accepted = 'accepted';
    case Rejected = 'rejected';
    case Blocked = 'blocked';

    public static function getValues(): array
    {
        return array_map(fn($case) => $case->value, self::cases());
    }
}
