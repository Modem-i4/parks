<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\LogsChanges;

/**
 * Class Tree
 * 
 * @property int $id
 * @property float|null $height_m
 * @property float|null $trunk_diameter_cm
 * @property float|null $trunk_circumference_cm
 * @property float|null $tilt_degree
 * @property float|null $crown_condition_percent
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Green $green
 *
 * @package App\Models
 */
class Tree extends Model
{
    use LogsChanges;
    protected $table = 'trees';
    public $incrementing = false;

    protected $casts = [
        'id' => 'int',
        'height_m' => 'float',
        'trunk_circumference_cm' => 'float',
        'tilt_degree' => 'float',
        'crown_condition_percent' => 'float'
    ];

    protected $fillable = [
        'id',
        'height_m',
        'trunk_circumference_cm',
        'tilt_degree',
        'crown_condition_percent'
    ];

    protected $appends = ['trunk_diameter_cm'];

    public function green()
    {
        return $this->belongsTo(Green::class, 'id');
    }

    public function getTrunkDiameterCmAttribute(): ?float
    {
        if ($this->trunk_circumference_cm === null) {
            return null;
        }
        return round($this->trunk_circumference_cm / M_PI, 2);
    }
}
