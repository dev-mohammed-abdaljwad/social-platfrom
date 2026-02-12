<?php 
namespace App\Enums;

enum ContentTypeEnum: string 
{
    case Text = 'text';
    case Image = 'image'; 
    case Video = 'video';
    public static function getValues(): array
    {
        return array_map(fn($case) => $case->value, self::cases());
    }
}