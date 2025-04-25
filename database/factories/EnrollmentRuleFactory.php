<?php

namespace ErasDev\Enrollments\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use ErasDev\Enrollments\Models\EnrollmentRule;

class EnrollmentRuleFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = EnrollmentRule::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        // Default to an "age_requirement" rule.
        return [
            'type' => 'age_requirement',
            'enabled' => true,
            // For polymorphic relationships, these can be set later via factory states or manually:
            'enrollable_type' => null,
            'enrollable_id'   => null,
        ];
    }
}
