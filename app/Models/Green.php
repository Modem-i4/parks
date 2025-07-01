<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

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
 * @property Collection|GreenWorksHistory[] $green_works_histories
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

	public function green_works_histories()
	{
		return $this->hasMany(GreenWorksHistory::class);
	}

	public function hedge()
	{
		return $this->hasOne(Hedge::class, 'id');
	}

	public function trees()
	{
		return $this->hasMany(Tree::class, 'id');
	}
}
