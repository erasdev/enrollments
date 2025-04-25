<?php

namespace ErasDev\Enrollments\Enums;

enum EnrollmentRuleType: string
{
    case AGE_REQUIREMENT = 'age_requirement';
    case PREREQUISITE = 'prerequisite';
    case CAPACITY = 'capacity';
    case WAITLIST = 'waitlist';
    case PAYMENT = 'payment';
    case CUSTOM = 'custom';

    /**
     * Get the display name for the rule type.
     */
    public function label(): string
    {
        return match ($this) {
            self::AGE_REQUIREMENT => 'Age Requirement',
            self::PREREQUISITE => 'Prerequisite',
            self::CAPACITY => 'Capacity',
            self::WAITLIST => 'Waitlist',
            self::PAYMENT => 'Payment',
            self::CUSTOM => 'Custom',
        };
    }

    /**
     * Get the description for the rule type.
     */
    public function description(): string
    {
        return match ($this) {
            self::AGE_REQUIREMENT => 'Enforces age restrictions for enrollment',
            self::PREREQUISITE => 'Requires completion of other courses or conditions',
            self::CAPACITY => 'Manages maximum enrollment capacity',
            self::WAITLIST => 'Manages waitlist functionality',
            self::PAYMENT => 'Handles payment requirements',
            self::CUSTOM => 'Custom rule implementation',
        };
    }

    /**
     * Convert the enum to a string.
     */
    public function toString(): string
    {
        return $this->value;
    }
}
