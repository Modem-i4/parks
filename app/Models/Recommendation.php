<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Recommendation
 * 
 * @property int $id
 * @property string $name
 * @property bool $custom
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Collection|Work[] $work
 *
 * @package App\Models
 */
class Recommendation extends Model
{
	protected $table = 'recommendations';

	protected $casts = [
		'custom' => 'bool'
	];

	protected $fillable = [
		'name',
		'custom'
	];

	public function works()
	{
		return $this->hasMany(Work::class);
	}
}
