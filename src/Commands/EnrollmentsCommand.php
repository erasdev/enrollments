<?php

namespace Erasdev\Enrollments\Commands;

use Illuminate\Console\Command;

class EnrollmentsCommand extends Command
{
    public $signature = 'enrollments';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
