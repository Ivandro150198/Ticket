<?php

class Coupon extends Model
{
    public static function findByCode(string $code): ?array
    {
        $st = self::db()->prepare('SELECT * FROM coupons WHERE UPPER(code) = UPPER(?) LIMIT 1');
        $st->execute([trim($code)]);
        $row = $st->fetch();
        return $row ?: null;
    }

    public static function all(): array
    {
        return self::db()->query('SELECT * FROM coupons ORDER BY id DESC')->fetchAll();
    }

    public static function create(array $data): int
    {
        $st = self::db()->prepare(
            'INSERT INTO coupons (code, type, value, max_uses, min_total, expires_at, active) VALUES (?,?,?,?,?,?,?)'
        );
        $st->execute([
            strtoupper($data['code']),
            $data['type'],
            $data['value'],
            $data['max_uses'] ?: null,
            $data['min_total'] ?? 0,
            $data['expires_at'] ?: null,
            $data['active'] ?? 1,
        ]);
        return (int) self::db()->lastInsertId();
    }

    public static function validate(string $code, float $subtotal): array
    {
        $coupon = self::findByCode($code);
        if (!$coupon || !(int) $coupon['active']) {
            return ['ok' => false, 'message' => 'Cupão inválido.'];
        }
        if ($coupon['expires_at'] && strtotime($coupon['expires_at']) < time()) {
            return ['ok' => false, 'message' => 'Cupão expirado.'];
        }
        if ($coupon['max_uses'] !== null && (int) $coupon['used_count'] >= (int) $coupon['max_uses']) {
            return ['ok' => false, 'message' => 'Cupão esgotado.'];
        }
        if ($subtotal < (float) $coupon['min_total']) {
            return ['ok' => false, 'message' => 'Total mínimo para este cupão: ' . money((float) $coupon['min_total'])];
        }
        $discount = $coupon['type'] === 'percent'
            ? round($subtotal * ((float) $coupon['value'] / 100), 2)
            : min((float) $coupon['value'], $subtotal);
        return ['ok' => true, 'coupon' => $coupon, 'discount' => $discount];
    }

    public static function incrementUse(int $id): void
    {
        self::db()->prepare('UPDATE coupons SET used_count = used_count + 1 WHERE id = ?')->execute([$id]);
    }

    public static function delete(int $id): void
    {
        self::db()->prepare('DELETE FROM coupons WHERE id = ?')->execute([$id]);
    }
}
