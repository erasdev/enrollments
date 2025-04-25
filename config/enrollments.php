<?php

use ErasDev\Enrollments\Enums\EnrollmentRuleType;

return [

    /*
    |--------------------------------------------------------------------------
    | Custom Model Mapping
    |--------------------------------------------------------------------------
    |
    | You may override default internal models with your own implementations.
    |
    */
    'models' => [
        'enrollee' => \App\Models\User::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Enrollment Table
    |--------------------------------------------------------------------------
    |
    | Specify the table name for storing enrollment records.
    |
    */
    'enrollments_table' => env('ENROLLMENTS_TABLE', 'enrollments'),

    /*  
    |--------------------------------------------------------------------------
    | Enrollee Table
    |--------------------------------------------------------------------------
    |
    | Specify the table name for storing enrollee records.
    |
    */
    'enrollees_table' => env('ENROLLEES_TABLE', 'enrollees'),

    /*
    |--------------------------------------------------------------------------
    | Enrollment Rules Table
    |--------------------------------------------------------------------------
    |
    | Specify the table name for storing enrollment rules.
    */
    'enrollment_rules_table' => env('ENROLLMENT_RULES_TABLE', 'enrollment_rules'),

    /*
    |--------------------------------------------------------------------------
    | Enrollment Model
    |--------------------------------------------------------------------------
    |
    | Specify the model class for the enrollment records.
    | This model will be used for interacting with the enrollment table.
    |
    */
    'enrollment_model' => env('ENROLLMENTS_MODEL', 'ErasDev\\Enrollments\\Models\\Enrollment'),

    /*
    |--------------------------------------------------------------------------
    | Rule Toggles
    |--------------------------------------------------------------------------
    |
    | Globally enable or disable rules here.
    |
    */
    'enable_age_requirements' => true, //expects Enrollable model to have a minimum_age, maximum_age, and eligibility_date column

    /*
    |--------------------------------------------------------------------------
    | Enrollment Rule Bindings
    |--------------------------------------------------------------------------
    |
    | Define mappings for enrollment rule types to their implementation classes.
    | Use 'default' to use the package's default implementation, or specify your own class.
    | Custom rules can be an array of classes that will be checked in order.
    |
    | Example of overriding a default rule:
    | EnrollmentRuleType::AGE_REQUIREMENT->value => \App\Rules\CustomAgeRequirementRule::class,
    |
    */
    'rules' => [
        'types' => [
            EnrollmentRuleType::AGE_REQUIREMENT->value => 'default',
        ],
    ],
]; 