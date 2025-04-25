<?php

namespace ErasDev\Enrollments\Rules;

use Carbon\Carbon;
use ErasDev\Enrollments\Models\EnrollmentRule;
use ErasDev\Enrollments\Rules\Contracts\AgeRequirementRuleInterface;
use ErasDev\Enrollments\Rules\Contracts\EnrollmentRuleInterface;
use Illuminate\Database\Eloquent\Model;

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
     * @return void
     */
    public function __construct(EnrollmentRule $rule)
    {
        $this->rule = $rule;
    }

    /**
     * Check if the user passes the age requirement.
     *
     * @param  Model  $enrollable  The enrollable being enrolled in
     * @param  Model  $enrollee  The enrollee attempting to enroll
     */
    public function passes(Model $enrollable, Model $enrollee): bool
    {
        if (! config('enrollments.enable_age_requirements')) {
            return true;
        }

        if (! $this->rule->isEnabled()) {
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
     */
    public function message(): string
    {
        return 'Applicant does not meet the age requirement.';
    }

    /**
     * Calculate the age of the user.
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
     */
    public function getMinimumAgeAttribute(): int
    {
        return $this->minimumAge;
    }

    /**
     * Get the maximum age requirement.
     */
    public function getMaximumAgeAttribute(): int
    {
        return $this->maximumAge;
    }

    /**
     * Get the eligibility date.
     */
    public function getEligibilityDateAttribute(): Carbon
    {
        return $this->eligibilityDate;
    }
}
