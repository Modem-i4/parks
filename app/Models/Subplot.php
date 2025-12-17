<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\LogsChanges;

/**
 * Class Subplot
 * 
 * @property int $id
 * @property int $plot_id
 * @property string $name
 * @property array $coordinates
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Plot $plot
 * @property Collection|Marker[] $markers
 *
 * @package App\Models
 */
class Subplot extends Model
{
    use LogsChanges;
	protected $table = 'subplots';

	protected $casts = [
		'plot_id' => 'int',
		'coordinates' => 'json'
	];

	protected $fillable = [
		'plot_id',
		'name',
		'coordinates'
	];

	public function plot()
	{
		return $this->belongsTo(Plot::class);
	}

	public function markers()
	{
		return $this->hasMany(Marker::class);
	}
}
