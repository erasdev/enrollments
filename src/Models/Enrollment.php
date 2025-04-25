<?php

namespace ErasDev\Enrollments\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

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
