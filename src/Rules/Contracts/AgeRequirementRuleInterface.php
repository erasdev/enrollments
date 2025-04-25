<?php

namespace ErasDev\Enrollments\Rules\Contracts;

use Carbon\Carbon;

interface AgeRequirementRuleInterface extends EnrollmentRuleInterface
{
    /**
     * Get the minimum age required for enrollment.
     */
    public function getMinimumAgeAttribute(): int;

    /**
     * Get the maximum age allowed for enrollment.
     */
    public function getMaximumAgeAttribute(): int;

    /**
     * Get the eligibility date for enrollment.
     */
    public function getEligibilityDateAttribute(): Carbon;
}
