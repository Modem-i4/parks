<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class GreenWorksHistory
 * 
 * @property int $id
 * @property int $green_id
 * @property Carbon $recommendation_date
 * @property Carbon|null $execution_date
 * @property string|null $notes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Green $green
 * @property Collection|GreenWorkRecommendation[] $green_work_recommendations
 *
 * @package App\Models
 */
class GreenWorksHistory extends Model
{
	protected $table = 'green_works_history';

	protected $casts = [
		'green_id' => 'int',
		'recommendation_date' => 'datetime',
		'execution_date' => 'datetime'
	];

	protected $fillable = [
		'green_id',
		'recommendation_date',
		'execution_date',
		'notes'
	];

	public function green()
	{
		return $this->belongsTo(Green::class);
	}

	public function green_work_recommendations()
	{
		return $this->hasMany(GreenWorkRecommendation::class, 'green_work_id');
	}
}
