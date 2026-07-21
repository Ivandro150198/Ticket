<?php

class AuditService
{
    public static function log(string $action, ?string $entity = null, ?int $entityId = null, array $meta = []): void
    {
        try {
            $st = Database::connection()->prepare(
                'INSERT INTO audit_logs (user_id, action, entity, entity_id, meta, ip) VALUES (?, ?, ?, ?, ?, ?)'
            );
            $st->execute([
                auth_id(),
                $action,
                $entity,
                $entityId,
                $meta ? json_encode($meta, JSON_UNESCAPED_UNICODE) : null,
                $_SERVER['REMOTE_ADDR'] ?? null,
            ]);
        } catch (Throwable $e) {
            // não bloquear fluxo principal
        }
    }

    public static function recent(int $limit = 100): array
    {
        $st = Database::connection()->query(
            'SELECT a.*, u.name AS user_name FROM audit_logs a
             LEFT JOIN users u ON u.id = a.user_id
             ORDER BY a.id DESC LIMIT ' . (int) $limit
        );
        return $st->fetchAll();
    }
}
