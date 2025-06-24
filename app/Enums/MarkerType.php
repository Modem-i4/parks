<?php
namespace App\Enums;

enum MarkerType: string
{
    case GREEN = 'green';
    case INFRASTRUCTURE = 'infrastructure';
    
    public static function values(): array
    {
        return array_map(fn($case) => $case->value, self::cases());
    }
}