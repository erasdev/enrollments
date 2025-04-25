<?php

namespace ErasDev\Enrollments\Models;

use ErasDev\Enrollments\Builders\AgeRequirementBuilder;
use ErasDev\Enrollments\Enums\EnrollmentRuleType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class EnrollmentRule extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'enrollable_type',
        'enrollable_id',
        'type',
        'config',
        'enabled',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'config' => 'array',
        'enabled' => 'boolean',
    ];

    /**
     * Get the enrollable model.
     */
    public function enrollable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the type of the rule.
     */
    public function type(): string
    {
        return $this->type;
    }

    /**
     * Get the configuration for the rule.
     */
    public function config(): array
    {
        return $this->config;
    }

    /**
     * Check if the rule is enabled.
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * Enable the rule.
     */
    public function enable(): void
    {
        $this->enabled = true;
        $this->save();
    }

    /**
     * Disable the rule.
     */
    public function disable(): void
    {
        $this->enabled = false;
        $this->save();
    }

    /**
     * Get the class name of the rule.
     */
    public function getRuleClass(): string
    {
        $type = $this->type();
        $config = config('enrollments.rules.types');

        if (! isset($config[$type])) {
            throw new \Exception("No rule class found for type: {$type}");
        }

        $class = $config[$type];
        if ($class === 'default') {
            $class = match ($type) {
                EnrollmentRuleType::AGE_REQUIREMENT->value => \ErasDev\Enrollments\Rules\AgeRequirementRule::class,
                EnrollmentRuleType::CAPACITY->value => \ErasDev\Enrollments\Rules\CapacityRule::class,
                default => throw new \Exception("No default rule class found for type: {$type}"),
            };
        }

        if (! class_exists($class)) {
            throw new \Exception("Rule class not found: {$class}");
        }

        return $class;
    }

    /**
     * Get the rule instance.
     *
     * @return mixed
     */
    public function getRule()
    {
        $class = $this->getRuleClass();
        $rule = new $class($this);

        return $rule;
    }

    /**
     * Resolve the handler for this rule.
     *
     * @return mixed
     */
    public function resolveHandler()
    {
        $handler = $this->getRule();

        return $handler;
    }

    /**
     * Check if the rule passes for the given enrollable and user.
     */
    public function passes(Model $enrollable, Model $user): bool
    {
        return $this->resolveHandler()->passes($user, $enrollable);
    }

    /**
     * Create a new age requirement rule for the given enrollable.
     *
     * @return AgeRequirementBuilder
     */
    public static function ageRequirement(Model $enrollable)
    {
        $existingRule = EnrollmentRule::where('enrollable_type', get_class($enrollable))
            ->where('type', EnrollmentRuleType::AGE_REQUIREMENT->value)
            ->first();

        if ($existingRule) {
            throw new \Exception('An age requirement already exists for this model.');
        }

        return new AgeRequirementBuilder($enrollable);
    }
}
