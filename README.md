# A package for managing enrollments and enrollment rules

Make any model enrollable.

## Installation

You can install the package via composer:

```bash
composer require erasdev/enrollments
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="enrollments-migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="enrollments-config"
```

This is the contents of the published config file:

```php
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
    | Table Names
    |--------------------------------------------------------------------------
    |
    | Specify the table names for storing enrollment records and rules.
    |
    */
    'enrollments_table' => env('ENROLLMENTS_TABLE', 'enrollments'),
    'enrollees_table' => env('ENROLLEES_TABLE', 'enrollees'),
    'enrollment_rules_table' => env('ENROLLMENT_RULES_TABLE', 'enrollment_rules'),

    /*
    |--------------------------------------------------------------------------
    | Enrollment Model
    |--------------------------------------------------------------------------
    |
    | Specify the model class for the enrollment records.
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
    'enable_age_requirements' => true,

    /*
    |--------------------------------------------------------------------------
    | Enrollment Rule Bindings
    |--------------------------------------------------------------------------
    |
    | Define mappings for enrollment rule types to their implementation classes.
    | Use 'default' to use the package's default implementation.
    |
    */
    'rules' => [
        'types' => [
            'age_requirement' => 'default',
        ],
    ],
];
```


## Usage

### Making a Model Enrollable

To make a model enrollable, use the `HasEnrollments` trait:

```php
use ErasDev\Enrollments\Traits\HasEnrollments;

class Course extends Model
{
    use HasEnrollments;
    
    // Your model code...
}
```

If your model needs to handle age-related functionality, you can also use the `HasAge` trait to your enrollee model:

```php
use ErasDev\Enrollments\Traits\HasAge;

class User extends Authenticatable
{
    use HasAge;
    
    // Your model code...
}
```

### Enrolling Users

```php
// Enroll a user in a course
$course->enroll($user);

// Check if a user is enrolled
$course->isEnrolled($user);

// Unenroll a user
$course->unenroll($user);
```

### Enrollment Rules

The package supports various types of enrollment rules:

- Age Requirements

### Custom Rule Implementations

You can create custom enrollment rules by implementing the `EnrollmentRuleInterface`:

```php
use ErasDev\Enrollments\Rules\Contracts\EnrollmentRuleInterface;
use ErasDev\Enrollments\Models\EnrollmentRule;
use Illuminate\Database\Eloquent\Model;

class CustomAgeRequirementRule implements EnrollmentRuleInterface
{
    protected $rule;

    public function __construct(EnrollmentRule $rule)
    {
        $this->rule = $rule;
    }

    public function passes(Model $enrollable, Model $enrollee): bool
    {
        // Your custom validation logic here
        return false;
    }

    public function message(): string
    {
        return 'Custom rule class message.';
    }
}
```

Then register your custom rule in the config file:

```php
'rules' => [
    'types' => [
        'age_requirement' => \App\Rules\CustomAgeRequirementRule::class,
    ],
],
```

## Age Requirements

Age requirements allow you to restrict enrollment based on the age of the enrollee. This is useful for courses or programs that have age restrictions, such as children's programs or adult-only courses.

### Basic Usage

To implement age requirement rules, you need to:
1. Use the `HasAgeRequirement` trait on your enrollable model (e.g., Course)
2. Use the `HasAge` trait on your enrollee model (e.g., User)

```php
// In your Course model (enrollable)
use ErasDev\Enrollments\Traits\HasEnrollments;g
use ErasDev\Enrollments\Traits\HasAgeRequirement;

class Course extends Model
{
    use HasEnrollments;
    use HasAgeRequirement;  // Required for age requirement rules
    
    // Your model code...
}

// In your User model (enrollee)
use ErasDev\Enrollments\Traits\HasAge;

class User extends Authenticatable
{
    use HasAge;  // Required for age requirement rules to work
    
    // Your model code...
}
```

Then you can add age requirements to your enrollable model:

```php
use ErasDev\Enrollments\Models\EnrollmentRule;

// Add an age requirement to a course
EnrollmentRule::ageRequirement($course)
    ->minYears(18)
    ->maxYears(25)
    ->eligibilityDate(now())
    ->save();

// Check if a user is eligible to enroll
$course->isEligible($user);

// Try to enroll (will throw an exception if not eligible)
$course->enroll($user);
```

You can also use different age units:
```php
EnrollmentRule::ageRequirement($course)
    ->minMonths(10)
    ->maxYears(18)
    ->eligibilityDate(now())
    ->save();
```

The `HasAgeRequirement` trait provides additional methods for managing age requirements:

```php
// Check if a model has an age requirement
$course->hasAgeRequirement();

// Get the age requirement details
$requirement = $course->getAgeRequirement();
$minAge = $requirement->minimumAge();
$maxAge = $requirement->maximumAge();
$eligibilityDate = $requirement->eligibilityDate();

// Enable/disable the age requirement
$course->enableAgeRequirement();
$course->disableAgeRequirement();


// Edit an age requirement with different age units
$course->editAgeRequirement([
    'minimum_age' => 10,
    'minimum_age_unit' => 'months',
    'maximum_age' => 18,
    'maximum_age_unit' => 'years',
    'eligibility_date' => now()->addDays(30),
]);

// Delete the age requirement
$course->deleteAgeRequirement();
```


#### Using Helper Methods

```php
// Add an age requirement to a course
$course->addAgeRequirement([
    'minimum_age' => 18,
    'maximum_age' => 65,
    'eligibility_date' => now(),
]);

// Add an age requirement with different age units
$course->addAgeRequirement([
    'minimum_age' => 10,
    'minimum_age_unit' => 'months',
    'maximum_age' => 18,
    'maximum_age_unit' => 'years',
    'eligibility_date' => now(),
]);

// Check if a user is eligible
if ($course->isEligible($user)) {
    // User is eligible
}

// Edit an age requirement
$course->editAgeRequirement([
    'minimum_age' => 16,
    'maximum_age' => 70,
    'eligibility_date' => now()->addDays(30),
]);

// Edit an age requirement with different age units
$course->editAgeRequirement([
    'minimum_age' => 10,
    'minimum_age_unit' => 'months',
    'maximum_age' => 18,
    'maximum_age_unit' => 'years',
    'eligibility_date' => now()->addDays(30),
]);
```

#### Using Fluent Syntax

```php
use ErasDev\Enrollments\Models\EnrollmentRule;

// Add an age requirement to a course
EnrollmentRule::ageRequirement($course)
    ->minYears(18)
    ->maxYears(65)
    ->eligibilityDate(now())
    ->save();

// Add an age requirement with mixed units
EnrollmentRule::ageRequirement($course)
    ->minMonths(10)
    ->maxYears(18)
    ->eligibilityDate(now()->addDays(30))
    ->save();

// Check if a user is eligible
if ($course->isEligible($user)) {
    // User is eligible
}

// Try to enroll (will throw an exception if not eligible)
$course->enroll($user);
```

### Age Calculation

The `HasAge` trait is used to calculate the age of a user. By default, it looks for a `date_of_birth` column in the database. If your model uses a different column name for storing the date of birth, you can override the `getDateOfBirthAttribute` method:

```php
use ErasDev\Enrollments\Traits\HasAge;

class User extends Authenticatable
{
    use HasAge;
    
    // Override the method to use a different column
    public function getDateOfBirthAttribute(): ?Carbon
    {
        return $this->attributes['birth_date'] ? Carbon::parse($this->attributes['birth_date']) : null;
    }
}
```

This allows you to customize how the trait retrieves the date of birth, making it flexible for different database schemas.

### Age Units

Age requirements can be specified in different units:

- **Years**: The default unit for age requirements
- **Months**: Useful for programs targeting very young children

You can mix units when setting requirements, for example requiring a minimum age of 10 months and a maximum age of 18 years.

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Era](https://github.com/erasdev)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.