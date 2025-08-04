<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use App\Enums\MediaType;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * Class Marker
 * 
 * @property int $id
 * @property int $park_id
 * @property string|null $type
 * @property array $coordinated
 * @property string|null $description
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Park $park
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
		'coordinates' => 'json',
		'type' => 'string',
	];

	protected $fillable = [
		'park_id',
		'type',
		'coordinates',
		'description'
	];

	public function park()
	{
		return $this->belongsTo(Park::class);
	}

	public function green()
	{
		return $this->hasOne(Green::class, 'id', 'id');
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
		return $this->morphMany(Media::class, 'model')->ofType(MediaType::IMAGE->value)->with('mediaFile')->orderBy('order');
	}


	public function icon(): MorphOne
	{
		return $this->morphOne(Media::class, 'model')->ofType(MediaType::ICON->value)->with('mediaFile')->orderBy('order');
	}

	public function getSubclassDataAttributes()
	{
		return [
			'quality_state' => $this->type === 'green' && $this->green
				? $this->green->quality_state
				: null,

			'name' => $this->type === 'infrastructure' && $this->infrastructure
				? $this->infrastructure->name
				: null,
		];
	}


	
}
