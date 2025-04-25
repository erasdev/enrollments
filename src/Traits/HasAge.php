<?php

namespace ErasDev\Enrollments\Traits;

use Carbon\Carbon;

trait HasAge
{
    /**
     * Get the models date of birth.
     * Assumes the model has a date_of_birth attribute.
     * Parent model can reimplement this method to customize the date of birth attribute.
     */
    public function getDateOfBirthAttribute(): ?Carbon
    {
        return $this->attributes['date_of_birth'] ? Carbon::parse($this->attributes['date_of_birth']) : null;
    }
}
