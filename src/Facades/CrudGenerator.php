<?php

namespace Flightsadmin\CrudGenerator\Facades;

use Illuminate\Support\Facades\Facade;

class CrudGenerator extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'crudgenerator';
    }
}
