<?php

class Event extends Model
{
    public static function published(int $page = 1, int $perPage = 12): array
    {
        $offset = ($page - 1) * $perPage;
        $st = self::db()->prepare(
            "SELECT e.*,
                (SELECT MIN(COALESCE(tt.promo_price, tt.price)) FROM ticket_types tt WHERE tt.event_id = e.id) AS min_price,
                (SELECT MAX(COALESCE(tt.promo_price, tt.price)) FROM ticket_types tt WHERE tt.event_id = e.id) AS max_price,
                (SELECT COALESCE(SUM(tt.stock), 0) FROM ticket_types tt WHERE tt.event_id = e.id) AS stock_remaining
             FROM events e
             WHERE e.status = 'published' AND e.starts_at >= NOW()
             ORDER BY e.starts_at ASC
             LIMIT {$perPage} OFFSET {$offset}"
        );
        $st->execute();
        return $st->fetchAll();
    }

    public static function countPublished(): int
    {
        return (int) self::db()->query(
            "SELECT COUNT(*) FROM events WHERE status = 'published' AND starts_at >= NOW()"
        )->fetchColumn();
    }

    public static function featured(int $limit = 6): array
    {
        $st = self::db()->prepare(
            "SELECT e.*,
                (SELECT MIN(COALESCE(tt.promo_price, tt.price)) FROM ticket_types tt WHERE tt.event_id = e.id) AS min_price,
                (SELECT MAX(COALESCE(tt.promo_price, tt.price)) FROM ticket_types tt WHERE tt.event_id = e.id) AS max_price,
                (SELECT COALESCE(SUM(tt.stock), 0) FROM ticket_types tt WHERE tt.event_id = e.id) AS stock_remaining
             FROM events e
             WHERE e.status = 'published' AND e.featured = 1 AND e.starts_at >= NOW()
             ORDER BY e.starts_at ASC
             LIMIT ?"
        );
        $st->bindValue(1, $limit, PDO::PARAM_INT);
        $st->execute();
        return $st->fetchAll();
    }

    /** Hero: destaques primeiro, depois datas mais próximas. */
    public static function forHero(int $limit = 6): array
    {
        $st = self::db()->prepare(
            "SELECT e.*,
                (SELECT MIN(COALESCE(tt.promo_price, tt.price)) FROM ticket_types tt WHERE tt.event_id = e.id) AS min_price,
                (SELECT MAX(COALESCE(tt.promo_price, tt.price)) FROM ticket_types tt WHERE tt.event_id = e.id) AS max_price,
                (SELECT COALESCE(SUM(tt.stock), 0) FROM ticket_types tt WHERE tt.event_id = e.id) AS stock_remaining
             FROM events e
             WHERE e.status = 'published' AND e.starts_at >= NOW()
             ORDER BY e.featured DESC, e.starts_at ASC
             LIMIT ?"
        );
        $st->bindValue(1, $limit, PDO::PARAM_INT);
        $st->execute();
        return $st->fetchAll();
    }

    public static function findBySlug(string $slug): ?array
    {
        $st = self::db()->prepare('SELECT * FROM events WHERE slug = ?');
        $st->execute([$slug]);
        $row = $st->fetch();
        return $row ?: null;
    }

    public static function find(int $id): ?array
    {
        $st = self::db()->prepare('SELECT * FROM events WHERE id = ?');
        $st->execute([$id]);
        $row = $st->fetch();
        return $row ?: null;
    }

    public static function byProducer(int $producerId): array
    {
        $st = self::db()->prepare('SELECT * FROM events WHERE producer_id = ? ORDER BY starts_at DESC');
        $st->execute([$producerId]);
        return $st->fetchAll();
    }

    public static function allAdmin(): array
    {
        return self::db()->query(
            'SELECT e.*, u.name AS producer_name FROM events e JOIN users u ON u.id = e.producer_id ORDER BY e.created_at DESC'
        )->fetchAll();
    }

    public static function create(array $data): int
    {
        $st = self::db()->prepare(
            'INSERT INTO events (producer_id, title, slug, description, venue, city, country, currency, starts_at, image, status, featured, promoter_name, promoter_nif, age_rating, capacity)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $country = strtoupper($data['country'] ?? 'PT');
        $st->execute([
            $data['producer_id'],
            $data['title'],
            $data['slug'],
            $data['description'],
            $data['venue'],
            $data['city'],
            $country,
            $data['currency'] ?? currency_for_country($country),
            $data['starts_at'],
            $data['image'] ?? null,
            $data['status'] ?? 'draft',
            $data['featured'] ?? 0,
            $data['promoter_name'] ?? null,
            $data['promoter_nif'] ?? null,
            $data['age_rating'] ?? 'Todos',
            $data['capacity'] ?? null,
        ]);
        return (int) self::db()->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        $country = strtoupper($data['country'] ?? 'PT');
        $st = self::db()->prepare(
            'UPDATE events SET title=?, slug=?, description=?, venue=?, city=?, country=?, currency=?, starts_at=?, image=?, status=?, featured=?, promoter_name=?, promoter_nif=?, age_rating=?, capacity=? WHERE id=?'
        );
        $st->execute([
            $data['title'],
            $data['slug'],
            $data['description'],
            $data['venue'],
            $data['city'],
            $country,
            $data['currency'] ?? currency_for_country($country),
            $data['starts_at'],
            $data['image'] ?? null,
            $data['status'],
            $data['featured'] ?? 0,
            $data['promoter_name'] ?? null,
            $data['promoter_nif'] ?? null,
            $data['age_rating'] ?? 'Todos',
            $data['capacity'] ?? null,
            $id,
        ]);
    }

    public static function delete(int $id): void
    {
        $st = self::db()->prepare('DELETE FROM events WHERE id = ?');
        $st->execute([$id]);
    }

    public static function setStatus(int $id, string $status): void
    {
        $st = self::db()->prepare('UPDATE events SET status = ? WHERE id = ?');
        $st->execute([$status, $id]);
    }

    public static function count(): int
    {
        return (int) self::db()->query('SELECT COUNT(*) FROM events')->fetchColumn();
    }
}
