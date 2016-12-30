<?php 

namespace Bnb\GoogleCloudPrint\Facades;

use Illuminate\Support\Facades\Facade;

class GoogleCloudPrint extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'google.print';
    }
}
