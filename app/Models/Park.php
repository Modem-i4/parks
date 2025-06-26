<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use App\Enums\MediaType;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * Class Park
 * 
 * @property int $id
 * @property string $name
 * @property string $address
 * @property float $area
 * @property string|null $description
 * @property array $coordinates
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Collection|Marker[] $markers
 * @property Collection|Plot[] $plots
 *
 * @package App\Models
 */
class Park extends Model
{
	protected $table = 'parks';

	protected $casts = [
		'area' => 'float',
		'coordinates' => 'json',
	];

	protected $fillable = [
		'name',
		'address',
		'area',
		'description',
		'coordinates'
	];

	public function markers()
	{
		return $this->hasMany(Marker::class);
	}

	public function plots()
	{
		return $this->hasMany(Plot::class);
	}

	public function media()
	{
		return $this->morphMany(Media::class, 'model')->where('type', MediaType::IMAGE->value)->orderBy('order');
	}

	public function icon(): MorphOne
	{
		return $this->morphOne(Media::class, 'model')->where('type', MediaType::ICON->value)->orderBy('order');
	}

}
