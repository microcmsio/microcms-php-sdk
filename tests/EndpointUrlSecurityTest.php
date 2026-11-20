<?php

namespace Microcms\Test;

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Microcms\Client;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

/**
 * Absolute and protocol-relative endpoint strings must be refused before any
 * request is issued. These tests fail while Client still forwards that string
 * to the HTTP client with the API key header.
 */
final class EndpointUrlSecurityTest extends TestCase
{
    private const SERVICE_DOMAIN = 'service-domain';
    private const API_KEY = 'placeholder-api-key';

    private HandlerStack $handlerStack;
    private MockHandler $mock;

    /** @var array<int, array<string, mixed>> */
    private $container;

    protected function setUp(): void
    {
        $this->handlerStack = HandlerStack::create();
        $this->container = [];
        $container = &$this->container;

        $this->mock = new MockHandler([]);
        $this->handlerStack->setHandler($this->mock);
        $this->handlerStack->push(Middleware::history($container));
    }

    /**
     * @dataProvider unsafeEndpointProvider
     */
    public function testUnsafeEndpointIsRejectedBeforeAnyRequest(string $operation, string $endpoint, ?string $contentId): void
    {
        $this->mock->append(new Response(200, [], '{"id":"content-id"}'));
        $client = new Client(
            self::SERVICE_DOMAIN,
            self::API_KEY,
            new \GuzzleHttp\Client(['handler' => $this->handlerStack])
        );

        try {
            $this->invoke($client, $operation, $endpoint, $contentId);
        } catch (\InvalidArgumentException $exception) {
            $this->assertNotSame('', $exception->getMessage());
            $this->assertSame(
                [],
                $this->container,
                'The API key request must not be handed to the HTTP client before it is refused'
            );

            return;
        }

        $this->fail($this->describeRequests($operation, $endpoint));
    }

    /**
     * @return array<string, array{0: string, 1: string, 2: string|null}>
     */
    public function unsafeEndpointProvider(): array
    {
        $endpoints = [
            'https scheme' => 'https://attacker.example/collect',
            'http scheme' => 'http://attacker.example/collect',
            'protocol-relative' => '//attacker.example/collect',
            'uppercase scheme' => 'HTTPS://attacker.example/collect',
            'userinfo host' => 'https://service-domain.microcms.io@attacker.example/collect',
            'dotted suffix host' => 'https://service-domain.microcms.io.attacker.example/collect',
        ];

        $cases = [];
        foreach ($endpoints as $name => $endpoint) {
            $cases['list ' . $name] = ['list', $endpoint, null];
            $cases['get ' . $name] = ['get', $endpoint, ''];
            $cases['get with content id ' . $name] = ['get', $endpoint, 'content-id'];
            $cases['create ' . $name] = ['create', $endpoint, null];
            $cases['create with content id ' . $name] = ['create', $endpoint, 'content-id'];
            $cases['update ' . $name] = ['update', $endpoint, null];
            $cases['update with content id ' . $name] = ['update', $endpoint, 'content-id'];
            $cases['delete ' . $name] = ['delete', $endpoint, 'content-id'];
        }

        return $cases;
    }

    private function invoke(Client $client, string $operation, string $endpoint, ?string $contentId): void
    {
        switch ($operation) {
            case 'list':
                $client->list($endpoint, ['limit' => 1]);

                return;
            case 'get':
                $client->get($endpoint, $contentId ?? '', ['depth' => 1]);

                return;
            case 'create':
                $body = ['title' => 'title'];
                if ($contentId !== null) {
                    $body['id'] = $contentId;
                }
                $client->create($endpoint, $body, ['status' => 'draft']);

                return;
            case 'update':
                $body = ['title' => 'title'];
                if ($contentId !== null) {
                    $body['id'] = $contentId;
                }
                $client->update($endpoint, $body);

                return;
            case 'delete':
                $client->delete($endpoint, $contentId ?? 'content-id');

                return;
            default:
                $this->fail('Unknown operation ' . $operation);
        }
    }

    private function describeRequests(string $operation, string $endpoint): string
    {
        if ($this->container === []) {
            return sprintf(
                '%s accepted endpoint %s and returned without refusing it',
                $operation,
                $endpoint
            );
        }

        $lines = [];
        foreach ($this->container as $entry) {
            $request = $entry['request'];
            if (!$request instanceof RequestInterface) {
                $lines[] = 'non-request entry';

                continue;
            }

            $lines[] = sprintf(
                '%s %s host=%s api-key-header=%s',
                $request->getMethod(),
                (string) $request->getUri(),
                $request->getUri()->getHost(),
                $request->hasHeader('X-MICROCMS-API-KEY') ? 'present' : 'absent'
            );
        }

        return sprintf(
            '%s sent %d request(s) for endpoint %s: %s',
            $operation,
            count($this->container),
            $endpoint,
            implode('; ', $lines)
        );
    }
}
