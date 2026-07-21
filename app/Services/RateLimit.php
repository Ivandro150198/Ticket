<?php

class RateLimit
{
    public static function tooManyLoginAttempts(string $ip, int $max = 8, int $minutes = 15): bool
    {
        try {
            $st = Database::connection()->prepare(
                'SELECT COUNT(*) FROM login_attempts WHERE ip = ? AND attempted_at >= (NOW() - INTERVAL ? MINUTE)'
            );
            $st->execute([$ip, $minutes]);
            return (int) $st->fetchColumn() >= $max;
        } catch (Throwable $e) {
            return false;
        }
    }

    public static function hitLogin(string $ip, ?string $email): void
    {
        try {
            $st = Database::connection()->prepare('INSERT INTO login_attempts (ip, email) VALUES (?, ?)');
            $st->execute([$ip, $email]);
        } catch (Throwable $e) {
        }
    }

    public static function clearLogin(string $ip): void
    {
        try {
            Database::connection()->prepare('DELETE FROM login_attempts WHERE ip = ?')->execute([$ip]);
        } catch (Throwable $e) {
        }
    }
}
