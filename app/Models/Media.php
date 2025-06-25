<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Media
 * 
 * @property int $id
 * @property string $model_type
 * @property int $model_id
 * @property string $file_path
 * @property string|null $description
 * @property int $order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models
 */
class Media extends Model
{
    protected $table = 'media';

    protected $casts = [
        'model_id' => 'int',
        'order' => 'int',
        'file_path' => 'string',
    ];

    protected $fillable = [
        'model_type',
        'model_id',
        'file_path',
        'description',
        'order'
    ];

    protected $hidden = ['model_type', 'model_id'];

    public function model()
    {
        return $this->morphTo();
    }

    public function getFilePathAttribute($value)
    {
        if (str_starts_with($value, '/storage/')) {
            return $value;
        }
        return '/storage/' . ltrim($value, '/');
    }
}
