<?php


namespace Kofi\NgPayments\Exceptions;

use Exception;
use Psr\Http\Message\RequestInterface;

class InvalidRequestBodyException extends Exception
{
    private $httpRequest = null;

    public function __construct(
        string $message = "Required parameters for this request are not provided. Check request for details",
        RequestInterface $http_request = null
    ) {
        $this->httpRequest = $http_request;
        parent::__construct($message);
    }

    public function getHttpRequest()
    {
        return $this->httpRequest;
    }

    public function getRequestBody()
    {
        return json_decode(@$this->httpRequest->getBody(), true);
    }
}