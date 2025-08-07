<?php
namespace App\Enums;

enum UserRole: string
{
    case SUPER_ADMIN = 'super_admin'; // Admins assign
    case ADMIN = 'admin'; // Users manage
    case NEWS_MANAGER = 'news_manager'; // News editing
    case WORK_MANAGER = 'work_manager'; // Works managing
    case EDITOR = 'editor'; // Content + dictionaries editing
    case WORKER = 'worker'; // Works execution
    case VIEWER = 'viewer'; // Content viewing
    case GUEST = 'guest'; // Not accredited
    case DISMISSED = 'dismissed'; // Banned

    public function level(): int
    {
        return match($this) {
            self::SUPER_ADMIN => 8,
            self::ADMIN => 7,
            self::NEWS_MANAGER => 6,
            self::WORK_MANAGER => 5,
            self::EDITOR => 4,
            self::WORKER => 3,
            self::VIEWER => 2,
            self::GUEST => 1,
            self::DISMISSED => 0,
        };
    }

    public static function fromString(string $value): self
    {
        return self::tryFrom($value) ?? self::GUEST;
    }
    
    public static function values(): array
    {
        return array_map(fn($case) => $case->value, self::cases());
    }
}
