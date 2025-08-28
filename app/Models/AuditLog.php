<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;

class AuditLog extends Model
{
    protected $fillable = [
        'model_type','model_id','user_id','action','before','after'
    ];

    protected $casts = [
        'before' => 'array',
        'after'  => 'array',
    ];

    public function user() { return $this->belongsTo(User::class); }
    public function model() { return $this->morphTo(); }
}
