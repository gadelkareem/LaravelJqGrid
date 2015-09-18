<?php

namespace App;

/**
 * Class Utils
 */
class Utils
{
    /**
     * Casting
     *
     * @param $value
     * @param $cast
     * @throws \Exception
     * @return mixed
     */
    public static function cast($value, $cast)
    {
        switch ($cast) {
            case 'string':
                if (empty(trim($value))) {
                    return null;
                }
                return (string)$value;
            case 'integer':
                return (int)$value;
            case 'boolean':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            case 'timestamp':
                if (is_string($value) && trim($value) == '') {
                    return null;
                }
                if (!$value instanceof \DateTime && strtotime($value)) {
                    return new \DateTime($value);
                }
                return $value;
            default:
                throw new \Exception('No type casting for ' . $cast);
        }
    }


}
