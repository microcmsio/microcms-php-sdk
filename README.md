# microCMS PHP SDK

[microCMS](https://document.microcms.io/manual/api-request) PHP SDK.

## Tutorial

See [official tutorial](https://document.microcms.io/tutorial/php/php-top).

## Installation

```sh
$ composer require microcmsio/microcms-php-sdk
```

## Usage

### Import

```php
<?php

require_once('vendor/autoload.php');

use \Microcms\Client;
```

### Create client object

```php
$client = new Client(
  "YOUR_DOMAIN",  // YOUR_DOMAIN is the XXXX part of XXXX.microcms.io
  "YOUR_API_KEY"  // API Key
);
```

### Get content list

```php
$list = $client->list("endpoint");
echo $list->contents[0]->title;
```

### Get content list with parameters

```php
$list = $client->list("endpoint", [
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
```

### Get single content

```php
$object = $client->get("endpoint", "my-content-id");
echo $object->title;
```

### Get single content with parameters

```php
$object = $client->get("endpoint", "my-content-id", [
  "draftKey" => "foo",
  "fields" => ["id", "title"],
  "depth" => 1,
]);
echo $object->title;
```

### Get object form content

```php
$object = $client->get("endpoint");
echo $object->title;
```

### Create content

```php
$createResult = $client->create(
  "endpoint",
  [
    "title" => "Hello, microCMS!",
    "contents" => "Awesome contents..."
  ]
);
echo $createResult->id;
```

### Create content with specified ID

```php
$createResult = $client->create(
  "endpoint",
  [
    "id" => "new-my-content-id",
    "title" => "Hello, microCMS!",
    "contents" => "Awesome contents..."
  ]
);
echo $createResult->id;
```

### Create draft content

```php
$createResult = $client->create(
  "endpoint",
  [
    "title" => "Hello, microCMS!",
    "contents" => "Awesome contents..."
  ],
  [ "status" => "draft" ]
);
echo $createResult->id;
```

### Update content

```php
$updateResult = $client->update("endpoint", [
  "id" => "new-my-content-id",
  "title" => "Hello, microCMS PHP SDK!"
]);
echo $updateResult->id;
```

### Update object form content

```php
$updateResult = $client->update("endpoint", [
  "title" => "Hello, microCMS PHP SDK!"
]);
echo $updateResult->id;
```

### Delete content

```php
$client->delete("endpoint", "new-my-content-id");
```
