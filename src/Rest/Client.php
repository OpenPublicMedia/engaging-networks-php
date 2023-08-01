<?php
/** @noinspection PhpUnused */
declare(strict_types=1);


namespace OpenPublicMedia\EngagingNetworksServices\Rest;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use OpenPublicMedia\EngagingNetworksServices\Enums\PageStatus;
use OpenPublicMedia\EngagingNetworksServices\Enums\PageType;
use OpenPublicMedia\EngagingNetworksServices\Page;
use OpenPublicMedia\EngagingNetworksServices\Rest\Exception\ErrorException;
use OpenPublicMedia\EngagingNetworksServices\Rest\Exception\NotFoundException;
use Psr\Http\Message\ResponseInterface;
use Psr\SimpleCache\InvalidArgumentException;
use RuntimeException;

/**
 * ENS REST API Client.
 *
 * @url https://www.engagingnetworks.support/api/rest/
 *
 * @package OpenPublicMedia\EngagingNetworksServices\Rest
 */
class Client
{
    const SESSION_EXPIRE_KEY = 'open_public_media.ens.rest.session_expire';
    const SESSION_TOKEN_KEY = 'open_public_media.ens.rest.session_token';

    protected string $apiKey;
    /**
     * Cache interface for storing the short-lived auth token.
     *
     * A PSR-16 compliant interface is preferred but any class providing
     * `set($key, $value)` and `get($key, $default)` methods will suffice.
     *
     * If not provided a new token will be generated for every request.
     */
    protected ?object $cache;
    protected ?GuzzleClient $client;
    private ?string $token = null;


    /**
     * Client constructor.
     *
     * @param array<string, mixed> $httpClientOptions
     */
    public function __construct(
        string $baseUri,
        string $apiKey,
        array $httpClientOptions = [],
        ?object $cache = null
    ) {
        $this->apiKey = $apiKey;
        $this->cache = $cache;
        $this->client = new GuzzleClient([
            'base_uri' => $baseUri,
            'http_errors' => false,
        ] + $httpClientOptions);
    }

    /**
     * Gets an API token, refreshing it if necessary.
     *
     * @url https://www.engagingnetworks.support/api/rest/#/operations/authenticate
     */
    private function getToken(): ?string
    {
        if ($this->token) {
            $token = $this->token;
        } else {
            try {
                $token = $this->cache?->get(self::SESSION_TOKEN_KEY);
            } catch (InvalidArgumentException $e) {
                throw new RuntimeException($e->getMessage(), $e->getCode(), $e);
            }
        }

        try {
            $expires = $this->cache?->get(self::SESSION_EXPIRE_KEY, 0);
            if (time() >= $expires - 300) {
                $token = null;
            }
        } catch (InvalidArgumentException $e) {
            throw new RuntimeException($e->getMessage(), $e->getCode(), $e);
        }

        if (!$token) {
            $response = $this->request('post', 'authenticate', ['body' => $this->apiKey]);
            $data = json_decode($response->getBody()->getContents(), true);
            $token = $data['ens-auth-token'];
            $expires = time() + ($data['expires']/1000);
            $this->cache?->set(self::SESSION_EXPIRE_KEY, $expires);
            $this->cache?->set(self::SESSION_TOKEN_KEY, $token);
        }

        if ($token != $this->token) {
            $this->token = $token;
        }

        return $this->token;
    }

    /**
     * Sends a request to the API.
     *
     * @param array<string, mixed> $options
     */
    public function request(string $method, string $endpoint, array $options = []): ResponseInterface
    {
        // Authentication endpoints do not require a token.
        if (!str_starts_with($endpoint, 'authenticate')) {
            $options['headers'] = ['ens-auth-token' => $this->getToken()] + ($options['headers'] ?? []);
        }

        try {
            $response = $this->client->request($method, $endpoint, $options);
        } catch (GuzzleException $e) {
            throw new RuntimeException($e->getMessage(), $e->getCode(), $e);
        }

        if ($response->getStatusCode() === 404) {
            throw new NotFoundException($response);
        } elseif ($response->getStatusCode() !== 200) {
            throw new ErrorException($response);
        }

        return $response;
    }

    /**
     * Sends a GET request to the API and parses a JSON response.
     *
     * @param array<string, mixed> $options
     *
     * @return object|array<object>
     */
    public function get(string $endpoint, array $options = []): object|array
    {
        $response = $this->request('get', $endpoint, $options);
        return json_decode($response->getBody()->getContents());
    }

    /**
     * Gets pages.
     *
     * @return Page[]
     *
     * @url https://www.engagingnetworks.support/api/rest/#/operations/listPages
     */
    public function getPages(PageType $type, ?PageStatus $status = null): array
    {
        $results = $this->get('page', ['query' => ['type' => $type->value, 'status' => $status?->value]]);
        $pages = [];
        foreach ($results as $result) {
            $pages[] = Page::fromJson($result);
        }
        return $pages;
    }

    /**
     * Gets a page.
     *
     * @url https://www.engagingnetworks.support/api/rest/#/operations/getPageDetails
     */
    public function getPage(int $id): Page
    {
        return Page::fromJson($this->get("page/$id"));
    }
}
