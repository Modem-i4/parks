<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Work
 * 
 * @property int $id
 * @property int $green_id
 * @property int $recommendation_id
 * @property Carbon $recommendation_date
 * @property int|null $recommender_id
 * @property Carbon|null $execution_date
 * @property int|null $executor_id
 * @property string|null $notes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Green $green
 * @property Recommendation $recommendation
 * @property User $recommender
 * @property User $executor
 *
 * @package App\Models
 */
class Work extends Model
{
	protected $table = 'works';

	protected $casts = [
		'green_id' => 'int',
		'recommendation_id' => 'int',
		'recommendation_date' => 'datetime',
		'green_id' => 'int',
		'execution_date' => 'datetime'
	];

	protected $fillable = [
		'green_id',
		'recommendation_id',
		'recommendation_date',
		'execution_date',
		'notes',
		'recommender_id',
		'executor_id',
	];

	public function green()
	{
		return $this->belongsTo(Green::class);
	}

	public function recommendation()
	{
    	return $this->belongsTo(Recommendation::class);
	}

	public function recommender()
	{
		return $this->belongsTo(User::class, 'recommender_id');
	}

	public function executor()
	{
		return $this->belongsTo(User::class, 'executor_id');
	}

}
