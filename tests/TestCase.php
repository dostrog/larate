<?php

namespace Dostrog\Larate\Tests;

use Dostrog\Larate\Facades\LarateFacade;
use Dostrog\Larate\Providers\LarateServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            LarateServiceProvider::class,
        ];
    }

    //    protected function getPackageAliases($app): array
    //    {
    //        return [
    //            'Larate' => LarateFacade::class,
    //        ];
    //    }

    //    public function getEnvironmentSetUp($app): void
    //    {
    //        $app['config']->set('database.default', 'sqlite');
    //        $app['config']->set('database.connections.sqlite', [
    //            'driver' => 'sqlite',
    //            'database' => ':memory:',
    //            'prefix' => '',
    //        ]);
    //    }
}
