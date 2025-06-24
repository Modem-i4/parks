<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class HedgeTypeRow
 * 
 * @property string $name
 * @property string|null $description
 * 
 * @property Hedge|null $hedge
 *
 * @package App\Models
 */
class HedgeTypeRow extends Model
{
	protected $table = 'hedge_type_rows';
	protected $primaryKey = 'name';
	public $incrementing = false;
	public $timestamps = false;

	protected $fillable = [
		'description'
	];

	public function hedge()
	{
		return $this->hasOne(Hedge::class, 'hedge_type_row');
	}
}
