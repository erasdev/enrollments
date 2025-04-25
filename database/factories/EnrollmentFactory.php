<?php

namespace ErasDev\Enrollments\Database\Factories;

use ErasDev\Enrollments\Models\Enrollment;
use Illuminate\Database\Eloquent\Factories\Factory;

class EnrollmentFactory extends Factory
{
    protected $model = Enrollment::class;

    public function definition(): array
    {
        return [
            'enrollee_id' => \App\Models\User::factory(),
            'enrollable_id' => \App\Models\Course::factory(),
            'enrollment_date' => $this->faker->dateTime(),
            'status' => $this->faker->randomElement(['pending', 'active', 'completed', 'cancelled']),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
}
