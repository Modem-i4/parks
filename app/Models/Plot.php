<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\LogsChanges;

/**
 * Class Plot
 * 
 * @property int $id
 * @property int $park_id
 * @property string $name
 * @property array $coordinates
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Park $park
 * @property Collection|Marker[] $markers
 *
 * @package App\Models
 */
class Plot extends Model
{
    use LogsChanges;
	protected $table = 'plots';

	protected $casts = [
		'park_id' => 'int',
		'coordinates' => 'json'
	];

	protected $fillable = [
		'park_id',
		'name',
		'coordinates'
	];

	public function park()
	{
		return $this->belongsTo(Park::class);
	}

	public function markers()
	{
		return $this->hasMany(Marker::class);
	}
}
