<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'subsidiary_id',
        'name',
        'status',
    ];

    public function subsidiary()
    {
        return $this->belongsTo(Subsidiary::class);
    }
}
