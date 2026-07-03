<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Microcms\Client;

// 環境変数から接続情報を取得します。
// 実行例:
//   YOUR_DOMAIN=your-domain \
//   YOUR_API_KEY=your-api-key \
//   YOUR_ENDPOINT=your-endpoint \
//   php examples/run.php
$domain = getenv('YOUR_DOMAIN');
$apiKey = getenv('YOUR_API_KEY');
$endpoint = getenv('YOUR_ENDPOINT');

if ($domain === false || $apiKey === false || $endpoint === false) {
  fwrite(STDERR, "環境変数 YOUR_DOMAIN, YOUR_API_KEY, YOUR_ENDPOINT を設定してください。\n");
  exit(1);
}

$client = new Client($domain, $apiKey);

// 一覧を取得します。
echo "=== list ===\n";
$list = $client->list($endpoint, [
  'limit' => 10,
  'orders' => ['-publishedAt'],
]);
var_dump($list);

// コンテンツIDを指定して1件取得します。
$contentId = 'my-content-id';
echo "=== get ===\n";
$content = $client->get($endpoint, $contentId);
var_dump($content);

// コンテンツを作成します。
echo "=== create ===\n";
$created = $client->create($endpoint, [
  'title' => 'php-sdkより追加したコンテンツ',
]);
var_dump($created);

// レスポンスは stdClass オブジェクトなので、プロパティアクセス(->)で値を取得します。
$createdId = $created->id;

// コンテンツを更新します。
echo "=== update ===\n";
$updated = $client->update($endpoint, [
  'id' => $createdId,
  'title' => 'php-sdkより更新したコンテンツ',
]);
var_dump($updated);

// コンテンツを削除します。
echo "=== delete ===\n";
$client->delete($endpoint, $createdId);
echo "deleted\n";
