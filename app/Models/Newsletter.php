<?php

class Newsletter extends Model
{
    public static function subscribe(string $email): bool
    {
        try {
            $st = self::db()->prepare('INSERT INTO newsletter_subscribers (email, accepted) VALUES (?, 1)');
            $st->execute([$email]);
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    public static function all(): array
    {
        return self::db()->query('SELECT * FROM newsletter_subscribers ORDER BY created_at DESC')->fetchAll();
    }

    public static function count(): int
    {
        return (int) self::db()->query('SELECT COUNT(*) FROM newsletter_subscribers')->fetchColumn();
    }

    public static function delete(int $id): void
    {
        self::db()->prepare('DELETE FROM newsletter_subscribers WHERE id = ?')->execute([$id]);
    }
}
