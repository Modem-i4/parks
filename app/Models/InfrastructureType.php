<?php

namespace App\Models;

use App\Enums\MediaType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Concerns\LogsChanges;

class InfrastructureType extends Model
{
    use LogsChanges;
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

	public function icon()
	{
		return $this->morphOne(Media::class, 'model')->ofType(MediaType::ICON->value)->with('mediaFile')->orderBy('order');
	}

	public function media()
	{
		return $this->morphMany(Media::class, 'model')->ofType(MediaType::IMAGE->value)->with('mediaFile')->orderBy('order');
	}
}
