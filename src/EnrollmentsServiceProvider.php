<?php

namespace Erasdev\Enrollments;

use Erasdev\Enrollments\Commands\EnrollmentsCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class EnrollmentsServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('enrollments')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_enrollments_table')
            ->hasCommand(EnrollmentsCommand::class);
    }
}
