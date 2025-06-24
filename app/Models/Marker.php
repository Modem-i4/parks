<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use App\Enums\MarkerType;

/**
 * Class Marker
 * 
 * @property int $id
 * @property int $park_id
 * @property int|null $plot_id
 * @property string $type
 * @property array $coordinated
 * @property string|null $description
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Park $park
 * @property Plot|null $plot
 * @property Infrastructure|null $infrastructure
 * @property Collection|Tag[] $tags
 *
 * @package App\Models
 */
class Marker extends Model
{
	protected $table = 'markers';

	protected $casts = [
		'park_id' => 'int',
		'plot_id' => 'int',
		'coordinated' => 'json'
		'type' => MarkerType::class,
	];

	protected $fillable = [
		'park_id',
		'plot_id',
		'type',
		'coordinated',
		'description'
	];

	public function park()
	{
		return $this->belongsTo(Park::class);
	}

	public function plot()
	{
		return $this->belongsTo(Plot::class);
	}

	public function infrastructure()
	{
		return $this->hasOne(Infrastructure::class, 'id');
	}

	public function tags()
	{
		return $this->belongsToMany(Tag::class, 'markers_tags');
	}

	public function media()
	{
		return $this->morphMany(Media::class, 'model');
	}
}
