<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Plot
 * 
 * @property int $id
 * @property int $park_id
 * @property string $name
 * @property string|null $description
 * @property float $area
 * @property array $geo_json
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Park $park
 * @property Collection|Marker[] $markers
 *
 * @package App\Models
 */
class Plot extends Model
{
	protected $table = 'plots';

	protected $casts = [
		'park_id' => 'int',
		'area' => 'float',
		'geo_json' => 'json'
	];

	protected $fillable = [
		'park_id',
		'name',
		'description',
		'area',
		'geo_json'
	];

	public function park()
	{
		return $this->belongsTo(Park::class);
	}

	public function markers()
	{
		return $this->hasMany(Marker::class);
	}
}
