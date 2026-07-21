<?php

class Seat extends Model
{
    public static function byEvent(int $eventId): array
    {
        $st = self::db()->prepare('SELECT * FROM seats WHERE event_id = ? ORDER BY row_label, seat_number');
        $st->execute([$eventId]);
        return $st->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $st = self::db()->prepare('SELECT * FROM seats WHERE id = ?');
        $st->execute([$id]);
        $row = $st->fetch();
        return $row ?: null;
    }

    public static function holdMany(array $ids): bool
    {
        if (!$ids) {
            return true;
        }
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $params = array_map('intval', $ids);
        $st = self::db()->prepare(
            "UPDATE seats SET status = 'held' WHERE id IN ($placeholders) AND status = 'available'"
        );
        $st->execute($params);
        return $st->rowCount() === count($ids);
    }

    public static function releaseMany(array $ids): void
    {
        if (!$ids) {
            return;
        }
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $params = array_map('intval', $ids);
        self::db()->prepare(
            "UPDATE seats SET status = 'available' WHERE id IN ($placeholders) AND status = 'held'"
        )->execute($params);
    }

    public static function markSold(int $id): void
    {
        self::db()->prepare("UPDATE seats SET status = 'sold' WHERE id = ?")->execute([$id]);
    }

    public static function generateGrid(int $eventId, ?int $ticketTypeId, array $rows, int $seatsPerRow): void
    {
        $st = self::db()->prepare(
            'INSERT IGNORE INTO seats (event_id, ticket_type_id, row_label, seat_number, status) VALUES (?,?,?,?,?)'
        );
        foreach ($rows as $row) {
            for ($n = 1; $n <= $seatsPerRow; $n++) {
                $st->execute([$eventId, $ticketTypeId, $row, $n, 'available']);
            }
        }
    }
}
