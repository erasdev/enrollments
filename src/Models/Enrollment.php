<?php

namespace ErasDev\Enrollments\Models;

use ErasDev\Enrollments\Database\Factories\EnrollmentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Enrollment extends Model
{
    use HasFactory;

    protected $fillable = [
        'enrollee_id',
        'enrollable_id',
        'enrollable_type',
    ];

    /**
     * Get the enrollee that owns the enrollment.
     */
    public function enrollee()
    {
        return $this->belongsTo(config('enrollments.models.enrollee'));
    }

    /**
     * Get the enrollable entity (e.g., course, event, etc.).
     */
    public function enrollable(): MorphTo
    {
        return $this->morphTo();
    }

} 