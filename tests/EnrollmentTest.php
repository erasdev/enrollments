<?php

use ErasDev\Enrollments\Traits\HasEnrollments;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Schema;

// Create a test model that uses the HasEnrollments trait
class EnrollmentTestEnrollableModel extends Model
{
    use HasEnrollments;

    protected $table = 'test_enrollables';

    protected $fillable = ['name'];
}

// Create a test user model
class EnrollmentTestUser extends User
{
    protected $table = 'test_users';

    protected $fillable = ['name', 'email', 'password'];
}

function EnrollmentTestSetup()
{
    // Create the necessary tables
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

function EnrollmentTestTeardown()
{
    Schema::dropIfExists('enrollments');
    Schema::dropIfExists('enrollment_rules');
    Schema::dropIfExists('test_enrollables');
    Schema::dropIfExists('test_users');
}

test('can enroll and unenroll a user', function () {
    EnrollmentTestSetup();
    // Create a test enrollable model
    $enrollable = EnrollmentTestEnrollableModel::create(['name' => 'Test Course']);

    // Create a test user
    $user = EnrollmentTestUser::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
    ]);

    // Enroll the user
    $enrollable->enroll($user);

    // Assert the user is enrolled
    expect($enrollable->isEnrolled($user))->toBeTrue();

    // Unenroll the user
    $enrollable->unenroll($user);

    // Assert the user is not enrolled
    expect($enrollable->isEnrolled($user))->toBeFalse();

    EnrollmentTestTeardown();
});

test('can check if a user is eligible', function () {
    EnrollmentTestSetup();

    // Create a test enrollable model
    $enrollable = EnrollmentTestEnrollableModel::create(['name' => 'Test Course']);

    // Create a test user
    $user = EnrollmentTestUser::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
    ]);

    // By default, with no rules, the user should be eligible
    expect($enrollable->isEligible($user))->toBeTrue();

    EnrollmentTestTeardown();
});
