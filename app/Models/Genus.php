<?php

namespace App\Models;

use App\Enums\MediaType;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\LogsChanges;

/**
 * Class Genus
 * 
 * @property int $id
 * @property int $family_id
 * @property string $name_ukr
 * @property string $name_lat
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Family $family
 * @property Collection|Species[] $species
 *
 * @package App\Models
 */
class Genus extends Model
{
    use LogsChanges;
	protected $table = 'genus';

	protected $casts = [
		'family_id' => 'int'
	];

	protected $fillable = [
		'family_id',
		'name_ukr',
		'name_lat'
	];

	public function family()
	{
		return $this->belongsTo(Family::class);
	}

	public function species()
	{
		return $this->hasMany(Species::class);
	}
	
	public function media()
	{
		return $this->morphMany(Media::class, 'model')->ofType(MediaType::IMAGE->value)->with('mediaFile')->orderBy('order');
	}
}
