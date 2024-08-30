<?php

namespace Teguh02\FilamentDbSync\Commands;

use Illuminate\Console\Command;

class FilamentDbSyncCommand extends Command
{
    public $signature = 'filament-db-sync';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
