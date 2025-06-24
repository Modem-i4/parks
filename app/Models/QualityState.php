<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class QualityState
 * 
 * @property string $name
 * @property string|null $description
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Collection|Green[] $greens
 *
 * @package App\Models
 */
class QualityState extends Model
{
	protected $table = 'quality_states';
	protected $primaryKey = 'name';
	public $incrementing = false;

	protected $fillable = [
		'description'
	];

	public function greens()
	{
		return $this->hasMany(Green::class, 'quality_state');
	}
}
