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
        }
    }
});

$pdo = Database::connection();
$st = $pdo->query(
    "SELECT t.*, o.buyer_name, o.buyer_email, oi.order_id,
            e.title AS event_title, e.venue, e.city, e.starts_at, tt.name AS ticket_name
     FROM tickets t
     JOIN order_items oi ON oi.id = t.order_item_id
     JOIN orders o ON o.id = oi.order_id
     JOIN ticket_types tt ON tt.id = oi.ticket_type_id
     JOIN events e ON e.id = tt.event_id
     WHERE o.status = 'paid'
       AND (t.pdf_path IS NULL OR t.pdf_path = '')"
);

$n = 0;
while ($ticket = $st->fetch(PDO::FETCH_ASSOC)) {
    $path = TicketService::regeneratePdf($ticket);
    echo "OK #{$ticket['order_id']} {$ticket['code']} => " . basename($path) . ' (' . filesize($path) . "b)\n";
    $n++;
}
echo "Repaired: $n\n";
