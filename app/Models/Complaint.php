<?php

class Complaint extends Model
{
    public static function create(array $data): int
    {
        $st = self::db()->prepare(
            'INSERT INTO complaints (name, email, nif, subject, message) VALUES (?,?,?,?,?)'
        );
        $st->execute([
            $data['name'],
            $data['email'],
            $data['nif'] ?? null,
            $data['subject'],
            $data['message'],
        ]);
        return (int) self::db()->lastInsertId();
    }

    public static function all(): array
    {
        return self::db()->query('SELECT * FROM complaints ORDER BY created_at DESC')->fetchAll();
    }

    public static function setStatus(int $id, string $status): void
    {
        self::db()->prepare('UPDATE complaints SET status = ? WHERE id = ?')->execute([$status, $id]);
    }
}
