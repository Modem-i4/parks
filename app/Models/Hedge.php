<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Hedge
 * 
 * @property int $id
 * @property float|null $length_m
 * @property string|null $hedge_row
 * @property string|null $hedge_shape
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Green $green
 *
 * @package App\Models
 */
class Hedge extends Model
{
	protected $table = 'hedges';
	public $incrementing = false;

	protected $casts = [
		'id' => 'int',
		'length_m' => 'float'
	];

	protected $fillable = [
		'id',
		'length_m',
		'hedge_row',
		'hedge_shape'
	];

	public function hedge_row()
	{
		return $this->belongsTo(HedgeRow::class, 'hedge_row');
	}

	public function hedge_shape()
	{
		return $this->belongsTo(HedgeShape::class, 'hedge_shape');
	}

	public function green()
	{
		return $this->belongsTo(Green::class, 'id');
	}
}
