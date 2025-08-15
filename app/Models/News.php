<?php

namespace App\Models;

use App\Enums\MediaType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Class News
 * 
 * @property int $id
 * @property string $title
 * @property string $body
 * @property int $author_id
 * @property Carbon|null $published_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property User $author
 *
 * @package App\Models
 */
class News extends Model
{
    use HasFactory;
	
    protected $fillable = [
        'title',
        'body',
        'author_id',
        'published_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

	public function cover()
	{
		return $this->morphOne(Media::class, 'model')->ofType(MediaType::IMAGE->value)->with('mediaFile');
	}
}
