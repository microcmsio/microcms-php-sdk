<?php

namespace Microcms;

class Client
{
    private $client;

    public function __construct(string $serviceDomain, string $apiKey)
    {
        $this->client = new \GuzzleHttp\Client([
            'base_uri' => sprintf("https://%s.microcms.io/api/v1/", $serviceDomain),
            'headers' => [
                'X-MICROCMS-API-KEY' => $apiKey,
            ]
        ]);
    }

    public function list(string $endpoint, array $options = [])
    {
        $path = $endpoint;
        $response = $this->client->get(
            $path,
            [
                "query" => $this->buildQuery($options)
            ]
        );
        return json_decode($response->getBody());
    }

    public function get(string $endpoint, string $contentId, array $options = [])
    {
        $path = implode("/", [$endpoint, $contentId]);
        $response = $this->client->get(
            $path,
            [
                "query" => $this->buildQuery($options)
            ]
        );
        return json_decode($response->getBody());
    }

    public function create(string $endpoint, array $body = [])
    {
        if (array_key_exists("id", $body)) {
            $method = "PUT";
            $path =  implode("/", [$endpoint, $body["id"]]);
        } else {
            $method = "POST";
            $path = $endpoint;
        }

        $response = $this->client->request(
            $method,
            $path,
            [
                "json" => $body,
            ]
        );
        return json_decode($response->getBody());
    }

    public function update(string $endpoint, array $body = [])
    {
        $response = $this->client->patch(
            implode("/", [$endpoint, $body["id"]]),
            [
                "json" => $body,
            ]
        );
        return json_decode($response->getBody());
    }

    public function delete(string $endpoint, string $id)
    {
        $path = implode("/", [$endpoint, $id]);
        $this->client->delete($path);
    }

    private function buildQuery(array $options) {
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
