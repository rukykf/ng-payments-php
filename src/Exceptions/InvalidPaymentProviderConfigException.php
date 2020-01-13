<?php


namespace Kofi\NgPayments\Exceptions;

use Exception;

class InvalidPaymentProviderConfigException extends Exception
{
    /**
     * @param string $message
     */
    public function __construct(
        string $message = "Could not create a PaymentProvider instance from the supplied configuration"
    ) {
        parent::__construct($message);
    }
}