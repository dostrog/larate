<?php

namespace Dostrog\Larate\Commands;

use Dostrog\Larate\Providers\LarateServiceProvider;
use Illuminate\Console\Command;

class InstallLarate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'larate:install';
    /**
     * The console command description.
     *
     * @var string|null
     */
    protected $description = 'Install the Larate';

    public function handle(): void
    {
        $this->info('Installing Larate...');

        $this->info('Publishing configuration...');

        $this->call('vendor:publish', [
            '--provider' => LarateServiceProvider::class,
            '--tag' => "config",
        ]);
    }
}
