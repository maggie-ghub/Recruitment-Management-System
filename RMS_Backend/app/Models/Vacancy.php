<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vacancy extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference_number',
        'subsidiary_id',
        'department_id',
        'created_by',
        'title',
        'openings',
        'openings_filled',
        'employment_type',
        'location',
        'grade_level',
        'reports_to',
        'salary_amount',
        'salary_currency',
        'closing_date',
        'description',
        'responsibilities',
        'qualifications',
        'experience_years',
        'skills_required',
        'application_requirements',
        'status',
    ];

    protected $casts = [
        'closing_date' => 'date',
        'skills_required' => 'array',
        'openings' => 'integer',
        'openings_filled' => 'integer',
        'salary_amount' => 'decimal:2',
    ];

    public function subsidiary()
    {
        return $this->belongsTo(Subsidiary::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function questions()
    {
        return $this->hasMany(VacancyQuestion::class)->orderBy('order');
    }

    public function statusHistories()
    {
        return $this->hasMany(VacancyStatusHistory::class)->latest();
    }

    /**
     * Check if vacancy accepts new applications (FR-VAC-007, BR-002)
     */
    public function isOpen(): bool
    {
        return $this->status === 'Published' && Carbon::now()->startOfDay()->lte($this->closing_date);
    }

    /**
     * Generate standard reference code e.g. TOR-TML-2026-001
     */
    public static function generateReference($subsidiaryCode): string
    {
        $year = date('Y');
        $count = self::whereYear('created_at', $year)->count() + 1;
        $num = str_pad($count, 3, '0', STR_PAD_LEFT);
        return "TOR-{$subsidiaryCode}-{$year}-{$num}";
    }
}
