<?php

namespace App\Models;

use Carbon\Carbon;
use Doctrine\Inflector\Rules\Word;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use App\Models\Concerns\LogsChanges;

/**
 * Class Green
 * 
 * @property int $id
 * @property string|null $inventory_number
 * @property string|null $inventory_number_old
 * @property int $species_id
 * @property int|null $plot_id
 * @property int|null $subplot_id
 * @property Carbon|null $planting_date
 * @property string|null $green_state
 * @property Carbon|null $green_state_changed_at
 * @property string|null $green_state_note
 * @property float|null $age
 * @property int|null $age_months
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Plot|null $plot
 * @property Sublot|null $subplot
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
    use LogsChanges;
	protected $table = 'green';

	protected $casts = [
		'species_id' => 'int',
		'subplot_id' => 'int',
		'planting_date' => 'datetime',
		'green_state_changed_at' => 'date:Y-m-d'
	];

	protected $fillable = [
		'inventory_number',
		'inventory_number_old',
		'species_id',
		'subplot_id',
		'planting_date',
		'green_state',
		'green_state_changed_at',
		'green_state_note'
	];

	protected $appends = ['age', 'age_months', 'plot', 'plot_id'];

	protected static function booted(): void
	{
		static::creating(function (Green $green) {
			if (!$green->green_state_changed_at) {
				$green->green_state_changed_at = today();
			}
		});

		static::updating(function (Green $green) {
			if ($green->isDirty('green_state') && !$green->isDirty('green_state_changed_at')) {
				$green->green_state_changed_at = today();
			}
		});
	}

	protected function age(): Attribute
	{
		return Attribute::get(fn () =>
			$this->planting_date ? $this->planting_date->diffInYears(now()) : null
		);
	}

	protected function ageMonths(): Attribute
	{
		return Attribute::get(fn () =>
			$this->planting_date ? (int) $this->planting_date->diffInMonths(now()) : null
		);
	}

	public function getPlotAttribute()
	{
		return $this->subplot?->plot;
	}

	public function getPlotIdAttribute()
	{
    	return $this->subplot?->plot_id;
	}

	public function subplot()
	{
		return $this->belongsTo(Subplot::class);
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
