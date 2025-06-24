<?php
namespace App\Enums;

enum UserRole: string
{
    case MAIN_ADMIN = 'main_admin'; // Admins assign
    case ADMIN = 'admin'; // Roles assign
    case FULL_MANAGER = 'full_manager'; // Dictionaries editing
    case CONTENT_MANAGER = 'content_manager'; // Content editing
    case WORKER = 'worker'; // Works marking
    case USER = 'user'; // Content viewing
    case UNAUTHORIZED = 'unauthorized'; // Waiting for approval

    public function level(): int
    {
        return match($this) {
            self::MAIN_ADMIN => 6,
            self::ADMIN => 5,
            self::FULL_MANAGER => 4,
            self::CONTENT_MANAGER => 3,
            self::WORKER => 2,
            self::USER => 1,
            self::UNAUTHORIZED => 0,
        };
    }

    public static function fromString(string $value): self
    {
        return self::tryFrom($value) ?? self::UNAUTHORIZED;
    }
    
    public static function values(): array
    {
        return array_map(fn($case) => $case->value, self::cases());
    }
}
