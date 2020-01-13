<?php


namespace Kofi\NgPayments\Exceptions;

use BadMethodCallException;

class FeatureNotSupportedException extends BadMethodCallException
{
    /**
     * @param string $message
     */
    public function __construct(
        $message = "This provider does not provide an endpoint for the feature you are trying to use"
    ) {
        parent::__construct($message);
    }
}
