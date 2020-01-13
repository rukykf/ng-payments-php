<?php


namespace Kofi\NgPayments\Exceptions;

use Exception;
use Psr\Http\Message\RequestInterface;

class InvalidRequestBodyException extends Exception
{
    private $httpRequest = null;

    /**
     * @param string $message
     * @param RequestInterface|null $http_request
     */
    public function __construct(
        string $message = "Required parameters for this request are not provided. Check request for details",
        RequestInterface $http_request = null
    ) {
        $this->httpRequest = $http_request;
        parent::__construct($message);
    }

    /**
     * @return RequestInterface|null
     */
    public function getHttpRequest()
    {
        return $this->httpRequest;
    }

    /**
     * @return array|null
     */
    public function getRequestBody()
    {
        return json_decode(@$this->httpRequest->getBody(), true);
    }
}