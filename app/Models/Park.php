<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use App\Enums\MediaType;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use App\Models\Concerns\LogsChanges;

/**
 * Class Park
 * 
 * @property int $id
 * @property string $name
 * @property string $address
 * @property string $operator
 * @property float $area
 * @property string|null $description
 * @property array|null $coordinates
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Collection|Marker[] $markers
 * @property Collection|Plot[] $plots
 *
 * @package App\Models
 */
class Park extends Model
{
    use LogsChanges;
    protected $table = 'parks';

    protected $casts = [
        'area' => 'float',
        'geo_json' => 'json',
    ];

    protected $fillable = [
        'name',
        'slug',
        'address',
        'area',
        'geo_json',
        'operator'
    ];

    protected $appends = ['coordinates', 'type'];

    public function getCoordinatesAttribute(): ?array
    {
        return $this->geo_json['properties']['center'] ?? null;
    }


    public function markers()
    {
        return $this->hasMany(Marker::class);
    }

    public function plots()
    {
        return $this->hasMany(Plot::class);
    }

    public function media()
    {
        return $this->morphMany(Media::class, 'model')->ofType(MediaType::IMAGE->value)->with('mediaFile')->orderBy('order');
    }

    public function icon(): MorphOne
    {
        return $this->morphOne(Media::class, 'model')->ofType(MediaType::ICON->value)->with('mediaFile')->orderBy('order');
    }
        
    public function getTypeAttribute()
    {
        return 'park';
    }
}
