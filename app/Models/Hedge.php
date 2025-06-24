<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Hedge
 * 
 * @property int $id
 * @property float|null $length_m
 * @property string|null $hedge_type_row
 * @property string|null $hedge_type_shape
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
		'hedge_type_row',
		'hedge_type_shape'
	];

	public function hedge_type_row()
	{
		return $this->belongsTo(HedgeTypeRow::class, 'hedge_type_row');
	}

	public function hedge_type_shape()
	{
		return $this->belongsTo(HedgeTypeShape::class, 'hedge_type_shape');
	}

	public function green()
	{
		return $this->belongsTo(Green::class, 'id');
	}
}
