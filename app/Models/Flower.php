<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Flower
 * 
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Green $green
 *
 * @package App\Models
 */
class Flower extends Model
{
	protected $table = 'flowers';
	public $incrementing = false;

	protected $casts = [
		'id' => 'int'
	];

	protected $fillable = [
		'id'
	];

	public function green()
	{
		return $this->belongsTo(Green::class, 'id');
	}
}
