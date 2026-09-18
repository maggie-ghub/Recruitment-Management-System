<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'original_filename',
        'stored_path',
        'mime_type',
        'file_size',
        'is_primary_cv',
    ];

    protected $casts = [
        'is_primary_cv' => 'boolean',
        'file_size' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
