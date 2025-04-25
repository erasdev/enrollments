<?php

namespace ErasDev\Enrollments\Traits;

use ErasDev\Enrollments\Accessors\AgeRequirementAccessor;
use ErasDev\Enrollments\Builders\AgeRequirementBuilder;
use ErasDev\Enrollments\Models\EnrollmentRule;

trait HasAgeRequirement
{
    use HasEnrollments;

    /**
     * Get the age requirement rule for this model.
     */
    public function getAgeRequirement(): AgeRequirementAccessor
    {
        return new AgeRequirementAccessor($this);
    }

    /**
     * Check if this model has an age requirement rule.
     */
    public function hasAgeRequirement(): bool
    {
        return $this->getAgeRequirement()->getRule() !== null;
    }

    /**
     * Add an age requirement to this model.
     *
     * @param  array  $config  Configuration array with keys:
     *                         - minimum_age: int (required)
     *                         - maximum_age: int (required)
     *                         - eligibility_date: \DateTime (required)
     *                         - minimum_age_unit: string (optional, default: 'years')
     *                         - maximum_age_unit: string (optional, default: 'years')
     */
    public function addAgeRequirement(array $config): AgeRequirementBuilder
    {
        if ($this->hasAgeRequirement()) {
            throw new \Exception('Age requirement rule already exists for this model.');
        }

        // Validate required parameters
        if (! isset($config['minimum_age']) || ! isset($config['maximum_age']) || ! isset($config['eligibility_date'])) {
            throw new \Exception('Missing required parameters: minimum_age, maximum_age, and eligibility_date are required.');
        }

        // Set default age units if not provided
        if (! isset($config['minimum_age_unit'])) {
            $config['minimum_age_unit'] = 'years';
        }

        if (! isset($config['maximum_age_unit'])) {
            $config['maximum_age_unit'] = 'years';
        }

        return new AgeRequirementBuilder($this, $config);
    }

    /**
     * Disable (without deleting) the age requirement rule for this model.
     */
    public function disableAgeRequirement(): void
    {

        $rule = $this->getAgeRequirement()->getRule();
        if (! $rule) {
            throw new \Exception('Age requirement rule does not exist for this model.');
        }

        $rule->enabled = false;
        $rule->save();
    }

    /**
     * Enable the age requirement for this model.
     */
    public function enableAgeRequirement(): void
    {

        $rule = $this->getAgeRequirement()->getRule();
        if (! $rule) {
            throw new \Exception('Age requirement rule does not exist for this model.');
        }

        $rule->enabled = true;
        $rule->save();
    }

    /**
     * Delete the age requirement rule for this model.
     *
     * @return bool
     */
    public function deleteAgeRequirement()
    {
        // Look for an existing age requirement rule.
        $rule = $this->getAgeRequirement()->getRule();

        // If the rule exists, delete it.
        if ($rule) {
            $rule->delete();
        }

        return $rule;
    }

    /**
     * Edit the age requirement for this model.
     *
     * @return \ErasDev\Enrollments\Models\EnrollmentRule
     */
    public function editAgeRequirement(array $config)
    {
        $rule = $this->getAgeRequirement()->getRule();
        if (! $rule) {
            throw new \Exception('Age requirement rule does not exist for this model.');
        }

        $currentConfig = $rule->config;

        // Handle minimum age and its unit
        if (isset($config['minimum_age'])) {
            $currentConfig['minimum_age'] = $config['minimum_age'];

            // Set the minimum age unit if provided, otherwise keep the existing one or default to 'years'
            if (isset($config['minimum_age_unit'])) {
                $currentConfig['minimum_age_unit'] = $config['minimum_age_unit'];
            } elseif (! isset($currentConfig['minimum_age_unit'])) {
                $currentConfig['minimum_age_unit'] = 'years';
            }
        }

        // Handle maximum age and its unit
        if (isset($config['maximum_age'])) {
            $currentConfig['maximum_age'] = $config['maximum_age'];

            // Set the maximum age unit if provided, otherwise keep the existing one or default to 'years'
            if (isset($config['maximum_age_unit'])) {
                $currentConfig['maximum_age_unit'] = $config['maximum_age_unit'];
            } elseif (! isset($currentConfig['maximum_age_unit'])) {
                $currentConfig['maximum_age_unit'] = 'years';
            }
        }

        // Handle eligibility date
        if (isset($config['eligibility_date'])) {
            $currentConfig['eligibility_date'] = $config['eligibility_date'];
        }

        $rule->config = $currentConfig;
        $rule->save();

        return $rule;
    }
}
