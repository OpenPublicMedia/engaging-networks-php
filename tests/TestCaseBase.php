<?php


namespace OpenPublicMedia\EngagingNetworksServices\Test;

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use OpenPublicMedia\EngagingNetworksServices\Rest\Client;
use PHPUnit\Framework\TestCase;
use Tochka\Cache\ArrayFileCache;

class TestCaseBase extends TestCase
{
    protected Client $restClient;
    protected Client $restClientWithCache;
    protected MockHandler $mockHandler;

    /**
     * Create client with mock handler.
     */
    protected function setUp(): void
    {
        $this->mockHandler = new MockHandler();
        $this->restClient = new Client('https://us.example.com', '1234567890', ['handler' => $this->mockHandler]);
        $cache = new ArrayFileCache(__DIR__ . '/data', 'test.db');
        $this->restClientWithCache = new Client('https://us.example.com', '1234567890', ['handler' => $this->mockHandler], $cache);
    }

    /**
     * Returns a regular JSON response.
     */
    protected static function jsonFixtureResponse(string $name, int $statusCode = 200): Response
    {
        return self::apiJsonResponse($statusCode, file_get_contents(__DIR__ . "/fixtures/$name.json"));
    }

    /**
     * Returns a response with a provided code and json content.
     */
    protected static function apiJsonResponse(int $code, string $json = '[]'): Response
    {
        return new Response($code, ['Content-Type' => 'application/json'], $json);
    }
}
