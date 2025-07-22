<?php
namespace App\Enums;

enum TagType: string
{
    case ALL = 'all';
    case INFRASTRUCTURE = 'infrastructure';
    case TREE = 'tree';
    case BUSH = 'bush';
    case HEDGE = 'hedge';
    case FLOWER = 'flower';

    public static function values(): array
    {
        return array_map(fn($case) => $case->value, self::cases());
    }

    public static function isGreenType(self|string $type): bool
    {
        $value = $type instanceof self ? $type->value : $type;
        return in_array($value, GreenType::values(), true);
    }
}
