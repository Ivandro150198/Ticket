<?php

class TicketType extends Model
{
    public static function byEvent(int $eventId): array
    {
        $st = self::db()->prepare('SELECT * FROM ticket_types WHERE event_id = ? ORDER BY price ASC');
        $st->execute([$eventId]);
        return $st->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $st = self::db()->prepare('SELECT * FROM ticket_types WHERE id = ?');
        $st->execute([$id]);
        $row = $st->fetch();
        return $row ?: null;
    }

    public static function create(array $data): int
    {
        $st = self::db()->prepare(
            'INSERT INTO ticket_types (event_id, name, price, promo_price, stock, vat_rate) VALUES (?, ?, ?, ?, ?, ?)'
        );
        $st->execute([
            $data['event_id'],
            $data['name'],
            $data['price'],
            $data['promo_price'] ?: null,
            $data['stock'],
            $data['vat_rate'] ?? 23,
        ]);
        return (int) self::db()->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        $st = self::db()->prepare(
            'UPDATE ticket_types SET name=?, price=?, promo_price=?, stock=?, vat_rate=? WHERE id=?'
        );
        $st->execute([
            $data['name'],
            $data['price'],
            $data['promo_price'] ?: null,
            $data['stock'],
            $data['vat_rate'] ?? 23,
            $id,
        ]);
    }

    public static function delete(int $id): void
    {
        $st = self::db()->prepare('DELETE FROM ticket_types WHERE id = ?');
        $st->execute([$id]);
    }

    public static function decrementStock(int $id, int $qty): bool
    {
        $st = self::db()->prepare('UPDATE ticket_types SET stock = stock - ? WHERE id = ? AND stock >= ?');
        $st->execute([$qty, $id, $qty]);
        return $st->rowCount() > 0;
    }

    public static function effectivePrice(array $type): float
    {
        if (!empty($type['promo_price']) && (float) $type['promo_price'] > 0) {
            return (float) $type['promo_price'];
        }
        return (float) $type['price'];
    }
}
