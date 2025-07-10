<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Class Media
 * 
 * @property int $id
 * @property int $media_library_id
 * @property string $model_type
 * @property int $model_id
 * @property int $order
 * @property string|null $description
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property-read MediaLibrary $mediaFile
 * @property-read Model $model
 *
 * @package App\Models
 */
class Media extends Model
{
    protected $table = 'media';

    protected $fillable = [
        'media_library_id',
        'model_type',
        'model_id',
        'order',
        'description',
    ];

    protected $appends = [
        'file_path',
    ];
    protected $hidden = ['mediaFile'];

    public function mediaFile(): BelongsTo
    {
        return $this->belongsTo(MediaLibrary::class, 'media_library_id');
    }

    public function model(): MorphTo
    {
        return $this->morphTo();
    }

    public function getFilePathAttribute(): ?string
    {
        return $this->mediaFile?->file_path;
    }

    public function getTypeAttribute(): ?string
    {
        return $this->mediaFile?->type;
    }

    public function scopeOfType($query, string $type)
    {
        return $query->whereHas('mediaFile', fn($q) => $q->where('type', $type));
    }
}