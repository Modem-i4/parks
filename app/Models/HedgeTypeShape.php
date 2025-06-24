<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class HedgeTypeShape
 * 
 * @property string $name
 * @property string|null $description
 * 
 * @property Hedge|null $hedge
 *
 * @package App\Models
 */
class HedgeTypeShape extends Model
{
	protected $table = 'hedge_type_shapes';
	protected $primaryKey = 'name';
	public $incrementing = false;
	public $timestamps = false;

	protected $fillable = [
		'description'
	];

	public function hedge()
	{
		return $this->hasOne(Hedge::class, 'hedge_type_shape');
	}
}
