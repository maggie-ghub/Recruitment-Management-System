<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VacancyStatusHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'vacancy_id',
        'changed_by',
        'from_status',
        'to_status',
        'notes',
    ];

    public function vacancy()
    {
        return $this->belongsTo(Vacancy::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
