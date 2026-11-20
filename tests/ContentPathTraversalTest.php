<?php

namespace Microcms\Test;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Microcms\Client;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

/**
 * Content ids and endpoint names are path segments. A value that climbs out of
 * the named endpoint must be rejected before the HTTP handler runs.
 */
final class ContentPathTraversalTest extends TestCase
{
    private MockHandler $mock;

    private Client $client;

    /** @var array<int, array<string, mixed>> */
    private $container;

    protected function setUp(): void
    {
        $this->container = [];
        $container = &$this->container;

        $this->mock = new MockHandler([]);
        $handlerStack = HandlerStack::create($this->mock);
        $handlerStack->push(Middleware::history($container));

        $this->client = new Client('service', 'key', new HttpClient(['handler' => $handlerStack]));
    }

    /**
     * @return array<string, array{0: string}>
     */
    public function escapingContentIds(): array
    {
        return [
            'parent api' => ['../inquiries'],
            'parent api entry' => ['../inquiries/some-id'],
            'parent segment' => ['..'],
            'above api version' => ['../../secret'],
            'nested parent' => ['foo/../../inquiries'],
            'dot parent' => ['./../inquiries'],
            'parent with query' => ['../inquiries?draftKey=x']
        ];
    }

    /**
     * @return array<string, array{0: string}>
     */
    public function escapingEndpoints(): array
    {
        return [
            'endpoint parent' => ['blog/../inquiries'],
            'endpoint above version' => ['../inquiries'],
            'endpoint nested parent' => ['blog/foo/../../inquiries']
        ];
    }

    /**
     * @dataProvider escapingContentIds
     */
    public function testGetRejectsEscapingContentId(string $contentId): void
    {
        $this->assertRejectedBeforeRequest(function () use ($contentId): void {
            $this->client->get('blog', $contentId);
        });
    }

    /**
     * @dataProvider escapingContentIds
     */
    public function testCreateRejectsEscapingContentId(string $contentId): void
    {
        $this->assertRejectedBeforeRequest(function () use ($contentId): void {
            $this->client->create('blog', [
                'id' => $contentId,
                'title' => 'x'
            ]);
        });
    }

    /**
     * @dataProvider escapingContentIds
     */
    public function testUpdateRejectsEscapingContentId(string $contentId): void
    {
        $this->assertRejectedBeforeRequest(function () use ($contentId): void {
            $this->client->update('blog', [
                'id' => $contentId,
                'title' => 'x'
            ]);
        });
    }

    /**
     * @dataProvider escapingContentIds
     */
    public function testDeleteRejectsEscapingContentId(string $contentId): void
    {
        $this->assertRejectedBeforeRequest(function () use ($contentId): void {
            $this->client->delete('blog', $contentId);
        });
    }

    public function testGetWithQueryOptionsRejectsEscapingContentId(): void
    {
        $this->assertRejectedBeforeRequest(function (): void {
            $this->client->get('blog', '../inquiries', [
                'fields' => ['title']
            ]);
        });
    }

    /**
     * @dataProvider escapingEndpoints
     */
    public function testListRejectsEscapingEndpoint(string $endpoint): void
    {
        $this->assertRejectedBeforeRequest(function () use ($endpoint): void {
            $this->client->list($endpoint);
        });
    }

    /**
     * @dataProvider escapingEndpoints
     */
    public function testGetRejectsEscapingEndpoint(string $endpoint): void
    {
        $this->assertRejectedBeforeRequest(function () use ($endpoint): void {
            $this->client->get($endpoint);
        });
    }

    /**
     * @dataProvider escapingEndpoints
     */
    public function testCreateRejectsEscapingEndpoint(string $endpoint): void
    {
        $this->assertRejectedBeforeRequest(function () use ($endpoint): void {
            $this->client->create($endpoint, [
                'title' => 'x'
            ]);
        });
    }

    /**
     * @dataProvider escapingEndpoints
     */
    public function testUpdateRejectsEscapingEndpoint(string $endpoint): void
    {
        $this->assertRejectedBeforeRequest(function () use ($endpoint): void {
            $this->client->update($endpoint, [
                'title' => 'x'
            ]);
        });
    }

    /**
     * @dataProvider escapingEndpoints
     */
    public function testDeleteRejectsEscapingEndpoint(string $endpoint): void
    {
        $this->assertRejectedBeforeRequest(function () use ($endpoint): void {
            $this->client->delete($endpoint, 'entry-1');
        });
    }

    public function testOrdinaryContentIdIsStillRequested(): void
    {
        $this->mock->append(new Response(200, [], '{"id":"my-content-id"}'));

        $this->client->get('blog', 'my-content-id');

        $request = $this->sentRequest();
        $this->assertSame('GET', $request->getMethod());
        $this->assertSame(
            'https://service.microcms.io/api/v1/blog/my-content-id',
            (string) $request->getUri()
        );
    }

    private function assertRejectedBeforeRequest(callable $call): void
    {
        $this->mock->append(new Response(200, [], '{}'));

        try {
            $call();
        } catch (\InvalidArgumentException $exception) {
            $this->assertCount(0, $this->container);

            return;
        }

        $request = $this->container[0]['request'] ?? null;
        if ($request instanceof RequestInterface) {
            $sent = $request->getMethod() . ' ' . $request->getUri();
            $sent .= $request->hasHeader('X-MICROCMS-API-KEY') ?
                ' with X-MICROCMS-API-KEY' :
                ' without X-MICROCMS-API-KEY';
        } else {
            $sent = 'no request';
        }

        $this->fail('Expected InvalidArgumentException before a request was sent, got ' . $sent);
    }

    private function sentRequest(): RequestInterface
    {
        $request = $this->container[0]['request'] ?? null;
        $this->assertInstanceOf(RequestInterface::class, $request);

        return $request;
    }
}
