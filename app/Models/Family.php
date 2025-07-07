<?php

namespace App\Models;

use App\Enums\GreenType;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Family
 * 
 * @property int $id
 * @property GreenType|string $type
 * @property string $name_ukr
 * @property string $name_lat
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Collection|Genus[] $genus
 *
 * @package App\Models
 */
class Family extends Model
{
	protected $table = 'families';

	protected $casts = [
		'type' => GreenType::class,
	];

	protected $fillable = [
		'name_ukr',
		'name_lat',
		'type',
	];

	public function genus()
	{
		return $this->hasMany(Genus::class);
	}
}
