<?php

namespace Dostrog\Larate\Tests;

use Dostrog\Larate\Facades\LarateFacade;
use Dostrog\Larate\Providers\LarateServiceProvider;
use Orchestra\Testbench\Attributes\WithMigration;
use Orchestra\Testbench\TestCase as Orchestra;

#[WithMigration]
class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            LarateServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app): array
    {
        return [
            'Larate' => LarateFacade::class,
        ];
    }

}
