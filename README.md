# microcms-php-sdk

## Tutorial

See [official tutorial](https://document.microcms.io/tutorial/php/php-top).

## Installation

```sh
$ composer require microcmsio/microcms-php-sdk
```

## Usage

```php
<?php

require_once('vendor/autoload.php');

$client = new \Microcms\Client(
  "YOUR_DOMAIN",  // YOUR_DOMAIN is the XXXX part of XXXX.microcms.io
  "YOUR_API_KEY"  // API Key
);

echo $client->list("blog")->contents[0]->title;

$list = $client->list("blog", [
  "draftKey" => "foo",
  "limit" => 10,
  "offset" => 1,
  "orders" => ["createdAt", "-updatedAt"],
  "q" => "PHP",
  "fields" => ["id", "title"],
  "filters" => "title[contains]microCMS",
  "depth" => 1
]);
echo $list->contents[0]->title;

echo $client->get("blog", "my-content-id")->title;

echo $client->get("blog", "my-content-id", [
  "draftKey" => "foo",
  "fields" => ["id", "title"],
  "depth" => 1,
])->title;

$createResult = $client->create("blog", [
  "title" => "Hello, microCMS!",
  "contents" => "Awesome contents..."
]);
echo $createResult->id;

$createResult = $client->create("blog", [
  "id" => "new-my-content-id",
  "title" => "Hello, microCMS!",
  "contents" => "Awesome contents..."
]);
echo $createResult->id;

$updateResult = $client->update("blog", [
  "id" => "new-my-content-id",
  "title" => "Hello, microCMS PHP SDK!"
]);
echo $updateResult->id;

$client->delete("blog", "new-my-content-id");
```
