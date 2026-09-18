<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VacancyQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'vacancy_id',
        'question_text',
        'question_type',
        'options',
        'is_required',
        'order',
    ];

    protected $casts = [
        'options' => 'array',
        'is_required' => 'boolean',
        'order' => 'integer',
    ];

    public function vacancy()
    {
        return $this->belongsTo(Vacancy::class);
    }
}
