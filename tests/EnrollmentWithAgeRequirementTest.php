<?php

use ErasDev\Enrollments\Traits\HasEnrollments;
use ErasDev\Enrollments\Traits\HasAgeRequirement;
use ErasDev\Enrollments\Models\EnrollmentRule;
use ErasDev\Enrollments\Traits\HasAge;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Schema;
use ErasDev\Enrollments\Rules\Contracts\AgeRequirementRuleInterface;
use Carbon\Carbon;

// Create a test model that uses the HasEnrollments trait
class EnrollableWithAgeRequirementTestModel extends Model
{
    use HasEnrollments;
    use HasAgeRequirement;
    protected $table = 'test_enrollables';
    
    protected $fillable = ['name'];
}

// Create a test user model
class EnrollableWithAgeRequirementTestUser extends User
{
    use HasAgeRequirement;
    protected $table = 'test_users';
    
    protected $fillable = ['name', 'email', 'password', 'date_of_birth'];
}

class CustomAgeRequirementRule implements AgeRequirementRuleInterface
{
    protected $rule;

    public function __construct(EnrollmentRule $rule)
    {
        $this->rule = $rule;
    }

    public function passes(Model $enrollable, Model $enrollee): bool
    {
        return false;
    }

    public function getMinimumAgeAttribute(): int
    {
        return 0;
    }

    public function getMaximumAgeAttribute(): int
    {
        return 100;
    }

    public function getEligibilityDateAttribute(): Carbon
    {
        return now();
    }

    public function message(): string
    {
        return 'Custom rule class message.';
    }
}

function EnrollableWithAgeRequirementTestSetup(){
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
    
}   

function EnrollableWithAgeRequirementTestTeardown(){
    Schema::dropIfExists('enrollments');
    Schema::dropIfExists('enrollment_rules');
    Schema::dropIfExists('test_enrollables');
    Schema::dropIfExists('test_users');
}

test('can check if a user is eligible', function () {
    EnrollableWithAgeRequirementTestSetup();
   

   // Ensure the config is loaded
   config(['enrollments.enable_age_requirements' => true]);

   // Create a test enrollable model
   $enrollable = EnrollableWithAgeRequirementTestModel::create(['name' => 'Test Course']);

   // Create a test user
   $user = EnrollableWithAgeRequirementTestUser::create([
       'name' => 'Test User',
       'email' => 'test@example.com',
       'password' => bcrypt('password'),
       'date_of_birth' => now()->subYears(10)
   ]); 

   EnrollmentRule::ageRequirement($enrollable)
       ->minYears(9)
       ->maxYears(25)
       ->eligibilityDate(now())
       ->save();

   // The user should now be ineligible because they are younger than the minimum age of 18
   expect($enrollable->isEligible($user))->toBeTrue();

   EnrollableWithAgeRequirementTestTeardown();

});

test('can check if a user is not eligible', function () {
    EnrollableWithAgeRequirementTestSetup();

    // Ensure the config is loaded
    config(['enrollments.enable_age_requirements' => true]);

    // Create a test enrollable model
    $enrollable = EnrollableWithAgeRequirementTestModel::create(['name' => 'Test Course']);

    // Create a test user
    $user = EnrollableWithAgeRequirementTestUser::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
        'date_of_birth' => now()->subYears(10)
    ]); 

    // Add age requirement using the trait method
    EnrollmentRule::ageRequirement($enrollable)
        ->minYears(18)
        ->maxYears(25)
        ->eligibilityDate(now())
        ->save();

    // The user should now be ineligible because they are younger than the minimum age of 18
    expect($enrollable->isEligible($user))->toBeFalse();

    // Try to enroll the user and expect an exception with the correct message
    expect(function() use ($enrollable, $user) {
        $enrollable->enroll($user);
    })->toThrow(Exception::class, 'Applicant does not meet the age requirement.');

    EnrollableWithAgeRequirementTestTeardown();
});

test('can handle mixed age units', function () {
    EnrollableWithAgeRequirementTestSetup();

    // Ensure the config is loaded
    config(['enrollments.enable_age_requirements' => true]);

    // Create a test enrollable model
    $enrollable = EnrollableWithAgeRequirementTestModel::create(['name' => 'Test Course']);

    // Create a test user
    $user = EnrollableWithAgeRequirementTestUser::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
        'date_of_birth' => now()->subMonths(8)
    ]); 
    
    // Add age requirement using the trait method
    EnrollmentRule::ageRequirement($enrollable)
        ->minMonths(10)
        ->maxYears(18)
        ->eligibilityDate(now())
        ->save();

    // The user should now be ineligible because they are younger than the minimum age of 10 months
    expect($enrollable->isEligible($user))->toBeFalse();

    EnrollableWithAgeRequirementTestTeardown();
});                 


test('expect an exception when trying to set two age requirements on the same model', function () {
    EnrollableWithAgeRequirementTestSetup();

    // Ensure the config is loaded
    config(['enrollments.enable_age_requirements' => true]);

    // Create a test enrollable model
    $enrollable = EnrollableWithAgeRequirementTestModel::create(['name' => 'Test Course']);

    // Create a test user
    $user = EnrollableWithAgeRequirementTestUser::create([
        'name' => 'Test User',
        'email' => 'test@example.com',  
        'password' => bcrypt('password'),
        'date_of_birth' => now()->subYears(10)
    ]); 

    // Add age requirement using the trait method
    EnrollmentRule::ageRequirement($enrollable)
        ->minYears(18)
        ->maxYears(25)
        ->eligibilityDate(now())
        ->save();

    // Expect an exception when trying to set another age requirement
    expect(function () use ($enrollable) {
        EnrollmentRule::ageRequirement($enrollable)
            ->minMonths(10)
            ->maxYears(18)
            ->eligibilityDate(now())
            ->save();
    })->toThrow(Exception::class);

    EnrollableWithAgeRequirementTestTeardown(); 
});

test('can use custom age requirement rule', function () {
    EnrollableWithAgeRequirementTestSetup();

    // Ensure the config is loaded
    config(['enrollments.enable_age_requirements' => true]);
    config(['enrollments.rules.types.age_requirement' => CustomAgeRequirementRule::class]);

    // Create a test enrollable model
    $enrollable = EnrollableWithAgeRequirementTestModel::create(['name' => 'Test Course']);

    // Create a test user
    $user = EnrollableWithAgeRequirementTestUser::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
        'date_of_birth' => now()->subYears(20)
    ]); 
    
    // Add age requirement using the trait method
    EnrollmentRule::ageRequirement($enrollable)
        ->minYears(18)
        ->maxYears(25)
        ->eligibilityDate(now())
        ->save();

    // The user should now be ineligible because the custom rule returns false
    expect($enrollable->isEligible($user))->toBeFalse();

    // Try to enroll the user and expect an exception with the custom message
    expect(function() use ($enrollable, $user) {
        $enrollable->enroll($user);
    })->toThrow(Exception::class, 'Custom rule class message.');

    EnrollableWithAgeRequirementTestTeardown();
});         