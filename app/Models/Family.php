<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Family
 * 
 * @property int $id
 * @property string $name_ukr
 * @property string $name_lat
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Collection|Genus[] $genera
 *
 * @package App\Models
 */
class Family extends Model
{
	protected $table = 'families';

	protected $fillable = [
		'name_ukr',
		'name_lat'
	];

	public function genera()
	{
		return $this->hasMany(Genus::class);
	}
}
