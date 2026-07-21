<?php

class Order extends Model
{
    public static function create(array $data): int
    {
        $st = self::db()->prepare(
            'INSERT INTO orders (user_id, buyer_name, buyer_email, buyer_phone, buyer_nif, gdpr_consent_at, total, currency, country, discount, coupon_id, status, payment_method, payment_ref)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $st->execute([
            $data['user_id'],
            $data['buyer_name'],
            $data['buyer_email'],
            $data['buyer_phone'] ?? null,
            $data['buyer_nif'] ?? null,
            $data['gdpr_consent_at'] ?? null,
            $data['total'],
            $data['currency'] ?? 'EUR',
            $data['country'] ?? 'PT',
            $data['discount'] ?? 0,
            $data['coupon_id'] ?? null,
            $data['status'] ?? 'pending',
            $data['payment_method'] ?? 'simulado',
            $data['payment_ref'] ?? null,
        ]);
        return (int) self::db()->lastInsertId();
    }

    public static function addItem(int $orderId, int $ticketTypeId, int $qty, float $unitPrice, ?int $seatId = null): int
    {
        $st = self::db()->prepare(
            'INSERT INTO order_items (order_id, ticket_type_id, qty, unit_price, seat_id) VALUES (?, ?, ?, ?, ?)'
        );
        $st->execute([$orderId, $ticketTypeId, $qty, $unitPrice, $seatId]);
        return (int) self::db()->lastInsertId();
    }

    public static function find(int $id): ?array
    {
        $st = self::db()->prepare('SELECT * FROM orders WHERE id = ?');
        $st->execute([$id]);
        $row = $st->fetch();
        return $row ?: null;
    }

    public static function findByStripeSession(string $sessionId): ?array
    {
        $st = self::db()->prepare('SELECT * FROM orders WHERE stripe_session_id = ?');
        $st->execute([$sessionId]);
        $row = $st->fetch();
        return $row ?: null;
    }

    public static function items(int $orderId): array
    {
        $st = self::db()->prepare(
            'SELECT oi.*, tt.name AS ticket_name, oi.unit_price,
                    e.title AS event_title, e.starts_at, e.venue, e.city, e.country, e.currency,
                    e.promoter_name, e.promoter_nif, e.age_rating, e.capacity, e.has_seats,
                    u.name AS producer_user_name
             FROM order_items oi
             JOIN ticket_types tt ON tt.id = oi.ticket_type_id
             JOIN events e ON e.id = tt.event_id
             LEFT JOIN users u ON u.id = e.producer_id
             WHERE oi.order_id = ?'
        );
        $st->execute([$orderId]);
        return $st->fetchAll();
    }

    public static function all(): array
    {
        return self::db()->query(
            'SELECT o.*, u.name AS user_name FROM orders o LEFT JOIN users u ON u.id = o.user_id ORDER BY o.created_at DESC'
        )->fetchAll();
    }

    public static function byProducer(int $producerId): array
    {
        $st = self::db()->prepare(
            'SELECT DISTINCT o.*, e.title AS event_title
             FROM orders o
             JOIN order_items oi ON oi.order_id = o.id
             JOIN ticket_types tt ON tt.id = oi.ticket_type_id
             JOIN events e ON e.id = tt.event_id
             WHERE e.producer_id = ?
             ORDER BY o.created_at DESC'
        );
        $st->execute([$producerId]);
        return $st->fetchAll();
    }

    public static function byUser(int $userId): array
    {
        $st = self::db()->prepare('SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC');
        $st->execute([$userId]);
        return $st->fetchAll();
    }

    public static function setStripeSession(int $id, string $sessionId): void
    {
        self::db()->prepare('UPDATE orders SET stripe_session_id = ? WHERE id = ?')->execute([$sessionId, $id]);
    }

    public static function markPaid(int $id, ?string $paymentRef = null, ?string $method = null): void
    {
        $st = self::db()->prepare(
            "UPDATE orders SET status = 'paid', payment_ref = COALESCE(?, payment_ref),
             payment_method = COALESCE(?, payment_method) WHERE id = ? AND status IN ('pending','paid')"
        );
        $st->execute([$paymentRef, $method, $id]);
    }

    public static function refund(int $id, string $reason): bool
    {
        $order = self::find($id);
        if (!$order || $order['status'] !== 'paid' || $order['refunded_at']) {
            return false;
        }
        $st = self::db()->prepare(
            "UPDATE orders SET status = 'cancelled', refunded_at = NOW(), refund_reason = ? WHERE id = ?"
        );
        $st->execute([$reason, $id]);
        // restore stock roughly by items
        foreach (self::items($id) as $item) {
            self::db()->prepare('UPDATE ticket_types SET stock = stock + ? WHERE id = ?')
                ->execute([(int) $item['qty'], (int) $item['ticket_type_id']]);
            if (!empty($item['seat_id'])) {
                self::db()->prepare("UPDATE seats SET status = 'available' WHERE id = ?")
                    ->execute([(int) $item['seat_id']]);
            }
        }
        return true;
    }

    public static function payableTotal(array $order): float
    {
        return max(0, (float) $order['total'] - (float) ($order['discount'] ?? 0));
    }

    public static function count(): int
    {
        return (int) self::db()->query('SELECT COUNT(*) FROM orders')->fetchColumn();
    }

    public static function revenue(): float
    {
        return (float) self::db()->query(
            "SELECT COALESCE(SUM(total - discount),0) FROM orders WHERE status = 'paid' AND refunded_at IS NULL"
        )->fetchColumn();
    }

    public static function exportRows(?int $producerId = null): array
    {
        if ($producerId) {
            $st = self::db()->prepare(
                'SELECT DISTINCT o.id, o.buyer_name, o.buyer_email, o.total, o.discount, o.status, o.payment_method, o.created_at, e.title AS event_title
                 FROM orders o
                 JOIN order_items oi ON oi.order_id = o.id
                 JOIN ticket_types tt ON tt.id = oi.ticket_type_id
                 JOIN events e ON e.id = tt.event_id
                 WHERE e.producer_id = ?
                 ORDER BY o.created_at DESC'
            );
            $st->execute([$producerId]);
            return $st->fetchAll();
        }
        return self::db()->query(
            'SELECT o.id, o.buyer_name, o.buyer_email, o.total, o.discount, o.status, o.payment_method, o.created_at
             FROM orders o ORDER BY o.created_at DESC'
        )->fetchAll();
    }
}
