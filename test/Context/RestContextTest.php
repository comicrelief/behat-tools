<?php

namespace Comicrelief\Behat\Unit\Context;

use Comicrelief\Behat\Context\RestContext;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class RestContextTest extends TestCase
{
    protected $postData = ['ilike' => 'post'];

    public function testClassIsInstantiated()
    {
        $baseUrl = 'https://api-url';

        $class = new RestContext($baseUrl);

        $this->assertInstanceOf(RestContext::class, $class);

        $this->assertEquals($baseUrl, $class->getParameter('base_url'));

        $this->assertInstanceOf(Client::class, $class->getClient());
    }

    public function testISendAGetRequestSuccessfully()
    {
        $baseUrl = 'https://api-url';

        $class = new RestContext($baseUrl);

        $response = $this->prophesize(Response::class);
        $response->getBody()->willReturn('{}');

        $client = $this->prophesize(Client::class);
        $client->get($baseUrl . '/user?', [])->willReturn($response->reveal());

        $class->setClient($client->reveal());

        $class->iSendRequestTo('get', '/user');

        $this->assertTrue($class->theResponseIsJson());
    }

    public function testISendAGetRequestEmptyJson()
    {
        $baseUrl = 'https://api-url';

        $class = new RestContext($baseUrl);

        $response = $this->prophesize(Response::class);
        $response->getBody()->willReturn('[]');

        $client = $this->prophesize(Client::class);
        $client->get($baseUrl . '/user?', [])->willReturn($response->reveal());

        $class->setClient($client->reveal());

        $class->iSendRequestTo('get', '/user');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Response json was empty');

        $class->theResponseIsJson();
    }

    public function testHandleEmptyResponse()
    {
        $baseUrl = 'https://api-url';

        $class = new RestContext($baseUrl);
        //Invalid json
        $response = $this->prophesize(Response::class);
        $response->getBody()->willReturn('sff56767--');

        $client = $this->prophesize(Client::class);
        $client->get($baseUrl . '/user?', [])->willReturn($response->reveal());

        $class->setClient($client->reveal());

        $class->iSendRequestTo('get', '/user');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Response body was empty');

        $class->theResponseIsJson();

        //Empty json
        $response->getBody()->willReturn('');

        $client->get($baseUrl . '/user?', [])->willReturn($response->reveal());

        $class->setClient($client->reveal());

        $class->iSendRequestTo('get', '/user');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Response body was empty');

        $class->theResponseIsJson();
    }

    public function testISendASuccessfulPostRequest()
    {
        $baseUrl = 'https://api-url';

        $class = new RestContext($baseUrl);

        $response = $this->prophesize(Response::class);
        $response->getBody()->willReturn('{}');

        $client = $this->prophesize(Client::class);
        $client->post($baseUrl .'/user', ["json" => $this->postData, "verify" => false])
            ->willReturn($response->reveal());

        $class->setClient($client->reveal());
        $class->setRequestPayload($this->postData);

        $class->iSendRequestTo('post', '/user');

        $this->assertTrue($class->theResponseIsJson());
    }

    public function testISendAFailedPostRequest()
    {
        $baseUrl = 'https://api-url';

        $class = new RestContext($baseUrl);

        $response = $this->prophesize(Response::class);
        $response->getBody()->willReturn('{}');
        $response->getStatusCode()->willReturn(404);
        $requestException = $this->prophesize(\GuzzleHttp\Exception\RequestException::class);
        $requestException->getResponse()->willReturn($response->reveal());

        $client = $this->prophesize(Client::class);
        $client->post($baseUrl .'/user', ["json" => $this->postData, "verify" => false])
            ->willThrow($requestException->reveal());

        $class->setClient($client->reveal());
        $class->setRequestPayload($this->postData);

        $class->iSendRequestTo('post', '/user');

        $this->assertNull($class->theRestResponseStatusCodeShouldBe('404'));
    }

    public function testISendASuccessfulDeleteRequest()
    {
        $baseUrl = 'https://api-url';

        $class = new RestContext($baseUrl);

        $response = $this->prophesize(Response::class);
        $response->getBody()->willReturn('{}');

        $client = $this->prophesize(Client::class);
        $client->delete($baseUrl . '/user?', [])->willReturn($response->reveal());

        $class->setClient($client->reveal());

        $class->iSendRequestTo('delete', '/user');

        $this->assertTrue($class->theResponseIsJson());
    }

    public function testTheResponseHasProperty()
    {
        $baseUrl = 'https://api-url';

        $class = new RestContext($baseUrl);

        $jsonArray = \GuzzleHttp\json_decode($this->getFixture('create-user.json'), true);

        $this->assertEquals($class::path($jsonArray, 'contentType'), 'test-content');
        $this->assertEquals($class::path($jsonArray, 'metadata.method'), 'post');
        $this->assertNull($class::path($jsonArray, 'abc.abc'));
        $this->assertNull($class::path($jsonArray, 'contentType.abc'));
    }

    /**
     * @param string $name
     * @return bool|string
     */
    protected function getFixture(string $name): string
    {
        $filename = dirname(__DIR__) . '/fixture/'. $name;
        $fixture = file_get_contents($filename);
        return $fixture;
    }


}