<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use App\Enums\TagType;

/**
 * Class Tag
 * 
 * @property int $id
 * @property string $name
 * @property bool $public
 * @property bool $custom
 * @property string|TagType $type
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Collection|Marker[] $markers
 *
 * @package App\Models
 */
class Tag extends Model
{
	protected $table = 'tags';

	protected $casts = [
		'public' => 'bool',
		'custom' => 'bool',
		'type' => TagType::class
	];

	protected $fillable = [
		'name',
		'public',
		'custom',
		'type'
	];

	public function markers()
	{
		return $this->belongsToMany(Marker::class, 'markers_tags');
	}
}
