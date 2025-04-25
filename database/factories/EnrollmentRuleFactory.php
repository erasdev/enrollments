<?php

namespace ErasDev\Enrollments\Database\Factories;

use ErasDev\Enrollments\Models\EnrollmentRule;
use Illuminate\Database\Eloquent\Factories\Factory;

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
     */
    public function definition(): array
    {
        // Default to an "age_requirement" rule.
        return [
            'type' => 'age_requirement',
            'enabled' => true,
            // For polymorphic relationships, these can be set later via factory states or manually:
            'enrollable_type' => null,
            'enrollable_id' => null,
        ];
    }
}
