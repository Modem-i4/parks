<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class HedgeShape
 * 
 * @property string $name
 * @property string|null $description
 * 
 * @property Hedge|null $hedge
 *
 * @package App\Models
 */
class HedgeShape extends Model
{
	protected $table = 'hedge_shapes';
	protected $primaryKey = 'name';
	public $incrementing = false;
	public $timestamps = false;

	protected $fillable = [
		'description'
	];

	public function hedge()
	{
		return $this->hasOne(Hedge::class, 'hedge_shape');
	}
}
