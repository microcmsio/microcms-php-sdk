<?php

namespace Microcms\Test;

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Microcms\Client;
use PHPUnit\Framework\TestCase;

final class ClientTest extends TestCase
{
    private $handlerStack;
    private $mock;
    private $container;

    protected function setUp(): void {
        $this->handlerStack = HandlerStack::create();
        $this->container = [];

        $this->mock = new MockHandler([]);
        $this->handlerStack->setHandler($this->mock);

        $history = Middleware::history($this->container);
        $this->handlerStack->push($history);
    }

    public function testList(): void
    {
        $this->mock->append(new Response(
            200,
            [],
            <<<JSON
                {
                    "contents": [
                        {
                            "id": "my-content-id",
                            "createdAt": "2021-10-26T01:55:09.701Z",
                            "updatedAt": "2021-10-26T01:55:09.701Z",
                            "publishedAt": "2021-10-26T01:55:09.701Z",
                            "revisedAt": "2021-10-26T01:55:09.701Z",
                            "title": "foo",
                            "body": "Hello, microCMS!"
                        }
                    ],
                    "totalCount": 1,
                    "offset": 0,
                    "limit": 10
                }
            JSON
        ));

        $client = new Client("service", "key", new \GuzzleHttp\Client(['handler' => $this->handlerStack]));
        $result = $client->list("blog");

        $this->assertCount(1, $this->container);
        $request = $this->container[0]['request'];
        $this->assertEquals("GET", $request->getMethod());
        $this->assertEquals("https://service.microcms.io/api/v1/blog", $request->getUri());
        $this->assertEquals("key", $request->getHeader('X-MICROCMS-API-KEY')[0]);

        $this->assertCount(1, $result->contents);
        $this->assertEquals("my-content-id", $result->contents[0]->id);
        $this->assertEquals(1, $result->totalCount);
        $this->assertEquals(0, $result->offset);
        $this->assertEquals(10, $result->limit);
    }

    public function testGet(): void
    {
        $this->mock->append(new Response(
            200,
            [],
            <<<JSON
                {
                    "id": "my-content-id",
                    "createdAt": "2021-10-26T01:55:09.701Z",
                    "updatedAt": "2021-10-26T01:55:09.701Z",
                    "publishedAt": "2021-10-26T01:55:09.701Z",
                    "revisedAt": "2021-10-26T01:55:09.701Z",
                    "title": "foo",
                    "body": "Hello, microCMS!"
                }
            JSON
        ));

        $client = new Client("service", "key", new \GuzzleHttp\Client(['handler' => $this->handlerStack]));
        $result = $client->get("blog", "my-content-id");

        $request = $this->container[0]['request'];
        $this->assertEquals("GET", $request->getMethod());
        $this->assertEquals("https://service.microcms.io/api/v1/blog/my-content-id", $request->getUri());
        $this->assertEquals("key", $request->getHeader('X-MICROCMS-API-KEY')[0]);

        $this->assertEquals("my-content-id", $result->id);
    }


    public function testCreateWithoutId(): void
    {
        $this->mock->append(new Response(
            201,
            [],
            <<<JSON
                {"id": "my-content-id"}
            JSON
        ));

        $client = new Client("service", "key", new \GuzzleHttp\Client(['handler' => $this->handlerStack]));
        $result = $client->create("blog", [
            "title" => "foo"
        ]);

        $request = $this->container[0]['request'];
        $this->assertEquals("POST", $request->getMethod());
        $this->assertEquals("https://service.microcms.io/api/v1/blog", $request->getUri());
        $this->assertEquals("key", $request->getHeader('X-MICROCMS-API-KEY')[0]);
        $this->assertEquals(
            ["title" => "foo"],
            json_decode($request->getBody(), true)
        );

        $this->assertEquals("my-content-id", $result->id);
    }

    public function testCreateWithId(): void
    {
        $this->mock->append(new Response(
            201,
            [],
            <<<JSON
                {"id": "my-content-id"}
            JSON
        ));

        $client = new Client("service", "key", new \GuzzleHttp\Client(['handler' => $this->handlerStack]));
        $result = $client->create("blog", [
            "id" => "my-content-id",
            "title" => "foo"
        ]);

        $request = $this->container[0]['request'];
        $this->assertEquals("PUT", $request->getMethod());
        $this->assertEquals("https://service.microcms.io/api/v1/blog/my-content-id", $request->getUri());
        $this->assertEquals("key", $request->getHeader('X-MICROCMS-API-KEY')[0]);
        $this->assertEquals(
            [
                "id" => "my-content-id",
                "title" => "foo",
            ],
            json_decode($request->getBody(), true)
        );

        $this->assertEquals("my-content-id", $result->id);
    }

    public function testUpdate(): void
    {
        $this->mock->append(new Response(
            200,
            [],
            <<<JSON
                {"id": "my-content-id"}
            JSON
        ));

        $client = new Client("service", "key", new \GuzzleHttp\Client(['handler' => $this->handlerStack]));
        $result = $client->update("blog", [
            "id" => "my-content-id",
            "title" => "foo"
        ]);

        $request = $this->container[0]['request'];
        $this->assertEquals("PATCH", $request->getMethod());
        $this->assertEquals("https://service.microcms.io/api/v1/blog/my-content-id", $request->getUri());
        $this->assertEquals("key", $request->getHeader('X-MICROCMS-API-KEY')[0]);
        $this->assertEquals(
            ["title" => "foo"],
            json_decode($request->getBody(), true)
        );

        $this->assertEquals("my-content-id", $result->id);
    }

    public function testDelete(): void
    {
        $this->mock->append(new Response(
            202,
            [],
            ""
        ));

        $client = new Client("service", "key", new \GuzzleHttp\Client(['handler' => $this->handlerStack]));
        $client->delete("blog", "my-content-id");

        $request = $this->container[0]['request'];
        $this->assertEquals("DELETE", $request->getMethod());
        $this->assertEquals("https://service.microcms.io/api/v1/blog/my-content-id", $request->getUri());
        $this->assertEquals("key", $request->getHeader('X-MICROCMS-API-KEY')[0]);
    }
}
