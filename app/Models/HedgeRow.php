<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\LogsChanges;

/**
 * Class HedgeRow
 * 
 * @property int $id
 * @property string $name
 * @property string|null $description
 * 
 * @property Hedge|null $hedge
 *
 * @package App\Models
 */
class HedgeRow extends Model
{
    use LogsChanges;
	protected $table = 'hedge_rows';
	public $timestamps = false;

	protected $fillable = [
		'name',
		'description'
	];

	public function hedge()
	{
		return $this->hasOne(Hedge::class, 'hedge_row');
	}
}
