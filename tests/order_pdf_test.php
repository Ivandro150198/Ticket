<?php

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../config/env.php';
env_load(__DIR__ . '/../.env');
require __DIR__ . '/../app/helpers.php';
require __DIR__ . '/../app/Database.php';

spl_autoload_register(function (string $class): void {
    foreach (['Models', 'Services'] as $d) {
        $p = __DIR__ . '/../app/' . $d . '/' . $class . '.php';
        if (is_file($p)) {
            require $p;
            return;
        }
    }
});

$pdo = Database::connection();
$pdo->beginTransaction();
TicketType::decrementStock(2, 1);
$oid = Order::create([
    'user_id' => 3,
    'buyer_name' => 'Test PDF',
    'buyer_email' => 'teste@example.com',
    'total' => 23.58,
    'discount' => 0,
    'status' => 'pending',
    'payment_method' => 'simulado',
]);
Order::addItem($oid, 2, 1, 23.58);
$pdo->commit();
PaymentService::markPaid($oid, 'TEST', 'simulado');
$t = Ticket::byOrder($oid);
$path = __DIR__ . '/../' . ($t[0]['pdf_path'] ?? '');
echo 'order=' . $oid . ' tickets=' . count($t) . PHP_EOL;
echo 'pdf=' . ($t[0]['pdf_path'] ?? '') . ' exists=' . (is_file($path) ? 'yes' : 'no') . ' size=' . (is_file($path) ? filesize($path) : 0) . PHP_EOL;
exit((count($t) && is_file($path)) ? 0 : 1);
