<?php


namespace Metav\NgPayments\Exceptions;

use Exception;

class FailedTransactionException extends Exception
{
    private $responseBody;

    public function __construct(
        array $response_body,
        string $message = "Transaction Failed. Check Response Body for details"
    ) {
        parent::__construct($message);
        $this->responseBody = $response_body;
    }

    /**
     * @return array
     */
    public function getResponseBody()
    {
        return $this->responseBody;
    }

}