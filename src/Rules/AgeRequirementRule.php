<?php

namespace ErasDev\Enrollments\Rules;

use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Database\Eloquent\Model;
use ErasDev\Enrollments\Rules\Contracts\AgeRequirementRuleInterface;
use ErasDev\Enrollments\Rules\Contracts\EnrollmentRuleInterface;
use ErasDev\Enrollments\Models\EnrollmentRule;

class AgeRequirementRule implements AgeRequirementRuleInterface, EnrollmentRuleInterface
{
    /**
     * The rule model instance.
     *
     * @var EnrollmentRule
     */
    protected $rule;

    /**
     * The minimum age requirement.
     *
     * @var int|null
     */
    protected $minimumAge;

    /**
     * The maximum age requirement.
     *
     * @var int|null
     */
    protected $maximumAge;

    /**
     * The eligibility date as a Carbon instance.
     *
     * @var \Carbon\Carbon|null
     */
    protected $eligibilityDate;

    /**
     * Create a new age requirement rule instance.
     *
     * @param EnrollmentRule $rule
     * @return void
     */
    public function __construct(EnrollmentRule $rule)
    {
        $this->rule = $rule;
    }

    /**
     * Check if the user passes the age requirement.
     *
     * @param Model $enrollable The enrollable being enrolled in
     * @param Model $enrollee The enrollee attempting to enroll
     * @return bool
     */
    public function passes(Model $enrollable, Model $enrollee): bool
    {
        if (!config('enrollments.enable_age_requirements')) {
            return true;
        }

        if (!$this->rule->isEnabled()) {
            return true;
        }

        $config = $this->rule->config();
        $minimumAge = $config['minimum_age'];
        $maximumAge = $config['maximum_age'];
        $eligibilityDate = new \DateTime($config['eligibility_date']);
        
        // Get the age units with fallbacks
        $minimumAgeUnit = $config['minimum_age_unit'] ?? 'years';
        $maximumAgeUnit = $config['maximum_age_unit'] ?? 'years';

        // Convert the date of birth to a DateTime object
        $dateOfBirth = new \DateTime($enrollee->date_of_birth);

        // Calculate age in the respective units
        $userAgeInMinimumUnit = $this->calculateAge($dateOfBirth, $eligibilityDate, $minimumAgeUnit);
        $userAgeInMaximumUnit = $this->calculateAge($dateOfBirth, $eligibilityDate, $maximumAgeUnit);

        // Check if the user meets both minimum and maximum age requirements
        return $userAgeInMinimumUnit >= $minimumAge && $userAgeInMaximumUnit <= $maximumAge;
    }

    /**
     * Get the error message for when the rule fails.
     *
     * @return string
     */
    public function message(): string
    {
        return 'Applicant does not meet the age requirement.';
    }

    /**
     * Calculate the age of the user.
     *
     * @param \DateTime $dateOfBirth
     * @param \DateTime $eligibilityDate
     * @param string $unit
     * @return int
     */
    public function calculateAge(\DateTime $dateOfBirth, \DateTime $eligibilityDate, string $unit): int
    {
        $interval = $dateOfBirth->diff($eligibilityDate);

        return match ($unit) {
            'months' => ($interval->y * 12) + $interval->m,
            default => $interval->y,
        };
    }

    /**
     * Get the minimum age requirement.
     *
     * @return int
     */
    public function getMinimumAgeAttribute(): int
    {
        return $this->minimumAge;
    }

    /**
     * Get the maximum age requirement.
     *
     * @return int
     */
    public function getMaximumAgeAttribute(): int
    {
        return $this->maximumAge;
    }

    /**
     * Get the eligibility date.
     *
     * @return \Carbon\Carbon
     */
    public function getEligibilityDateAttribute(): Carbon
    {
        return $this->eligibilityDate;
    }
}
