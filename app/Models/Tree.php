<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Tree
 * 
 * @property int $id
 * @property float|null $height_m
 * @property float|null $trunk_diameter_cm
 * @property float|null $trunk_circumference_cm
 * @property float|null $tilt_degree
 * @property float|null $crown_condition_percent
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Green $green
 *
 * @package App\Models
 */
class Tree extends Model
{
	protected $table = 'trees';
	public $incrementing = false;

	protected $casts = [
		'id' => 'int',
		'height_m' => 'float',
		'trunk_diameter_cm' => 'float',
		'trunk_circumference_cm' => 'float',
		'tilt_degree' => 'float',
		'crown_condition_percent' => 'float'
	];

	protected $fillable = [
		'id',
		'height_m',
		'trunk_diameter_cm',
		'trunk_circumference_cm',
		'tilt_degree',
		'crown_condition_percent'
	];

	public function green()
	{
		return $this->belongsTo(Green::class, 'id');
	}
}
