<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\LogsChanges;

/**
 * Class Bush
 * 
 * @property int $id
 * @property int|null $quantity
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Green $green
 *
 * @package App\Models
 */
class Bush extends Model
{
    use LogsChanges;
	protected $table = 'bushes';
	public $incrementing = false;

	protected $casts = [
		'id' => 'int',
		'quantity' => 'int'
	];

	protected $fillable = [
		'id',
		'quantity'
	];

	public function green()
	{
		return $this->belongsTo(Green::class, 'id');
	}
}
