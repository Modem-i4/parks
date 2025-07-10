<?php

namespace App\Models;

use App\Enums\MediaType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class InfrastructureType extends Model
{
    use HasFactory;

    protected $table = 'infrastructure_type';

    protected $fillable = [
        'name',
        'description',
    ];

    public $timestamps = false;

    public function infrastructures()
    {
        return $this->hasMany(Infrastructure::class);
    }

	public function icon(): MorphOne
	{
		return $this->morphOne(Media::class, 'model')->ofType(MediaType::ICON->value)->with('mediaFile')->orderBy('order');
	}
}
