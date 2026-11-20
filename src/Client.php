<?php

namespace Microcms;

class Client
{
    private string $serviceDomain;
    private string $apiKey;

    private \GuzzleHttp\Client $client;

    public function __construct(string $serviceDomain, string $apiKey, ?\GuzzleHttp\Client $client = null)
    {
        if (!preg_match('/\A[A-Za-z0-9](?:[A-Za-z0-9-]*[A-Za-z0-9])?\z/', $serviceDomain)) {
            throw new \InvalidArgumentException("serviceDomain must be a single host label");
        }
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
        $response = $this->client->get(
            $this->buildUri($endpoint),
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
        $response = $this->client->get(
            $this->buildUri($endpoint, $contentId ? $contentId : null),
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
            $uri = $this->buildUri($endpoint, $body["id"]);
        } else {
            $method = "POST";
            $uri = $this->buildUri($endpoint);
        }

        $response = $this->client->request(
            $method,
            $uri,
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
            $this->buildUri($endpoint, array_key_exists("id", $body) ? $body["id"] : null),
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
        $this->client->delete($this->buildUri($endpoint, $id), $this->buildOption());
    }

    /**
     * @param mixed $contentId
     */
    private function buildUri(string $endpoint, $contentId = null): \Psr\Http\Message\UriInterface
    {
        $host = sprintf("%s.microcms.io", $this->serviceDomain);
        $path = "/api/v1/" . $this->pathSegment($endpoint);
        if ($contentId !== null) {
            $path .= "/" . $this->pathSegment($contentId);
        }

        $uri = new \GuzzleHttp\Psr7\Uri("https://" . $host . $path);
        if (
            $uri->getScheme() !== "https" ||
            strtolower($uri->getHost()) !== strtolower($host) ||
            $uri->getUserInfo() !== "" ||
            $uri->getPort() !== null ||
            $uri->getPath() !== $path
        ) {
            throw new \InvalidArgumentException("Request URI must stay on the microCMS service host");
        }

        return $uri;
    }

    /**
     * @param mixed $value
     */
    private function pathSegment($value): string
    {
        if (is_int($value)) {
            $value = (string) $value;
        }
        if (
            !is_string($value) ||
            $value === "." ||
            $value === ".." ||
            !preg_match('/\A[A-Za-z0-9._~-]+\z/', $value)
        ) {
            throw new \InvalidArgumentException("Endpoint and content id must each be a single URL path segment");
        }

        return $value;
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
