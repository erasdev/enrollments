<?php

use ErasDev\Enrollments\Traits\HasEnrollments;
use ErasDev\Enrollments\Traits\HasAgeRequirement;
use ErasDev\Enrollments\Models\EnrollmentRule;
use ErasDev\Enrollments\Traits\HasAge;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

// Create a test model that uses the HasEnrollments trait
class HasAgeRequirementTraitTestModel extends Model
{
    use HasEnrollments;
    use HasAgeRequirement;
    protected $table = 'test_enrollables';
    
    protected $fillable = ['name'];
}

// Create a test user model
class HasAgeRequirementTraitTestUser extends User
{
    use HasAge;
    protected $table = 'test_users';
    
    protected $fillable = ['name', 'email', 'password', 'date_of_birth'];
}

function HasAgeRequirementTraitTestSetup(){
    Schema::create('test_enrollables', function ($table) {
        $table->id();
        $table->string('name');
        $table->timestamps();
    });

    Schema::create('test_users', function ($table) {
        $table->id();
        $table->string('name');
        $table->string('email')->unique();
        $table->string('password');
        $table->date('date_of_birth');
        $table->rememberToken();
        $table->timestamps();
    });

    Schema::create('enrollment_rules', function ($table) {
        $table->id();
        $table->morphs('enrollable');
        $table->json('config')->nullable();
        $table->string('type');
        $table->boolean('enabled')->default(true);
        $table->timestamps();
    });

    Schema::create('enrollments', function ($table) {
        $table->id();
        $table->foreignId('enrollee_id')->constrained('test_users')->onDelete('cascade');
        $table->morphs('enrollable');
        $table->timestamps();
    });
    
    // Ensure the config is loaded
    config(['enrollments.enable_age_requirements' => true]);
}   

function HasAgeRequirementTraitTestTeardown(){
    Schema::dropIfExists('enrollments');
    Schema::dropIfExists('enrollment_rules');
    Schema::dropIfExists('test_enrollables');
    Schema::dropIfExists('test_users');
}

test('can check if a model has an age requirement', function () {
    HasAgeRequirementTraitTestSetup();
    
    // Create a test enrollable model
    $enrollable = HasAgeRequirementTraitTestModel::create(['name' => 'Test Course']);
    
    // Initially, the model should not have an age requirement
    expect($enrollable->hasAgeRequirement())->toBeFalse();
    
    // Add an age requirement
    EnrollmentRule::ageRequirement($enrollable)
        ->minYears(10)
        ->maxYears(18)
        ->save();
    
    // Now the model should have an age requirement
    expect($enrollable->hasAgeRequirement())->toBeTrue();
    
    HasAgeRequirementTraitTestTeardown();
});

test('can get age requirement details', function () {
    HasAgeRequirementTraitTestSetup();
    
    // Create a test enrollable model
    $enrollable = HasAgeRequirementTraitTestModel::create(['name' => 'Test Course']);
    
    // Add an age requirement
    $eligibilityDate = now()->addDays(30);
    EnrollmentRule::ageRequirement($enrollable)
        ->minYears(10)
        ->maxYears(18)
        ->eligibilityDate($eligibilityDate)
        ->save();
    
    // Get the age requirement details
    $requirement = $enrollable->getAgeRequirement();
    
    // Check the details
    expect($requirement->minimumAge())->toBe(10);
    expect($requirement->maximumAge())->toBe(18);
    expect($requirement->eligibilityDate()->format('Y-m-d H:i:s'))->toBe($eligibilityDate->format('Y-m-d H:i:s'));
    
    HasAgeRequirementTraitTestTeardown();
});

test('can enable and disable age requirement', function () {
    HasAgeRequirementTraitTestSetup();
    
    // Create a test enrollable model
    $enrollable = HasAgeRequirementTraitTestModel::create(['name' => 'Test Course']);
    
    // Add an age requirement
    EnrollmentRule::ageRequirement($enrollable)
        ->minYears(10)
        ->maxYears(18)
        ->save();
    
    // Initially, the age requirement should be enabled
    expect($enrollable->getAgeRequirement()->isEnabled())->toBeTrue();
    
    // Disable the age requirement
    $enrollable->disableAgeRequirement();
    expect($enrollable->getAgeRequirement()->isEnabled())->toBeFalse();
    
    // Enable the age requirement
    $enrollable->enableAgeRequirement();
    expect($enrollable->getAgeRequirement()->isEnabled())->toBeTrue();
    
    HasAgeRequirementTraitTestTeardown();
});

test('can delete age requirement', function () {
    HasAgeRequirementTraitTestSetup();
    
    // Create a test enrollable model
    $enrollable = HasAgeRequirementTraitTestModel::create(['name' => 'Test Course']);
    
    // Add an age requirement
    EnrollmentRule::ageRequirement($enrollable)
        ->minYears(10)
        ->maxYears(18)
        ->save();
    
    // Initially, the model should have an age requirement
    expect($enrollable->hasAgeRequirement())->toBeTrue();
    
    // Delete the age requirement
    $enrollable->deleteAgeRequirement();
    
    // Now the model should not have an age requirement
    expect($enrollable->hasAgeRequirement())->toBeFalse();
    
    HasAgeRequirementTraitTestTeardown();
});

test('can edit age requirement with different age units', function () {
    HasAgeRequirementTraitTestSetup();
    
    // Create a test enrollable model
    $enrollable = HasAgeRequirementTraitTestModel::create(['name' => 'Test Course']);
    
    // Add an age requirement
    EnrollmentRule::ageRequirement($enrollable)
        ->minYears(10)
        ->maxYears(18)
        ->save();
    
    // Edit the age requirement with different age units
    $eligibilityDate = now()->addDays(30);
    $enrollable->editAgeRequirement([
        'minimum_age' => 10,
        'minimum_age_unit' => 'months',
        'maximum_age' => 18,
        'maximum_age_unit' => 'years',
        'eligibility_date' => $eligibilityDate,
    ]);
    
    // Get the updated age requirement details
    $requirement = $enrollable->getAgeRequirement();
    
    // Check the details
    expect($requirement->minimumAge())->toBe(10);
    expect($requirement->maximumAge())->toBe(18);
    expect($requirement->eligibilityDate()->format('Y-m-d H:i:s'))->toBe($eligibilityDate->format('Y-m-d H:i:s'));
    
    // Check the age units
    $rule = $enrollable->getAgeRequirement()->getRule();
    expect($rule->config['minimum_age_unit'])->toBe('months');
    expect($rule->config['maximum_age_unit'])->toBe('years');
    
    HasAgeRequirementTraitTestTeardown();
});

test('expect an exception when trying to edit a non-existent age requirement', function () {
    HasAgeRequirementTraitTestSetup();
    
    // Create a test enrollable model
    $enrollable = HasAgeRequirementTraitTestModel::create(['name' => 'Test Course']);
    
    // Try to edit a non-existent age requirement
    expect(function () use ($enrollable) {
        $enrollable->editAgeRequirement([
            'minimum_age' => 10,
            'maximum_age' => 18,
        ]);
    })->toThrow(Exception::class, 'Age requirement rule does not exist for this model.');
    
    HasAgeRequirementTraitTestTeardown();
});

test('expect an exception when trying to enable a non-existent age requirement', function () {
    HasAgeRequirementTraitTestSetup();
    
    // Create a test enrollable model
    $enrollable = HasAgeRequirementTraitTestModel::create(['name' => 'Test Course']);
    
    // Try to enable a non-existent age requirement
    expect(function () use ($enrollable) {
        $enrollable->enableAgeRequirement();
    })->toThrow(Exception::class, 'Age requirement rule does not exist for this model.');
    
    HasAgeRequirementTraitTestTeardown();
});

test('expect an exception when trying to disable a non-existent age requirement', function () {
    HasAgeRequirementTraitTestSetup();
    
    // Create a test enrollable model
    $enrollable = HasAgeRequirementTraitTestModel::create(['name' => 'Test Course']);
    
    // Try to disable a non-existent age requirement
    expect(function () use ($enrollable) {
        $enrollable->disableAgeRequirement();
    })->toThrow(Exception::class, 'Age requirement rule does not exist for this model.');
    
    HasAgeRequirementTraitTestTeardown();
});

test('can add age requirement with array parameters', function () {
    HasAgeRequirementTraitTestSetup();
    
    // Create a test enrollable model
    $enrollable = HasAgeRequirementTraitTestModel::create(['name' => 'Test Course']);
    
    // Add an age requirement with array parameters
    $eligibilityDate = now()->addDays(30);
    $enrollable->addAgeRequirement([
        'minimum_age' => 10,
        'minimum_age_unit' => 'months',
        'maximum_age' => 18,
        'maximum_age_unit' => 'years',
        'eligibility_date' => $eligibilityDate,
    ])->save();
    
    // Get the age requirement details
    $requirement = $enrollable->getAgeRequirement();
    
    // Check the details
    expect($requirement->minimumAge())->toBe(10);
    expect($requirement->maximumAge())->toBe(18);
    expect($requirement->eligibilityDate()->format('Y-m-d H:i:s'))->toBe($eligibilityDate->format('Y-m-d H:i:s'));
    
    // Check the age units
    $rule = $enrollable->getAgeRequirement()->getRule();
    expect($rule->config['minimum_age_unit'])->toBe('months');
    expect($rule->config['maximum_age_unit'])->toBe('years');
    
    HasAgeRequirementTraitTestTeardown();
});

test('expect an exception when required parameters are missing', function () {
    HasAgeRequirementTraitTestSetup();
    
    // Create a test enrollable model
    $enrollable = HasAgeRequirementTraitTestModel::create(['name' => 'Test Course']);
    
    // Try to add an age requirement with missing parameters
    expect(function () use ($enrollable) {
        $enrollable->addAgeRequirement([
            'minimum_age' => 10,
            // Missing maximum_age and eligibility_date
        ]);
    })->toThrow(Exception::class, 'Missing required parameters: minimum_age, maximum_age, and eligibility_date are required.');
    
    HasAgeRequirementTraitTestTeardown();
}); 