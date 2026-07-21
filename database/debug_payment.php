<?php

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../config/env.php';
env_load(__DIR__ . '/../.env');
require __DIR__ . '/../app/helpers.php';
require __DIR__ . '/../app/Database.php';

spl_autoload_register(static function (string $class): void {
    foreach ([
        __DIR__ . '/../app/Services/' . $class . '.php',
        __DIR__ . '/../app/Models/' . $class . '.php',
    ] as $file) {
        if (is_file($file)) {
            require $file;
            return;
        }
    }
});

$pdo = Database::connection();

echo "=== Columns orders ===\n";
$cols = $pdo->query("SHOW COLUMNS FROM orders")->fetchAll(PDO::FETCH_ASSOC);
foreach ($cols as $c) {
    echo $c['Field'] . ' ' . $c['Type'] . "\n";
}

echo "\n=== Sample event ===\n";
$ev = $pdo->query('SELECT id, title, country, currency FROM events WHERE status="published" LIMIT 1')->fetch(PDO::FETCH_ASSOC);
print_r($ev);

$type = $pdo->query('SELECT * FROM ticket_types WHERE event_id=' . (int)$ev['id'] . ' LIMIT 1')->fetch(PDO::FETCH_ASSOC);
print_r($type);

echo "\n=== Try Order::create ===\n";
try {
    $id = Order::create([
        'user_id' => null,
        'buyer_name' => 'Teste Pagamento',
        'buyer_email' => 'teste@local.test',
        'buyer_phone' => '900000000',
        'total' => 10,
        'currency' => $ev['currency'] ?? 'EUR',
        'country' => $ev['country'] ?? 'PT',
        'discount' => 0,
        'coupon_id' => null,
        'status' => 'pending',
        'payment_method' => 'simulado',
    ]);
    echo "order ok #$id\n";

    Order::addItem($id, (int)$type['id'], 1, 10.0);
    echo "item ok\n";

    PaymentService::markPaid($id, 'SIM-TEST', 'simulado');
    $order = Order::find($id);
    echo "status=" . $order['status'] . " method=" . $order['payment_method'] . "\n";
    $tickets = Ticket::byOrder($id);
    echo "tickets=" . count($tickets) . "\n";
} catch (Throwable $e) {
    echo 'ERROR: ' . $e->getMessage() . "\n";
    echo $e->getFile() . ':' . $e->getLine() . "\n";
    echo $e->getTraceAsString() . "\n";
}

echo "\n=== payment_methods_for_country ===\n";
print_r(payment_methods_for_country('PT'));
print_r(payment_methods_for_country('GW'));
