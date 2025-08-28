<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use App\Models\Concerns\LogsChanges;

/**
 * Class MediaLibrary
 * 
 * @property int $id
 * @property string $file_path
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Collection|Media[] $usages
 *
 * @package App\Models
 */
class MediaLibrary extends Model
{
    use LogsChanges;
    protected $table = 'media_library';

    protected $fillable = [
        'file_path',
        'type'
    ];

    public function usages(): HasMany
    {
        return $this->hasMany(Media::class);
    }

    public function getFilePathAttribute($value): string
    {
        $path = ltrim($value, '/');
        if (Storage::disk('public')->exists($path)) {
            return '/storage/' . $path;
        }
        return '/' . ($path ?? '');
    }
}
