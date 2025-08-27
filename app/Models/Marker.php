<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use App\Enums\MediaType;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;


/**
 * Class Marker
 * 
 * @property int $id
 * @property int $park_id
 * @property string|null $type
 * @property array $coordinates
 * @property string|null $description
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * 
 * @property Park $park
 * @property Infrastructure|null $infrastructure
 * @property Collection|Tag[] $tags
 *
 * @package App\Models
 */

class Marker extends Model
{
    use SoftDeletes;
	protected $table = 'markers';

	protected $casts = [
		'park_id' => 'int',
		'coordinates' => 'json',
		'type' => 'string'
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
			'green_state' => $this->type === 'green' && $this->green
				? $this->green->green_state
				: null,

			'name' => $this->type === 'infrastructure' && $this->infrastructure
				? $this->infrastructure->name
				: null,
		];
	}

	public function relationsToArray()
    {
		$relations = parent::relationsToArray();

		if (!array_key_exists('icon', $relations) || is_null($relations['icon'])) {
			$typeIcon = $this->infrastructure?->infrastructureType?->icon;
			if ($typeIcon) {
				$relations['icon'] = [
					'id'        => $typeIcon->id,
					'file_path' => $typeIcon->file_path,
				];
			}
		}

		return $relations;
    }
	
}
