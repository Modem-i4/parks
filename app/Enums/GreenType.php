<?php
namespace App\Enums;

enum GreenType: string
{
    case TREE = 'tree';
    case BUSH = 'bush';
    case HEDGE = 'hedge';
    case FLOWER = 'flower';
    
    public static function values(): array
    {
        return array_map(fn($case) => $case->value, self::cases());
    }
}