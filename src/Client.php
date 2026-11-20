<?php

namespace Microcms;

class Client
{
    private string $serviceDomain;
    private string $apiKey;

    private \GuzzleHttp\Client $client;

    public function __construct(string $serviceDomain, string $apiKey, ?\GuzzleHttp\Client $client = null)
    {
        $this->serviceDomain = $serviceDomain;
        $this->apiKey = $apiKey;

        if (is_null($client)) {
            $this->client = new \GuzzleHttp\Client();
        } else {
            $this->client = $client;
        }
    }

    /**
     * @param array<string, mixed> $options
     *
     * @return mixed
     */
    public function list(string $endpoint, array $options = [])
    {
        $path = $this->buildPath($endpoint);
        $response = $this->client->get(
            $path,
            $this->buildOption([
                "query" => $this->buildQuery($options)
            ])
        );
        return json_decode($response->getBody());
    }

    /**
     * @param array<string, mixed> $options
     *
     * @return mixed
     */
    public function get(string $endpoint, string $contentId = "", array $options = [])
    {
        $path = $contentId ? $this->buildPath($endpoint, $contentId) : $this->buildPath($endpoint);
        $response = $this->client->get(
            $path,
            $this->buildOption([
                "query" => $this->buildQuery($options)
            ])
        );
        return json_decode($response->getBody());
    }

    /**
     * @param array<string, mixed> $body
     * @param array<string, mixed> $options
     *
     * @return mixed
     */
    public function create(string $endpoint, array $body = [], array $options = [])
    {
        if (array_key_exists("id", $body)) {
            $method = "PUT";
            $path = $this->buildPath($endpoint, $body["id"]);
        } else {
            $method = "POST";
            $path = $this->buildPath($endpoint);
        }

        $response = $this->client->request(
            $method,
            $path,
            $this->buildOption([
                "json" => $body,
                "query" => $options
            ])
        );
        return json_decode($response->getBody());
    }

    /**
     * @param array<string, mixed> $body
     *
     * @return mixed
     */
    public function update(string $endpoint, array $body = [])
    {
        $response = $this->client->patch(
            array_key_exists("id", $body) ? $this->buildPath($endpoint, $body["id"]) : $this->buildPath($endpoint),
            $this->buildOption([
                "json" => array_filter(
                    $body,
                    function ($v, $k) {
                        return $k != "id";
                    },
                    ARRAY_FILTER_USE_BOTH
                ),
            ])
        );
        return json_decode($response->getBody());
    }

    public function delete(string $endpoint, string $id): void
    {
        $path = $this->buildPath($endpoint, $id);
        $this->client->delete($path, $this->buildOption());
    }

    /**
     * Endpoint names and content ids are each a single path segment. Anything
     * outside this alphabet could add segments, dot segments, a query or an
     * absolute URL, and send the API key to a different API or host.
     *
     * @param mixed ...$segments
     */
    private function buildPath(...$segments): string
    {
        foreach ($segments as $segment) {
            if (is_int($segment)) {
                $segment = (string) $segment;
            }
            if (!is_string($segment) || preg_match('/\A[A-Za-z0-9_-]+\z/', $segment) !== 1) {
                throw new \InvalidArgumentException('Invalid endpoint or content id');
            }
        }
        return implode("/", $segments);
    }

    /**
     * @param array<string, mixed> $option
     *
     * @return array<string, mixed>
     */
    private function buildOption(array $option = []): array
    {
        return array_merge(
            [
                'base_uri' => sprintf("https://%s.microcms.io/api/v1/", $this->serviceDomain),
                'headers' => [
                    'X-MICROCMS-API-KEY' => $this->apiKey,
                ]
            ],
            $option
        );
    }

    /**
     * @param array<string, mixed> $options
     *
     * @return array<string, mixed>
     */
    private function buildQuery(array $options): array
    {
        return array_filter(
            [
                "draftKey" =>
                    array_key_exists("draftKey", $options) ?
                        $options["draftKey"] :
                        null,
                "limit" =>
                    array_key_exists("limit", $options) ?
                        $options["limit"] :
                        null,
                "offset" =>
                    array_key_exists("offset", $options) ?
                        $options["offset"] :
                        null,
                "orders" =>
                    array_key_exists("orders", $options) ?
                        implode(",", $options["orders"]) :
                        null,
                "q" =>
                    array_key_exists("q", $options) ?
                        $options["q"] :
                        null,
                "fields" =>
                    array_key_exists("fields", $options) ?
                        implode(",", $options["fields"]) :
                        null,
                "ids" =>
                    array_key_exists("ids", $options) ?
                        implode(",", $options["ids"]) :
                        null,
                "filters" =>
                    array_key_exists("filters", $options) ?
                        $options["filters"] :
                        null,
                "depth" =>
                    array_key_exists("depth", $options) ?
                        $options["depth"] :
                        null,
            ],
            function ($v, $k) {
                return $v;
            },
            ARRAY_FILTER_USE_BOTH
        );
    }
}
