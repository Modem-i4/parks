<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class GreenWorkRecommendation
 * 
 * @property int $green_work_id
 * @property int $recommendation_id
 * 
 * @property GreenWorksHistory $green_works_history
 * @property Recommendation $recommendation
 *
 * @package App\Models
 */
class GreenWorkRecommendation extends Model
{
	protected $table = 'green_work_recommendations';
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'green_work_id' => 'int',
		'recommendation_id' => 'int'
	];

	public function green_works_history()
	{
		return $this->belongsTo(GreenWorksHistory::class, 'green_work_id');
	}

	public function recommendation()
	{
		return $this->belongsTo(Recommendation::class);
	}
}
