<?php

class Ticket extends Model
{
    public static function create(array $data): int
    {
        $st = self::db()->prepare(
            'INSERT INTO tickets (order_item_id, code, seat_label, holder_name, qr_payload, pdf_path) VALUES (?, ?, ?, ?, ?, ?)'
        );
        $st->execute([
            $data['order_item_id'],
            $data['code'],
            $data['seat_label'] ?? null,
            $data['holder_name'] ?? null,
            $data['qr_payload'],
            $data['pdf_path'] ?? null,
        ]);
        return (int) self::db()->lastInsertId();
    }

    public static function byOrder(int $orderId): array
    {
        $st = self::db()->prepare(
            'SELECT t.*, oi.order_id, tt.name AS ticket_name, e.title AS event_title, e.starts_at, e.venue, e.city
             FROM tickets t
             JOIN order_items oi ON oi.id = t.order_item_id
             JOIN ticket_types tt ON tt.id = oi.ticket_type_id
             JOIN events e ON e.id = tt.event_id
             WHERE oi.order_id = ?
             ORDER BY t.id ASC'
        );
        $st->execute([$orderId]);
        return $st->fetchAll();
    }

    public static function byUser(int $userId): array
    {
        $st = self::db()->prepare(
            'SELECT t.*, oi.order_id, o.status AS order_status, tt.name AS ticket_name,
                    e.title AS event_title, e.starts_at, e.venue, e.city
             FROM tickets t
             JOIN order_items oi ON oi.id = t.order_item_id
             JOIN orders o ON o.id = oi.order_id
             JOIN ticket_types tt ON tt.id = oi.ticket_type_id
             JOIN events e ON e.id = tt.event_id
             WHERE o.user_id = ? AND o.status = \'paid\'
             ORDER BY e.starts_at DESC, t.id DESC'
        );
        $st->execute([$userId]);
        return $st->fetchAll();
    }

    public static function findByCode(string $code): ?array
    {
        $st = self::db()->prepare(
            'SELECT t.*, oi.order_id, oi.unit_price, o.buyer_name, o.buyer_email, o.status AS order_status, tt.name AS ticket_name,
                    e.title AS event_title, e.starts_at, e.venue, e.city, e.currency, e.producer_id,
                    e.promoter_name, e.promoter_nif, e.age_rating, e.capacity, u.name AS producer_user_name
             FROM tickets t
             JOIN order_items oi ON oi.id = t.order_item_id
             JOIN orders o ON o.id = oi.order_id
             JOIN ticket_types tt ON tt.id = oi.ticket_type_id
             JOIN events e ON e.id = tt.event_id
             LEFT JOIN users u ON u.id = e.producer_id
             WHERE t.code = ?'
        );
        $st->execute([$code]);
        $row = $st->fetch();
        return $row ?: null;
    }

    public static function markUsed(int $id): bool
    {
        $st = self::db()->prepare('UPDATE tickets SET used_at = NOW() WHERE id = ? AND used_at IS NULL');
        $st->execute([$id]);
        return $st->rowCount() > 0;
    }

    public static function updatePdf(int $id, string $path): void
    {
        $st = self::db()->prepare('UPDATE tickets SET pdf_path = ? WHERE id = ?');
        $st->execute([$path, $id]);
    }

    public static function generateCode(): string
    {
        return strtoupper(bin2hex(random_bytes(4)) . '-' . bin2hex(random_bytes(3)));
    }
}
