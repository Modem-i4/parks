<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\LogsChanges;

/**
 * Class MarkersTag
 * 
 * @property int $marker_id
 * @property int $tag_id
 * 
 * @property Marker $marker
 * @property Tag $tag
 *
 * @package App\Models
 */
class MarkersTag extends Model
{
    use LogsChanges;
	protected $table = 'markers_tags';
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'marker_id' => 'int',
		'tag_id' => 'int'
	];

	public function marker()
	{
		return $this->belongsTo(Marker::class);
	}

	public function tag()
	{
		return $this->belongsTo(Tag::class);
	}
}
