<?php

namespace App\Models;

use Carbon\Carbon;
use Doctrine\Inflector\Rules\Word;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

/**
 * Class Green
 * 
 * @property int $id
 * @property string|null $inventory_number
 * @property int $species_id
 * @property Carbon|null $planting_date
 * @property string|null $quality_state
 * @property string|null $quality_state_note
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Species $species
 * @property Bush|null $bush
 * @property Flower|null $flower
 * @property Collection|Work[] $works
 * @property Hedge|null $hedge
 * @property Collection|Tree[] $trees
 *
 * @package App\Models
 */
class Green extends Model
{
	protected $table = 'green';

	protected $casts = [
		'species_id' => 'int',
		'planting_date' => 'datetime'
	];

	protected $fillable = [
		'inventory_number',
		'species_id',
		'planting_date',
		'quality_state',
		'quality_state_note'
	];

	protected $appends = ['age'];

	protected function age(): Attribute
	{
		return Attribute::get(fn () =>
			$this->planting_date ? $this->planting_date->diffInYears(now()) : null
		);
	}

	public function species()
	{
		return $this->belongsTo(Species::class);
	}

	public function tree()
	{
		return $this->hasOne(Tree::class, 'id');
	}

	public function bush()
	{
		return $this->hasOne(Bush::class, 'id');
	}

	public function flower()
	{
		return $this->hasOne(Flower::class, 'id');
	}

	public function works()
	{
		return $this->hasMany(Work::class)->orderByDesc('recommendation_date');
	}

	public function hedge()
	{
		return $this->hasOne(Hedge::class, 'id');
	}
}
