<?php


namespace Kofi\NgPayments\Exceptions;

use GuzzleHttp\Exception\BadResponseException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class FailedPaymentException extends BadResponseException
{
    /**
     * @param RequestInterface $httpRequest
     * @param ResponseInterface $httpResponse
     * @param string $message
     */
    public function __construct(
        RequestInterface $httpRequest,
        ResponseInterface $httpResponse,
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