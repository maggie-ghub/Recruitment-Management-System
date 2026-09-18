<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkExperience extends Model
{
    use HasFactory;

    protected $table = 'work_experiences';

    protected $fillable = [
        'user_id',
        'company_name',
        'job_title',
        'start_date',
        'end_date',
        'is_current',
        'responsibilities',
    ];

    protected $casts = [
        'start_date' => 'date:Y-m-d',
        'end_date' => 'date:Y-m-d',
        'is_current' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
