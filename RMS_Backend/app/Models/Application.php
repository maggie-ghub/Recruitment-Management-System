<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    use HasFactory;

    protected $fillable = [
        'vacancy_id',
        'user_id',
        'cv_document_id',
        'cover_letter',
        'status',
        'notes',
    ];

    protected $casts = [
        'vacancy_id'     => 'integer',
        'user_id'        => 'integer',
        'cv_document_id' => 'integer',
    ];

    public function vacancy()
    {
        return $this->belongsTo(Vacancy::class);
    }

    public function applicant()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function cvDocument()
    {
        return $this->belongsTo(Document::class, 'cv_document_id');
    }
}
