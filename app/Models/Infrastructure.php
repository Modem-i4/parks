<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\LogsChanges;

/**
 * Class Infrastructure
 * 
 * @property int $id
 * @property string $name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Marker $marker
 *
 * @package App\Models
 */
class Infrastructure extends Model
{
    use LogsChanges;
	protected $table = 'infrastructure';
	public $incrementing = false;

	protected $casts = [
		'id' => 'int'
	];

	protected $fillable = [
		'id',
		'name',
		'infrastructure_type_id'
	];

	public function marker()
	{
		return $this->belongsTo(Marker::class);
	}

	
	public function infrastructureType()
	{
		return $this->belongsTo(InfrastructureType::class);
	}
}
