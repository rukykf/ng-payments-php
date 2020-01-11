<?php


namespace Kofi\NgPayments\Exceptions;

use GuzzleHttp\Exception\BadResponseException;

class FailedTransactionException extends BadResponseException
{
    public function __construct(
        $httpRequest,
        $httpResponse,
        string $message = "Transaction Failed. Check Response Body for details"
    ) {
        parent::__construct($message, $httpRequest, $httpResponse);
    }

    /**
     * @return array
     */
    public function getResponseBody()
    {
        return json_decode($this->getResponse()->getBody(), true);
    }

}