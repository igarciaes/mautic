<?php

namespace MauticAddon\MauticCrmBundle\Api\Exception;

class ErrorException extends \Exception
{

    public function __construct($message = 'Context not found.', $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
