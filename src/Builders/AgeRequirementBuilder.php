<?php

namespace ErasDev\Enrollments\Builders;

use Carbon\Carbon;
use ErasDev\Enrollments\Enums\EnrollmentRuleType;
use ErasDev\Enrollments\Models\EnrollmentRule;

class AgeRequirementBuilder
{
    /**
     * The model instance.
     *
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $model;

    /**
     * The configuration array.
     *
     * @var array
     */
    protected $config;

    /**
     * The saved rule instance.
     *
     * @var \ErasDev\Enrollments\Models\EnrollmentRule|null
     */
    protected $rule;

    /**
     * Create a new age requirement builder instance.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param array $config
     * @return void
     */
    public function __construct($model, array $config = [])
    {
        $this->model = $model;
        $this->config = $config;
    }


    

    /**
     * Set the minimum age in years.
     *
     * @param int $years
     * @return $this
     */
    public function minYears(int $years)
    {
        $this->config['minimum_age'] = $years;
        $this->config['minimum_age_unit'] = 'years';
        return $this;
    }

    /**
     * Set the maximum age in years.
     *
     * @param int $years
     * @return $this
     */
    public function maxYears(int $years)
    {
        $this->config['maximum_age'] = $years;
        $this->config['maximum_age_unit'] = 'years';
        return $this;
    }

    /** 
     * Set the minimum age in months.
     *
     * @param int $months
     * @return $this
     */
    public function minMonths(int $months)
    {
        $this->config['minimum_age'] = $months;
        $this->config['minimum_age_unit'] = 'months';
        return $this;
    }

    /**
     * Set the maximum age in months.
     *
     * @param int $months
     * @return $this
     */ 
    public function maxMonths(int $months)
    {
        $this->config['maximum_age'] = $months;
        $this->config['maximum_age_unit'] = 'months';
        return $this;
    }

    /**
     * Set the eligibility date.
     *
     * @param \DateTime|null $date
     * @return $this
     */
    public function eligibilityDate(\DateTime $date = null)
    {
        $this->config['eligibility_date'] = $date ? $date->format('Y-m-d H:i:s') : now()->format('Y-m-d H:i:s');
        return $this;
    }

    /**
     * Set the age unit to years.
     *
     * @return $this
     */
    public function years()
    {
        if (isset($this->config['minimum_age']) && !isset($this->config['minimum_age_unit'])) {
            $this->config['minimum_age_unit'] = 'years';
        }
        
        if (isset($this->config['maximum_age']) && !isset($this->config['maximum_age_unit'])) {
            $this->config['maximum_age_unit'] = 'years';
        }
        
        return $this;
    }

    /**
     * Set the age unit to months.
     *
     * @return $this
     */
    public function months()
    {
        if (isset($this->config['minimum_age']) && !isset($this->config['minimum_age_unit'])) {
            $this->config['minimum_age_unit'] = 'months';
        }
        
        if (isset($this->config['maximum_age']) && !isset($this->config['maximum_age_unit'])) {
            $this->config['maximum_age_unit'] = 'months';
        }
        
        return $this;
    }

    /**
     * Save the age requirement rule.
     *
     * @return \ErasDev\Enrollments\Models\EnrollmentRule
     */
    public function save()
    {
        if ($this->rule) {
            return $this->rule;
        }

        // Set default eligibility date if not set
        if (!isset($this->config['eligibility_date'])) {
            $this->config['eligibility_date'] = now()->format('Y-m-d H:i:s');
        }

        // Set default minimum age if not set
        if (!isset($this->config['minimum_age'])) {
            $this->config['minimum_age'] = 0;
            $this->config['minimum_age_unit'] = 'years';
        }
        
        // Set default age units if not set
        if (isset($this->config['minimum_age']) && !isset($this->config['minimum_age_unit'])) {
            $this->config['minimum_age_unit'] = 'years';
        }
        
        if (isset($this->config['maximum_age']) && !isset($this->config['maximum_age_unit'])) {
            $this->config['maximum_age_unit'] = 'years';
        }

        $this->rule = EnrollmentRule::create([
            'enrollable_type' => get_class($this->model),
            'enrollable_id' => $this->model->id,
            'type' => EnrollmentRuleType::AGE_REQUIREMENT->value,
            'config' => $this->config,
            'enabled' => true,
        ]);

        return $this->rule;
    }
}
