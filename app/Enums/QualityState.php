<?php
namespace App\Enums;

enum QualityState: string
{
    case GOOD = 'good';
    case NORMAL = 'normal';
    case BAD = 'bad';

    public function level(): int
    {
        return match($this) {
            self::GOOD => 2,
            self::NORMAL => 1,
            self::BAD => 0,
        };
    }

    public static function fromString(string $value): self
    {
        return self::tryFrom($value) ?? self::NORMAL;
    }
    
    public static function values(): array
    {
        return array_map(fn($case) => $case->value, self::cases());
    }
}