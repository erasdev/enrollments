<?php

namespace ErasDev\Enrollments\Rules\Contracts;

use Illuminate\Database\Eloquent\Model;

interface EnrollmentRuleInterface
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  Model  $enrollable  The enrollable being enrolled in
     * @param  Model  $enrollee  The enrollee attempting to enroll
     */
    public function passes(Model $enrollable, Model $enrollee): bool;

    /**
     * Get the validation error message.
     */
    public function message(): string;
}
