<?php

namespace ErasDev\Enrollments\Rules\Contracts;

use Carbon\Carbon;

interface AgeRequirementRuleInterface extends EnrollmentRuleInterface
{
    /**
     * Get the minimum age required for enrollment.
     *
     * @return int
     */
    public function getMinimumAgeAttribute(): int;
    
    /**
     * Get the maximum age allowed for enrollment.
     *
     * @return int
     */
    public function getMaximumAgeAttribute(): int;
    
    /**
     * Get the eligibility date for enrollment.
     *
     * @return \Carbon\Carbon
     */
    public function getEligibilityDateAttribute(): Carbon;
}