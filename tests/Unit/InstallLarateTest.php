<?php

namespace Dostrog\Larate\Tests\Unit;

use Dostrog\Larate\Facades\LarateFacade;
use Dostrog\Larate\Larate;
use Dostrog\Larate\Tests\TestCase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class InstallLarateTest extends TestCase
{
    public const CONFIG_NAME = 'larate.php';

    public function setUp(): void
    {
        parent::setUp();

        // make sure we're starting from a clean state
        if (File::exists(config_path(self::CONFIG_NAME))) {
            unlink(config_path(self::CONFIG_NAME));
        }
    }

    /** @test */
    public function the_install_command_copies_the_configuration(): void
    {
        self::assertFalse(File::exists(config_path(self::CONFIG_NAME)));

        Artisan::call('larate:install');

        self::assertTrue(File::exists(config_path(self::CONFIG_NAME)));
    }

    /** @test */
    public function the_class_registered_in_app_container(): void
    {
        self::assertInstanceOf(Larate::class, app('larate'));
    }

    /** @test */
    public function the_facade_is_aliased(): void
    {
        self::assertTrue(class_exists(LarateFacade::class));
        self::assertInstanceOf(Larate::class, LarateFacade::getFacadeRoot());
    }
}
