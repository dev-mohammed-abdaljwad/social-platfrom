<?php 
namespace App\Enums;

enum PrivacyTypeEnum: string 
{
    case Privet = 'private'; 
    case Public = 'public'; 
    case Friends = 'friends';

    public static function getValues(): array
    {
        return array_map(fn($case) => $case->value, self::cases());
    }
}