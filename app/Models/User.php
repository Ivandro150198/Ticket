<?php

class User extends Model
{
    public static function find(int $id): ?array
    {
        $st = self::db()->prepare('SELECT id, name, email, phone, role, created_at FROM users WHERE id = ?');
        $st->execute([$id]);
        $row = $st->fetch();
        return $row ?: null;
    }

    public static function findByEmail(string $email): ?array
    {
        $st = self::db()->prepare('SELECT * FROM users WHERE email = ?');
        $st->execute([$email]);
        $row = $st->fetch();
        return $row ?: null;
    }

    public static function create(array $data): int
    {
        $st = self::db()->prepare(
            'INSERT INTO users (name, email, password, phone, role) VALUES (?, ?, ?, ?, ?)'
        );
        $st->execute([
            $data['name'],
            $data['email'],
            password_hash($data['password'], PASSWORD_DEFAULT),
            $data['phone'] ?? null,
            $data['role'] ?? 'cliente',
        ]);
        return (int) self::db()->lastInsertId();
    }

    public static function all(): array
    {
        return self::db()->query('SELECT id, name, email, phone, role, created_at FROM users ORDER BY id DESC')->fetchAll();
    }

    public static function updateRole(int $id, string $role): void
    {
        $st = self::db()->prepare('UPDATE users SET role = ? WHERE id = ?');
        $st->execute([$role, $id]);
    }

    public static function updatePassword(string $email, string $password): void
    {
        $st = self::db()->prepare('UPDATE users SET password = ? WHERE email = ?');
        $st->execute([password_hash($password, PASSWORD_DEFAULT), $email]);
    }

    public static function count(): int
    {
        return (int) self::db()->query('SELECT COUNT(*) FROM users')->fetchColumn();
    }
}
