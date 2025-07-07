<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Genus
 * 
 * @property int $id
 * @property int $family_id
 * @property string $name_ukr
 * @property string $name_lat
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Family $family
 * @property Collection|Species[] $species
 *
 * @package App\Models
 */
class Genus extends Model
{
	protected $table = 'genus';

	protected $casts = [
		'family_id' => 'int'
	];

	protected $fillable = [
		'family_id',
		'name_ukr',
		'name_lat'
	];

	public function family()
	{
		return $this->belongsTo(Family::class);
	}

	public function species()
	{
		return $this->hasMany(Species::class);
	}
}
