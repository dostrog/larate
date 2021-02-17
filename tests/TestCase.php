<?php

namespace Dostrog\Larate\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Dostrog\Larate\Providers\LarateServiceProvider;
use Dostrog\Larate\Facades\LarateFacade;

class TestCase extends Orchestra
{
    public function setUp(): void
    {
        parent::setUp();
    }

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
