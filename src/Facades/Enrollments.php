<?php

namespace Erasdev\Enrollments\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Erasdev\Enrollments\Enrollments
 */
class Enrollments extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Erasdev\Enrollments\Enrollments::class;
    }
}
