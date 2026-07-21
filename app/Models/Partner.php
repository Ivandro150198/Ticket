<?php

class Partner extends Model
{
    public static function active(): array
    {
        return self::db()->query('SELECT * FROM partners WHERE active = 1 ORDER BY sort_order ASC')->fetchAll();
    }

    public static function all(): array
    {
        return self::db()->query('SELECT * FROM partners ORDER BY sort_order ASC')->fetchAll();
    }

    public static function create(array $data): int
    {
        $st = self::db()->prepare('INSERT INTO partners (name, logo, website, active, sort_order) VALUES (?, ?, ?, ?, ?)');
        $st->execute([
            $data['name'],
            $data['logo'] ?? null,
            $data['website'] ?? null,
            $data['active'] ?? 1,
            $data['sort_order'] ?? 0,
        ]);
        return (int) self::db()->lastInsertId();
    }

    public static function delete(int $id): void
    {
        $st = self::db()->prepare('DELETE FROM partners WHERE id = ?');
        $st->execute([$id]);
    }

    public static function toggle(int $id): void
    {
        self::db()->prepare('UPDATE partners SET active = IF(active=1,0,1) WHERE id = ?')->execute([$id]);
    }
}
