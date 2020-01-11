<?php


namespace Kofi\NgPayments\Tests\unit\Mocks;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;

class MockHttpClient
{
    private static $mockHandler = null;
    private static $historyContainer = [];

    public static function getHttpClient(array $responses = [])
    {
        if (empty($responses)) {
            $responses = [
                new Response(200)
            ];
        }
        $history = Middleware::history(self::$historyContainer);
        self::$mockHandler = new MockHandler($responses);
        $handler_stack = HandlerStack::create(self::$mockHandler);
        $handler_stack->push($history);
        return new Client(['handler' => $handler_stack]);
    }

    public static function getMockHandler()
    {
        return self::$mockHandler;
    }

    public static function resetMockHandler()
    {
        self::$mockHandler->reset();
    }

    public static function resetHttpTransactionHistory()
    {
        self::$historyContainer = [];
    }

    public static function appendResponsesToMockHttpClient(array $responses)
    {
        foreach ($responses as $response) {
            self::$mockHandler->append($response);
        }
    }

    public static function getHttpTransactionHistory(): array
    {
        return self::$historyContainer;
    }

    public static function getRecentHttpTransaction()
    {
        $index = count(self::$historyContainer) - 1;
        if ($index < 0) {
            return [];
        } else {
            return self::$historyContainer[$index];
        }
    }

    public static function getRecentRequest()
    {
        return @self::getRecentHttpTransaction()['request'];
    }

    public static function getRecentRequestBody()
    {
        $request = self::getRecentRequest();
        if ($request == null) {
            return [];
        }

        return json_decode($request->getBody(), true);
    }

    public static function getRecentResponse()
    {
        return @self::getRecentHttpTransaction()['response'];
    }

    public static function getRecentResponseBody()
    {
        $response = self::getRecentResponse();
        if ($response == null) {
            return [];
        }

        return json_decode($response->getBody(), true);
    }
}