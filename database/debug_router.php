<?php

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../config/env.php';
env_load(__DIR__ . '/../.env');
require __DIR__ . '/../app/helpers.php';
require __DIR__ . '/../app/Database.php';
require __DIR__ . '/../app/Router.php';

echo 'config url=' . config('url') . PHP_EOL;

$uris = [
    '/Ticket/public/checkout',
    '/Ticket/public/checkout/',
    '/checkout',
    '/Ticket/public/carrinho',
    '/Ticket/public/pagamento/mbway/1',
];

foreach ($uris as $uri) {
    $path = parse_url($uri, PHP_URL_PATH) ?: '/';
    $base = rtrim(config('url', ''), '/');
    $orig = $path;
    if ($base && str_starts_with($path, $base)) {
        $path = substr($path, strlen($base)) ?: '/';
    }
    $path = '/' . trim($path, '/');
    if ($path !== '/') {
        $path = rtrim($path, '/');
    }
    echo "$orig => $path\n";
}
