<?php

namespace App\Models;

use App\Enums\GreenType;
use App\Enums\MediaType;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\LogsChanges;

/**
 * Class Family
 * 
 * @property int $id
 * @property GreenType|string $type
 * @property string $name_ukr
 * @property string $name_lat
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Collection|Genus[] $genus
 *
 * @package App\Models
 */
class Family extends Model
{
    use LogsChanges;
	protected $table = 'families';

	protected $casts = [
		'type' => GreenType::class,
	];

	protected $fillable = [
		'name_ukr',
		'name_lat',
		'type',
	];

	public function genus()
	{
		return $this->hasMany(Genus::class);
	}

	public function media()
	{
		return $this->morphMany(Media::class, 'model')->ofType(MediaType::IMAGE->value)->with('mediaFile')->orderBy('order');
	}
}
