<?php

use ErasDev\Enrollments\Models\EnrollmentRule;
use ErasDev\Enrollments\Enums\EnrollmentRuleType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

// Create a test model
class AgeRequirementBuilderTestEnrollableModel extends Model
{
    protected $table = 'test_enrollables';
    protected $fillable = ['name'];
}

function AgeRequirementBuilderTestSetup(){
     // Create the necessary tables
     Schema::create('test_enrollables', function ($table) {
        $table->id();
        $table->string('name');
        $table->timestamps();
    });
    
    // Create the enrollment_rules table
    Schema::create('enrollment_rules', function ($table) {
        $table->id();
        $table->morphs('enrollable');
        $table->json('config')->nullable();
        $table->string('type');
        $table->boolean('enabled')->default(true);
        $table->timestamps();
    });
}

function AgeRequirementBuilderTestTeardown(){
    Schema::dropIfExists('enrollment_rules');
    Schema::dropIfExists('test_enrollables');
}

test('age requirement builder can be used with fluent interface', function () {
    AgeRequirementBuilderTestSetup();
    
    // Create a test enrollable model
    $enrollable = AgeRequirementBuilderTestEnrollableModel::create(['name' => 'Test Course']);
    
    // Use the fluent builder to create an age requirement rule
    $rule = EnrollmentRule::ageRequirement($enrollable)
        ->minYears(10)
        ->maxYears(18)
        ->save();
    
    // Verify the rule was created correctly
    expect($rule)->not->toBeNull();
    expect($rule->type())->toBe(EnrollmentRuleType::AGE_REQUIREMENT->value);
    expect($rule->enrollable_type)->toBe(get_class($enrollable));
    expect($rule->enrollable_id)->toBe($enrollable->id);
    expect($rule->enabled)->toBeTrue();

    AgeRequirementBuilderTestTeardown();
}); 

test('age requirement builder can set eligibility date', function () {
    AgeRequirementBuilderTestSetup();
    
    // Create a test enrollable model
    $enrollable = AgeRequirementBuilderTestEnrollableModel::create(['name' => 'Test Course']);
    
    // Use the fluent builder to create an age requirement rule
    $rule = EnrollmentRule::ageRequirement($enrollable)
        ->minYears(10)
        ->maxYears(18)
        ->eligibilityDate(now()->subYears(10))
        ->save();
    
    // Verify the rule was created correctly
    expect($rule)->not->toBeNull();
    expect($rule->type())->toBe(EnrollmentRuleType::AGE_REQUIREMENT->value);
    expect($rule->enrollable_type)->toBe(get_class($enrollable));
    expect($rule->enrollable_id)->toBe($enrollable->id);
    expect($rule->enabled)->toBeTrue();
    expect($rule->config())->toHaveKey('eligibility_date', now()->subYears(10));

    AgeRequirementBuilderTestTeardown();
});

test('age requirement builder uses now as default eligibility date', function () {
    AgeRequirementBuilderTestSetup();
    
    // Create a test enrollable model
    $enrollable = AgeRequirementBuilderTestEnrollableModel::create(['name' => 'Test Course']);
    
    // Use the fluent builder to create an age requirement rule
    $rule = EnrollmentRule::ageRequirement($enrollable)
        ->minYears(10)
        ->maxYears(18)
        ->save();
    
    // Verify the rule was created correctly
    expect($rule->config())->toHaveKey('eligibility_date', now());

    AgeRequirementBuilderTestTeardown();
}); 

test('age requirement builder can set age unit to months', function () {
    AgeRequirementBuilderTestSetup();
    
    // Create a test enrollable model
    $enrollable = AgeRequirementBuilderTestEnrollableModel::create(['name' => 'Test Course']);
    
    // Use the fluent builder to create an age requirement rule
    $rule = EnrollmentRule::ageRequirement($enrollable)
        ->minMonths(10)
        ->maxMonths(18)
        ->save();
    
    // Verify the rule was created correctly
    expect($rule->config())->toHaveKey('minimum_age_unit', 'months');
    expect($rule->config())->toHaveKey('maximum_age_unit', 'months');

    AgeRequirementBuilderTestTeardown();
}); 

test('age requirement builder can set age unit to years', function () {
    AgeRequirementBuilderTestSetup();
    
    // Create a test enrollable model
    $enrollable = AgeRequirementBuilderTestEnrollableModel::create(['name' => 'Test Course']);

    // Use the fluent builder to create an age requirement rule
    $rule = EnrollmentRule::ageRequirement($enrollable)
        ->minYears(10)
        ->maxYears(18)
        ->save();

    // Verify the rule was created correctly
    expect($rule->config())->toHaveKey('minimum_age_unit', 'years');
    expect($rule->config())->toHaveKey('maximum_age_unit', 'years');

    AgeRequirementBuilderTestTeardown();
}); 

test('age requirement builder uses 0 as default minimum age', function () {
    AgeRequirementBuilderTestSetup();
    
    // Create a test enrollable model
    $enrollable = AgeRequirementBuilderTestEnrollableModel::create(['name' => 'Test Course']);
    
    // Use the fluent builder to create an age requirement rule
    $rule = EnrollmentRule::ageRequirement($enrollable)
        ->maxYears(25)
        ->save();

    // Verify the rule was created correctly
    expect($rule->config())->toHaveKey('minimum_age', 0);

    AgeRequirementBuilderTestTeardown();
});

test('can mix age units', function () {
    AgeRequirementBuilderTestSetup();
    
    // Create a test enrollable model
    $enrollable = AgeRequirementBuilderTestEnrollableModel::create(['name' => 'Test Course']);

    // Use the fluent builder to create an age requirement rule 
    $rule = EnrollmentRule::ageRequirement($enrollable)
        ->minMonths(10)
        ->maxYears(18)
        ->save();

    // Verify the rule was created correctly
    expect($rule->config())->toHaveKey('minimum_age_unit', 'months');
    expect($rule->config())->toHaveKey('maximum_age_unit', 'years');

    AgeRequirementBuilderTestTeardown(); 
});