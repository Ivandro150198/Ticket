<?php

class ContactMessage extends Model
{
    public static function create(array $data): int
    {
        $st = self::db()->prepare(
            'INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)'
        );
        $st->execute([$data['name'], $data['email'], $data['subject'], $data['message']]);
        return (int) self::db()->lastInsertId();
    }

    public static function all(): array
    {
        return self::db()->query('SELECT * FROM contact_messages ORDER BY created_at DESC')->fetchAll();
    }
}
