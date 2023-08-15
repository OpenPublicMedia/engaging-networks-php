<?php
/** @noinspection PhpUnused */
declare(strict_types=1);


namespace OpenPublicMedia\EngagingNetworksServices\Rest;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use OpenPublicMedia\EngagingNetworksServices\Rest\Enums\PageStatus;
use OpenPublicMedia\EngagingNetworksServices\Rest\Enums\PageType;
use OpenPublicMedia\EngagingNetworksServices\Rest\Exception\ErrorException;
use OpenPublicMedia\EngagingNetworksServices\Rest\Exception\NotFoundException;
use OpenPublicMedia\EngagingNetworksServices\Rest\Resource\Page;
use OpenPublicMedia\EngagingNetworksServices\Rest\Resource\PageRequestResult;
use OpenPublicMedia\EngagingNetworksServices\Rest\Resource\Supporter;
use OpenPublicMedia\EngagingNetworksServices\Rest\Resource\SupporterField;
use OpenPublicMedia\EngagingNetworksServices\Rest\Resource\SupporterQuestion;
use OpenPublicMedia\EngagingNetworksServices\Rest\Resource\UntaggedSupporterField;
use OpenPublicMedia\EngagingNetworksServices\Rest\Resource\TaggedSupporterField;
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

        // ENS REST API uses 404 and 204 interchangeably for "not found".
        if ($response->getStatusCode() === 404 || $response->getStatusCode() === 204) {
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
     * Sends a POST request to the API and parses a JSON response.
     *
     * @param array<string, mixed> $options
     *
     * @return object|array<object>
     */
    public function post(string $endpoint, array $options = []): object|array
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
}
