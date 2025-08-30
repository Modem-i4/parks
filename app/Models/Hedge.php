<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\LogsChanges;

/**
 * Class Hedge
 * 
 * @property int $id
 * @property float|null $length_m
 * @property int|null $area
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Green $green
 *
 * @package App\Models
 */
class Hedge extends Model
{
    use LogsChanges;
    protected $table = 'hedges';

    protected $casts = [
        'id' => 'int',
        'length_m' => 'float',
		'area' => 'int'
    ];

    protected $fillable = [
        'id',
        'length_m',
        'hedge_row_id',
        'hedge_shape_id',
		'area'
    ];

    public function hedge_row()
    {
        return $this->belongsTo(HedgeRow::class);
    }

    public function hedge_shape()
    {
        return $this->belongsTo(HedgeShape::class);
    }

    public function green()
    {
        return $this->belongsTo(Green::class, 'id');
    }
}

