<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GradeRecord extends Model
{
    use HasUuids;

    protected $fillable = [
        'academic_class_id',
        'student_id',
        'assignment_score',
        'mid_score',
        'final_score',
        'final_numeric',
        'final_letter',
        'status',
        'finalized_at',
    ];

    protected function casts(): array
    {
        return [
            'assignment_score' => 'decimal:2',
            'mid_score' => 'decimal:2',
            'final_score' => 'decimal:2',
            'final_numeric' => 'decimal:2',
            'finalized_at' => 'datetime',
        ];
    }

    public function academicClass(): BelongsTo
    {
        return $this->belongsTo(AcademicClass::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
