<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KrsDetail extends Model
{
    use HasUuids;

    protected $fillable = [
        'krs_header_id',
        'academic_class_id',
    ];

    public function header(): BelongsTo
    {
        return $this->belongsTo(KrsHeader::class, 'krs_header_id');
    }

    public function academicClass(): BelongsTo
    {
        return $this->belongsTo(AcademicClass::class);
    }
}
