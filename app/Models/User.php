<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Enums\UserRole;

/**
 * Class User
 * 
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $role
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models
 */
class User extends Authenticatable
{
    use Notifiable;
	protected $table = 'users';

	protected $casts = [
		'email_verified_at' => 'datetime',
        'role' => UserRole::class,
	];

	protected $hidden = [
		'password',
		'remember_token'
	];

	protected $fillable = [
		'name',
		'email',
		'role',
		'email_verified_at',
		'password',
		'remember_token'
	];

    public function roleEnum(): UserRole
    {
        return UserRole::fromString($this->role->value);
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
