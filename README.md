# microcms-php-sdk

## Installation

```sh
$ composer require lambdasawa/microcms-php-sdk
```

## Usage

```php
$client = new \Lambdasawa\MicrocmsPhpSdk\Client(
  "XXXX",  // YOUR_DOMAIN is the XXXX part of XXXX.microcms.io
  "XXX"    // API Key
));

var_dump($client->list("blog"));
var_dump($client->list("tag"));

var_dump($client->list("blog", [
    "draftKey" => "foo",
    "limit" => 10,
    "offset" => 1,
    "orders" => ["createdAt", "-updatedAt"],
    "q" => "PHP",
    "fields" => ["id", "title"],
    "filters" => "title[contains]microCMS",
    "depth" => 1
]));

var_dump($client.get("blog", "my-content-id"));

var_dump($client.get("blog", "my-content-id", [
    "draftKey" => "foo",
    "fields" => ["id", "title"],
    "depth" => 1,
]));

var_dump($client.create("blog", []
    "title" => "Hello, microCMS!",
    "contents" => "Awesome contents..."
]));

var_dump($client.create("blog", [
    "id" => "my-content-id",
    "title" => "Hello, microCMS!",
    "contents" => "Awesome contents..."
]));

var_dump($client.update("blog", [
    "id" => "my-content-id",
    "title" => "Hello, microCMS Ruby SDK!"
]));

var_dump($client.delete("blog", "my-content-id"));
```
