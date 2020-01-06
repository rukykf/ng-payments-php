<?php


namespace Metav\NgPayments\Exceptions;

use Exception;

class InvalidRequestBodyException extends Exception
{
    public function __construct(
        string $message = "Required parameters for this request are not provided"
    ) {
        parent::__construct($message);
    }
}