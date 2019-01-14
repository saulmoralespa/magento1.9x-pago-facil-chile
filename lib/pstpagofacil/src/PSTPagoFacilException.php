<?php
/**
 * Created by PhpStorm.
 * User: smp
 * Date: 20/12/18
 * Time: 09:23 PM
 */

namespace PSTPagoFacil;

class PSTPagoFacilException extends \Exception
{
    public function __construct($message, $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}