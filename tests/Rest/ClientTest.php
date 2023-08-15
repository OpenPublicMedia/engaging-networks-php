<?php
declare(strict_types=1);


namespace OpenPublicMedia\EngagingNetworksServices\Test\Rest;

use GuzzleHttp\Psr7\Response;
use OpenPublicMedia\EngagingNetworksServices\Rest\Enums\PageRequestResultStatus;
use OpenPublicMedia\EngagingNetworksServices\Rest\Enums\PageRequestResultType;
use OpenPublicMedia\EngagingNetworksServices\Rest\Enums\PaymentType;
use OpenPublicMedia\EngagingNetworksServices\Rest\Enums\RecurringFrequency;
use OpenPublicMedia\EngagingNetworksServices\Rest\Exception\SupporterFieldNotFoundException;
use OpenPublicMedia\EngagingNetworksServices\Rest\Resource\Page;
use OpenPublicMedia\EngagingNetworksServices\Rest\Enums\PageType;
use OpenPublicMedia\EngagingNetworksServices\Rest\Exception\ErrorException;
use OpenPublicMedia\EngagingNetworksServices\Rest\Exception\NotFoundException;
use OpenPublicMedia\EngagingNetworksServices\Rest\Resource\PageRequestResult;
use OpenPublicMedia\EngagingNetworksServices\Rest\Resource\Supporter;
use OpenPublicMedia\EngagingNetworksServices\Rest\Resource\SupporterField;
use OpenPublicMedia\EngagingNetworksServices\Rest\Resource\SupporterQuestion;
use OpenPublicMedia\EngagingNetworksServices\Test\TestCaseBase;

/**
 * Class ClientTest
 *
 * @coversDefaultClass \OpenPublicMedia\EngagingNetworksServices\Rest\Client
 *
 * @package OpenPublicMedia\EngagingNetworksServices\Test
 */
class ClientTest extends TestCaseBase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->mockHandler->append($this->jsonFixtureResponse('postAuthenticate'));
    }

    /**
     * @covers ::getToken
     */
    public function testCacheClient(): void {
        $this->mockHandler->append($this->jsonFixtureResponse('getPage'));
        $result1 = $this->restClientWithCache->getPage(112233);
        $this->assertInstanceOf(Page::class, $result1);
        $this->mockHandler->append($this->jsonFixtureResponse('getPage'));
        $result2 = $this->restClientWithCache->getPage(112233);
        $this->assertInstanceOf(Page::class, $result2);
        $cache_file = __DIR__  . '/../data/test.db.php';
        if (is_writable($cache_file)) {
            unlink($cache_file);
        }
    }

    /**
     * @covers ::request
     */
    public function testErrorException(): void {
        $this->mockHandler->append($this->jsonFixtureResponse('getPages-error', 404));
        $this->expectException(ErrorException::class);
        $this->restClient->getPages(PageType::dc);
    }

    /**
     * @covers ::request
     */
    public function testNotFoundException(): void {
        $this->mockHandler->append($this->jsonFixtureResponse('getPage-notFound', 404));
        $this->expectException(NotFoundException::class);
        $this->restClient->getPage(999999);
    }

    public function testGetPage(): void
    {
        $this->mockHandler->append($this->jsonFixtureResponse('getPage'));
        $result = $this->restClient->getPage(112233);
        $this->assertInstanceOf(Page::class, $result);
    }

    public function testGetPages(): void
    {
        $this->mockHandler->append($this->jsonFixtureResponse('getPages'));
        $results = $this->restClient->getPages(PageType::dcf);
        $this->assertIsArray($results);
        foreach ($results as $result) {
            $this->assertInstanceOf(Page::class, $result);
        }
    }

    public function testProcessPage(): void
    {
        $this->mockHandler->append($this->jsonFixtureResponse('processPage'));
        $result = $this->restClient->processPage(1234, []);
        $this->assertInstanceOf(PageRequestResult::class, $result);
        $this->assertEquals(PageRequestResultStatus::success, $result->getStatus());
        $this->assertEquals(PageRequestResultType::creditSingle, $result->getType());
        $this->assertEquals(PaymentType::visa, $result->getPaymentType());
        $this->assertEquals(RecurringFrequency::monthly, $result->getRecurringFrequency());
    }

    public function testGetSupporterFields(): void {
        $this->mockHandler->append($this->jsonFixtureResponse('getSupporterFields'));
        $results = $this->restClient->getSupporterFields();
        $this->assertIsArray($results);
        $this->assertCount(4, $results);
        foreach ($results as $result) {
            $this->assertInstanceOf(SupporterField::class, $result);
        }
        $this->assertTrue($results['First Name']->isTagged());
        $this->assertFalse($results['Age']->isTagged());
    }

    public function testGetSupporterQuestions(): void {
        $this->mockHandler->append($this->jsonFixtureResponse('getSupporterQuestions'));
        $results = $this->restClient->getSupporterQuestions();
        $this->assertIsArray($results);
        $this->assertCount(3, $results);
        foreach ($results as $result) {
            $this->assertInstanceOf(SupporterQuestion::class, $result);
            $this->assertFalse($result->hasDetails());
        }
    }

    public function testGetSupporterQuestion(): void {
        $this->mockHandler->append($this->jsonFixtureResponse('getSupporterQuestion'));
        $question = $this->restClient->getSupporterQuestion(1234567);
        $this->assertInstanceOf(SupporterQuestion::class, $question);
        $this->assertTrue($question->hasDetails());

        $this->mockHandler->append(
            $this->jsonFixtureResponse('postAuthenticate'),
            $this->jsonFixtureResponse('getSupporterQuestion-notFound')
        );
        $this->expectException(NotFoundException::class);
        $this->restClient->getSupporterQuestion(1);
    }

    public function testGetSupporterById(): void {
        $this->mockHandler->append($this->jsonFixtureResponse('getSupporter'));
        $supporter = $this->restClient->getSupporterById(1234567890);
        $this->assertInstanceOf(Supporter::class, $supporter);
        $this->assertEquals('32', $supporter->getField('Age'));
        $this->expectException(SupporterFieldNotFoundException::class);
        $supporter->getField('Not found');

        $this->mockHandler->append($this->jsonFixtureResponse('postAuthenticate'), new Response(204));
        $this->expectException(NotFoundException::class);
        $this->restClient->getSupporterById(1);
    }

    public function testGetSupporterByEmail(): void {
        $this->mockHandler->append($this->jsonFixtureResponse('getSupporter'));
        $supporter = $this->restClient->getSupporterByEmailAddress('first.last@example.com');
        $this->assertInstanceOf(Supporter::class, $supporter);
    }

    public function testGetSupporterNotFound(): void {
        $this->mockHandler->append(new Response(204));
        $this->expectException(NotFoundException::class);
        $this->restClient->getSupporterById(1);
    }

}
