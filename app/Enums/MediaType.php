<?php
namespace App\Enums;

enum MediaType: string
{
    case IMAGE = 'image';
    case ICON = 'icon';
    
    public static function values(): array
    {
        return array_map(fn($case) => $case->value, self::cases());
    }
}