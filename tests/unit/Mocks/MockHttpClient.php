<?php


namespace Metav\NgPayments\Tests\unit\Mocks;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;

class MockHttpClient
{
    private $mockHandler = null;
    private $historyContainer = [];

    public function getMockHttpClient(array $responses = [])
    {
        if (empty($responses)) {
            $responses = [
                new Response(200)
            ];
        }
        $this->historyContainer = [];
        $history = Middleware::history($this->historyContainer);
        $this->mockHandler = new MockHandler($responses);
        $handler_stack = HandlerStack::create($this->mockHandler);
        $handler_stack->push($history);
        return new Client(['handler' => $handler_stack]);
    }

    public function getMockHandler()
    {
        return $this->mockHandler;
    }

    public function resetMockHandler()
    {
        $this->mockHandler->reset();
    }

    public function appendResponsesToMockHttpClient(array $responses)
    {
        $this->mockHandler->append($responses);
    }

    public function getHttpTransactionHistory(): array
    {
        return $this->historyContainer;
    }

    public function getRecentHttpTransaction()
    {
        $index = count($this->historyContainer) - 1;
        if ($index < 0) {
            return [];
        } else {
            return $this->historyContainer[$index];
        }
    }

    public function getRecentRequest()
    {
        return @$this->getRecentHttpTransaction()['request'];
    }

    public function getRecentRequestBody()
    {
        $request = $this->getRecentRequest();
        if ($request == null) {
            return [];
        }

        return json_decode($request->getBody(), true);
    }

    public function getRecentResponse()
    {
        return @$this->getRecentHttpTransaction()['response'];
    }

    public function getRecentResponseBody()
    {
        $response = $this->getRecentResponse();
        if ($response == null) {
            return [];
        }

        return json_decode($response->getBody(), true);
    }
}