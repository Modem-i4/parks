<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use App\Enums\SpeciesType;

/**
 * Class Species
 * 
 * @property int $id
 * @property string $type
 * @property int $genus_id
 * @property string $name_ukr
 * @property string $name_lat
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Genus $genus
 * @property Collection|Green[] $greens
 *
 * @package App\Models
 */
class Species extends Model
{
	protected $table = 'species';

	protected $casts = [
		'genus_id' => 'int',
		'type' => SpeciesType::class,
	];

	protected $fillable = [
		'type',
		'genus_id',
		'name_ukr',
		'name_lat'
	];

	public function genus()
	{
		return $this->belongsTo(Genus::class);
	}

	public function greens()
	{
		return $this->hasMany(Green::class);
	}
}
