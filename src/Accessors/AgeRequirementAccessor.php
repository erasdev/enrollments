<?php

namespace ErasDev\Enrollments\Accessors;

use ErasDev\Enrollments\Enums\EnrollmentRuleType;
use ErasDev\Enrollments\Models\EnrollmentRule;

class AgeRequirementAccessor
{
    /**
     * The model instance.
     *
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $model;

    /**
     * The rule instance.
     *
     * @var \ErasDev\Enrollments\Models\EnrollmentRule|null
     */
    protected $rule;

    /**
     * Create a new age requirement accessor instance.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @return void
     */
    public function __construct($model)
    {
        $this->model = $model;
        $this->rule = $this->findRule();
    }

    /**
     * Find the age requirement rule for the model.
     *
     * @return \ErasDev\Enrollments\Models\EnrollmentRule|null
     */
    protected function findRule()
    {

        return $this->model->enrollmentRules()
            ->where('type', EnrollmentRuleType::AGE_REQUIREMENT->value)
            ->first();
    }

    /**
     * Get the rule instance.
     *
     * @return \ErasDev\Enrollments\Models\EnrollmentRule|null
     */
    public function getRule()
    {
        return $this->rule;
    }

    /**
     * Get the rule ID.
     *
     * @return int|null
     */
    public function getId()
    {
        return $this->rule?->id ?? null;
    }


    /**
     * Get the minimum age requirement.
     *
     * @return int|null
     */
    public function minimumAge()
    {
        return $this->rule?->config['minimum_age'] ?? null;
    }

    /**
     * Get the maximum age requirement.
     *
     * @return int|null
     */
    public function maximumAge()
    {
        return $this->rule?->config['maximum_age'] ?? null;
    }

    /**
     * Get the eligibility date.
     *
     * @return \DateTime|null
     */
    public function eligibilityDate()
    {
        return $this->rule?->config['eligibility_date'] ? new \DateTime($this->rule->config['eligibility_date']) : null;
    }

    /**
     * Get the age unit.
     *
     * @return string|null
     */
    public function ageUnit()
    {
        return $this->rule?->config['age_unit'] ?? null;
    }

    /**
     * Check if the age requirement is enabled.
     *
     * @return bool
     */
    public function isEnabled()
    {
        if (!$this->exists()) {
            throw new \Exception('Age requirement rule does not exist for this model.');
        }

        return $this->rule->enabled;
    }

    /**
     * Check if the age requirement exists.
     *
     * @return bool
     */
    public function exists()
    {
        return $this->rule !== null;
    }

    /**
     * Return the rule instance when called directly.
     *
     * @return \ErasDev\Enrollments\Models\EnrollmentRule|null
     */
    public function __invoke()
    {
        return $this->rule;
    }
} 