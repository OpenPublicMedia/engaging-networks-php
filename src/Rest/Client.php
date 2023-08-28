<?php
/** @noinspection PhpUnused */
declare(strict_types=1);


namespace OpenPublicMedia\EngagingNetworksServices\Rest;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use OpenPublicMedia\EngagingNetworksServices\Rest\Enums\PageStatus;
use OpenPublicMedia\EngagingNetworksServices\Rest\Enums\PageType;
use OpenPublicMedia\EngagingNetworksServices\Rest\Exception\RequestException;
use OpenPublicMedia\EngagingNetworksServices\Rest\Exception\NotFoundException;
use OpenPublicMedia\EngagingNetworksServices\Rest\Resource\Page;
use OpenPublicMedia\EngagingNetworksServices\Rest\Resource\PageRequestResult;
use OpenPublicMedia\EngagingNetworksServices\Rest\Resource\Supporter;
use OpenPublicMedia\EngagingNetworksServices\Rest\Resource\SupporterField;
use OpenPublicMedia\EngagingNetworksServices\Rest\Resource\SupporterQuestion;
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
    protected GuzzleClient $client;
    private ?string $token = null;


    /**
     * Client constructor.
     *
     * @param array<string, mixed> $httpClientOptions
     */
    public function __construct(
        string $baseUri,
        protected string $apiKey,
        array $httpClientOptions = [],
        protected ?object $cache = null,
        protected string $cache_key_token = 'open_public_media.ens.rest.session_token',
        protected string $cache_key_token_expire = 'open_public_media.ens.rest.session_expire',
    ) {
        $this->client = new GuzzleClient([
            'base_uri' => $baseUri,
            'http_errors' => false,
        ] + $httpClientOptions);
    }


    /**
     * Gets the cache key used for the API token.
     */
    public function getTokenCacheKey(): string
    {
        return $this->cache_key_token;
    }

    /**
     * Sets the cache key used for the API token.
     */
    public function setTokenCacheKey(string $key): void
    {
        $this->cache_key_token = $key;
    }

    /**
     * Gets the cache key used for the API token expiration.
     */
    public function getTokenExpireCacheKey(): string
    {
        return $this->cache_key_token_expire;
    }

    /**
     * Sets the cache key used for the API token expiration.
     */
    public function setTokenExpireCacheKey(string $key): void
    {
        $this->cache_key_token_expire = $key;
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
                $token = $this->cache?->get($this->getTokenCacheKey());
            } catch (InvalidArgumentException $e) {
                throw new RuntimeException($e->getMessage(), $e->getCode(), $e);
            }
        }

        try {
            $expires = $this->cache?->get($this->getTokenExpireCacheKey(), 0);
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
            $this->cache?->set($this->getTokenExpireCacheKey(), $expires);
            $this->cache?->set($this->getTokenCacheKey(), $token);
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

        // ENS REST API uses 404 and 204 interchangeably for "not found".
        if ($response->getStatusCode() === 404 || $response->getStatusCode() === 204) {
            throw new NotFoundException($response);
        } elseif ($response->getStatusCode() !== 200) {
            throw new RequestException($response);
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
     * Sends a POST request to the API and parses a JSON response.
     *
     * @param array<string, mixed> $options
     */
    public function post(string $endpoint, array $options = []): object
    {
        $response = $this->request('post', $endpoint, $options);
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

    /**
     * Processes a page request.
     *
     * @param array<string, mixed> $payload
     *
     * @url https://www.engagingnetworks.support/api/rest/#/operations/processPage
     */
    public function processPage(int $id, array $payload): PageRequestResult
    {
        return PageRequestResult::fromJson($this->post("page/$id/process", ['json' => $payload]));
    }

    /**
     * Gets all possible supporter fields.
     *
     * Fields are keyed by name because that is how they are keyed on a
     * supporter object.
     *
     * @url https://www.engagingnetworks.support/api/rest/#/operations/listSupporterFields
     *
     * @return array<string, SupporterField>
     */
    public function getSupporterFields(): array
    {
        $fields = [];
        $results = $this->get('supporter/fields');
        foreach ($results as $result) {
            $fields[$result->name] = SupporterField::fromJson($result);
        }
        return $fields;
    }

    /**
     * Gets all possible supporter questions.
     *
     * @url https://www.engagingnetworks.support/api/rest/#/operations/listSupporterQuestions
     *
     * @return array<int, SupporterQuestion>
     */
    public function getSupporterQuestions(): array
    {
        $questions = [];
        $results = $this->get('supporter/questions');
        foreach ($results as $result) {
            $questions[$result->id] = SupporterQuestion::fromJson($result);
        }
        return $questions;
    }

    /**
     * Gets a support question with details.
     *
     * This endpoint returns the result as a single item array. A fake response
     * is used to generate an appropriate not found exception.
     *
     * @url https://www.engagingnetworks.support/api/rest/#/operations/viewQuestion
     */
    public function getSupporterQuestion(int $id): SupporterQuestion
    {
        $results = $this->get("supporter/questions/$id");
        if (empty($results)) {
            throw new NotFoundException(new Response(404));
        }
        return SupporterQuestion::fromDetailJson($results[0]);
    }

    /**
     * Builds query string parameters for a supporter GET operations.
     *
     * @return array<string,mixed>
     */
    private static function buildSupporterQuery(
        string $emailAddress = null,
        bool $withMemberships = false,
        bool $withQuestions = false
    ): array {
        $query = [];
        if ($emailAddress) {
            $query['email'] = $emailAddress;
        }
        if ($withMemberships) {
            $query['includeMemberships'] = 'true';
        }
        if ($withQuestions) {
            $query['includeQuestions'] = 'true';
        }
        return $query;
    }

    /**
     * Gets a supporter by supporter ID.
     *
     * @url https://www.engagingnetworks.support/api/rest/#/operations/supporterDetail
     */
    public function getSupporterById(int $id, bool $withMemberships = false, bool $withQuestions = false): Supporter
    {
        $query = self::buildSupporterQuery(withMemberships: $withMemberships, withQuestions:  $withQuestions);
        return Supporter::fromJson($this->get("supporter/$id", ['query' => $query]));
    }

    /**
     * Gets a supporter by supporter email address.
     *
     * @url https://www.engagingnetworks.support/api/rest/#/operations/getSupporterByEmail
     */
    public function getSupporterByEmailAddress(
        string $emailAddress,
        bool $withMemberships = false,
        bool $withQuestions = false
    ): Supporter {
        $query = self::buildSupporterQuery($emailAddress, $withMemberships, $withQuestions);
        return Supporter::fromJson($this->get("supporter", ['query' => $query]));
    }

    /**
     * Adds or updates a supporter.
     *
     * @url https://www.engagingnetworks.support/api/rest/#/operations/supporterUpdate
     *
     * @param array<string, string> $fields
     *   Array of field values to be upsert keyed by field name.
     *
     * @return int
     *   Inserted/update supporter ID.
     */
    public function addOrUpdateSupporter(string $emailAddress, ?array $fields = []): int
    {
        $payload = [Supporter::EMAIL_ADDRESS => $emailAddress, ...$fields];
        $result = $this->post('supporter', ['json' => $payload]);
        return $result->id;
    }
}
