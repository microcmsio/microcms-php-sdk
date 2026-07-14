# microCMS PHP SDK

[microCMS](https://document.microcms.io/) のPHP SDKです。

## 保守方針

このSDKの現在の保守レベルは `Maintenance` です。

詳細は[SDKの保守方針](https://document.microcms.io/manual/limitations#hc2b0bc6659)をご覧ください。

## チュートリアル

[公式チュートリアル](https://document.microcms.io/tutorial/php/php-top)をご覧ください。

## インストール

```sh
$ composer require microcmsio/microcms-php-sdk
```

## 使い方

### インポート

```php
<?php

require_once('vendor/autoload.php');

use \Microcms\Client;
```

### クライアントオブジェクトの作成

```php
$client = new Client(
  "YOUR_DOMAIN",  // YOUR_DOMAINはXXXX.microcms.ioのXXXXの部分です
  "YOUR_API_KEY"  // APIキー
);
```

### コンテンツ一覧の取得

```php
$list = $client->list("endpoint");
echo $list->contents[0]->title;
```

### パラメータを指定したコンテンツ一覧の取得

```php
$list = $client->list("endpoint", [
  "draftKey" => "foo",
  "limit" => 10,
  "offset" => 1,
  "orders" => ["createdAt", "-updatedAt"],
  "q" => "こんにちは",
  "fields" => ["id", "title"],
  "filters" => "title[contains]microCMS",
  "depth" => 1
]);
echo $list->contents[0]->title;
```

### 単一コンテンツの取得

```php
$object = $client->get("endpoint", "my-content-id");
echo $object->title;
```

### パラメータを指定した単一コンテンツの取得

```php
$object = $client->get("endpoint", "my-content-id", [
  "draftKey" => "foo",
  "fields" => ["id", "title"],
  "depth" => 1,
]);
echo $object->title;
```

### オブジェクト形式のコンテンツの取得

```php
$object = $client->get("endpoint");
echo $object->title;
```

### コンテンツの作成

```php
$createResult = $client->create(
  "endpoint",
  [
    "title" => "コンテンツ",
    "contents" => "こんにちは、コンテンツ！"
  ]
);
echo $createResult->id;
```

### IDを指定したコンテンツの作成

```php
$createResult = $client->create(
  "endpoint",
  [
    "id" => "new-my-content-id",
    "title" => "マイコンテンツ",
    "contents" => "こんにちは、マイコンテンツ！"
  ]
);
echo $createResult->id;
```

### 下書きコンテンツの作成

```php
$createResult = $client->create(
  "endpoint",
  [
    "title" => "下書きコンテンツ",
    "contents" => "こんにちは、下書きコンテンツ！"
  ],
  [ "status" => "draft" ]
);
echo $createResult->id;
```

### コンテンツの更新

```php
$updateResult = $client->update("endpoint", [
  "id" => "new-my-content-id",
  "title" => "こんにちは、microCMS PHP SDK！"
]);
echo $updateResult->id;
```

### オブジェクト形式のコンテンツの更新

```php
$updateResult = $client->update("endpoint", [
  "title" => "こんにちは、microCMS PHP SDK！"
]);
echo $updateResult->id;
```

### コンテンツの削除

```php
$client->delete("endpoint", "new-my-content-id");
```
