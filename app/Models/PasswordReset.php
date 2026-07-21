<?php

class PasswordReset extends Model
{
    public static function create(string $email): string
    {
        self::db()->prepare('DELETE FROM password_resets WHERE email = ?')->execute([$email]);
        $token = bin2hex(random_bytes(32));
        $st = self::db()->prepare('INSERT INTO password_resets (email, token) VALUES (?, ?)');
        $st->execute([$email, $token]);
        return $token;
    }

    public static function findValid(string $token): ?array
    {
        $st = self::db()->prepare(
            'SELECT * FROM password_resets WHERE token = ? AND created_at >= (NOW() - INTERVAL 1 HOUR)'
        );
        $st->execute([$token]);
        $row = $st->fetch();
        return $row ?: null;
    }

    public static function deleteByEmail(string $email): void
    {
        self::db()->prepare('DELETE FROM password_resets WHERE email = ?')->execute([$email]);
    }
}
