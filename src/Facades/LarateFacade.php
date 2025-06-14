<?php

namespace Dostrog\Larate\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Dostrog\Larate\Larate
 */
class LarateFacade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'larate';
    }
}
