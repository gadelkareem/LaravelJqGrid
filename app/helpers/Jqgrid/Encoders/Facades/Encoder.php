<?php

namespace Jqgrid\Encoders\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Class Encoder
 * @package  Jqgrid\Encoders\Facades
 */
class Encoder extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'Jqgrid\Encoders\EncoderSource';
    }
}
