<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Enums\UserRole;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function roleEnum(): UserRole
    {
        return UserRole::fromString($this->role);
    }

    public function isOnly(UserRole|string $role): bool
    {
        $target = $role instanceof UserRole ? $role->value : $role;
        return $this->role === $target;
    }

    public function atLeast(UserRole|string $role): bool
    {
        $current = $this->roleEnum();
        $target = $role instanceof UserRole ? $role : UserRole::fromString($role);

        return $current->level() >= $target->level();
    }

}
