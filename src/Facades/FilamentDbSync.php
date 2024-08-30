<?php

namespace Teguh02\FilamentDbSync\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Teguh02\FilamentDbSync\FilamentDbSync
 */
class FilamentDbSync extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Teguh02\FilamentDbSync\FilamentDbSync::class;
    }
}
