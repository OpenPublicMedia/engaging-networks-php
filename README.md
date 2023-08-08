# Engaging Networks PHP Library

This library abstracts interactions with the [Engaging Networks Services (ENS) APIs](https://www.engagingnetworks.support/knowledge-base/engaging-networks-services-ens/).

## Implemented APIs:

- [REST API](https://www.engagingnetworks.support/api/rest/#/)

## Installation

Install via composer:

```bash
composer require openpublicmedia/engaging-networks-php
```

## Use

### REST API

The `OpenPublicMedia\EngagingNetworksServices\Rest\Client` queries the REST API.
The client requires an API User with a whitelisted IP address and an API token
in the target Engaging Networks account.

See [REST API Instructions](https://www.engagingnetworks.support/knowledge-base/engaging-networks-services-rest-services/)

### Examples

#### Creating a client

```php
use OpenPublicMedia\EngagingNetworksServices\Rest\Client;

$base_uri = 'https://ca.engagingnetworks.app/ens/service/';
$api_key = '11111111-2222-3333-4444-555555555555';

$client = new Client($base_uri, $api_key);
```

Providing a cache service is also supported (and recommended) when creating the
client. If the client has a cache service it will be used to cache the
authentication token provided by the ENS REST API across multiple requests for
the lifetime of the token.

A PSR-16 compliant interface is preferred but any class providing
`set($key, $value)` and `get($key, $default)` methods will suffice.

```php
use OpenPublicMedia\EngagingNetworksServices\Rest\Client;
use Tochka\Cache\ArrayFileCache;

$base_uri = 'https://ca.engagingnetworks.app/ens/service/';
$api_key = '11111111-2222-3333-4444-555555555555';

$cache = new ArrayFileCache('.', 'my_awesome_cache');
$client = new Client($base_uri, $api_key, cache: $cache);
```

#### Handling exceptions

Custom exceptions are provided for 404 response and general errors. Additional
information from the ENS REST API is captured in these exceptions.

```php
use OpenPublicMedia\EngagingNetworksServices\Rest\Client;

$base_uri = 'https://ca.engagingnetworks.app/ens/service/';
$api_key = '11111111-2222-3333-4444-555555555555';

$client = new Client($base_uri, $api_key);
try {
    $results = $client->getPages(PageType::dc);
} catch (Exception $e) {
    var_dump(get_class($e));
    var_dump($e->getMessage());
    var_dump($e->getCode());
    var_dump($e->getErrorMessage());
    var_dump($e->getErrorMessageId());
}
```

## Development goals

See [CONTRIBUTING](CONTRIBUTING.md) for information about contributing to
this project.

### v1

- [x] ENS REST API client (`\OpenPublicMedia\EngagingNetworksServices\Rest\Client`)
- [x] API direct querying (`$client->request()`)
- [x] Result/error handling
- [x] Page Services
  - [x] [List Pages by type](https://www.engagingnetworks.support/api/rest/#/operations/listPages)
  - [x] [View page details](https://www.engagingnetworks.support/api/rest/#/operations/getPageDetails)
  - [x] [Process a page request](https://www.engagingnetworks.support/api/rest/#/operations/processPage)
- [ ] Supporter Services
  - [ ] [List available supporter fields](https://www.engagingnetworks.support/api/rest/#/operations/listSupporterFields)
  - [ ] [List available supporter questions](https://www.engagingnetworks.support/api/rest/#/operations/listSupporterQuestions)
  - [ ] [Retrieve details about the content of a question](https://www.engagingnetworks.support/api/rest/#/operations/viewQuestion)
  - [ ] [View Supporter by Email Address](https://www.engagingnetworks.support/api/rest/#/operations/getSupporterByEmail)
  - [ ] [Add / Update Supporter](https://www.engagingnetworks.support/api/rest/#/operations/supporterUpdate)
  - [ ] [View Supporter by ID](https://www.engagingnetworks.support/api/rest/#/operations/supporterDetail)
  - [ ] [Supporter query](https://www.engagingnetworks.support/api/rest/#/operations/supporterQuery)
