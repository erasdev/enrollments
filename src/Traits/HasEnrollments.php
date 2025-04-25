<?php

namespace ErasDev\Enrollments\Traits;

use ErasDev\Enrollments\Models\Enrollment;
use ErasDev\Enrollments\Models\EnrollmentRule;
use Illuminate\Support\Facades\Schema;

trait HasEnrollments
{
    /**
     * Get the polymorphic many-to-many relationship for enrollees.
     */
    public function enrollees()
    {
        return $this->morphToMany(
            config('enrollments.models.enrollee'),
            'enrollable',
            'enrollments',
            'enrollable_id',
            'enrollee_id'
        )->withTimestamps();
    }

    /**
     * Get the polymorphic one-to-many relationship for enrollment rules.
     */
    public function enrollmentRules()
    {
        return $this->morphMany(EnrollmentRule::class, 'enrollable');
    }

    /**
     * Get the enrollment records for this entity.
     */
    public function enrollments()
    {
        return $this->morphMany(Enrollment::class, 'enrollable');
    }

    /**
     * Enroll an enrollee into this entity.
     *
     * @param  mixed  $enrollee
     * @return mixed
     *
     * @throws \Exception
     */
    public function enroll($enrollee)
    {
        // Check if the enrollment_rules table exists
        if (Schema::hasTable('enrollment_rules')) {
            // Evaluate all enrollment rules
            foreach ($this->enrollmentRules as $rule) {
                $handler = $rule->resolveHandler();
                if (! $handler->passes($this, $enrollee)) {
                    throw new \Exception($handler->message());
                }
            }
        }

        // Enroll the user by creating the enrollment record
        $enrollment = $this->enrollments()->create([
            'enrollee_id' => $enrollee->id,
        ]);

        return $enrollment;
    }

    /**
     * Unenroll an enrollee from this entity.
     *
     * @param  mixed  $enrollee
     * @return void
     */
    public function unenroll($enrollee)
    {
        $this->enrollments()->where('enrollee_id', $enrollee->id)->delete();
    }

    /**
     * Check if an enrollee is enrolled in this entity.
     *
     * @param  mixed  $enrollee
     * @return bool
     */
    public function isEnrolled($enrollee)
    {
        return $this->enrollments()->where('enrollee_id', $enrollee->id)->exists();
    }

    /**
     * Check if an enrollee is eligible to enroll in this entity.
     *
     * @param  mixed  $enrollee
     * @return bool
     */
    public function isEligible($enrollee)
    {
        // Check if the enrollment_rules table exists
        if (! Schema::hasTable('enrollment_rules')) {
            return true;
        }

        foreach ($this->enrollmentRules()->get() as $rule) {
            $handler = $rule->resolveHandler();
            $passes = $handler->passes($this, $enrollee);
            if (! $passes) {
                return false;
            }
        }

        return true;
    }
}
