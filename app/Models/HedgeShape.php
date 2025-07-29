<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class HedgeShape
 * 
 * @property int $id
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
	public $timestamps = false;

	protected $fillable = [
		'name',
		'description'
	];

	public function hedge()
	{
		return $this->hasOne(Hedge::class, 'hedge_shape');
	}
}
