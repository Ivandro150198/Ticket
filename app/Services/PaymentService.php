<?php

use Stripe\Checkout\Session;
use Stripe\Stripe;
use Stripe\Webhook;

class PaymentService
{
    public static function stripeEnabled(): bool
    {
        $key = env('STRIPE_SECRET_KEY', '');
        return is_string($key) && str_starts_with($key, 'sk_');
    }

    public static function createStripeCheckout(array $order, array $lineItems): ?string
    {
        if (!self::stripeEnabled()) {
            return null;
        }
        Stripe::setApiKey(env('STRIPE_SECRET_KEY'));
        $session = Session::create([
            'mode' => 'payment',
            'success_url' => absolute_url('pagamento/sucesso?session_id={CHECKOUT_SESSION_ID}'),
            'cancel_url' => absolute_url('pagamento/cancelado?order=' . $order['id']),
            'customer_email' => $order['buyer_email'],
            'client_reference_id' => (string) $order['id'],
            'metadata' => ['order_id' => (string) $order['id']],
            'line_items' => $lineItems,
        ]);
        Order::setStripeSession((int) $order['id'], $session->id);
        return $session->url;
    }

    public static function handleStripeWebhook(string $payload, string $sigHeader): void
    {
        $secret = env('STRIPE_WEBHOOK_SECRET', '');
        if ($secret) {
            $event = Webhook::constructEvent($payload, $sigHeader, $secret);
        } else {
            $event = json_decode($payload);
            if (!$event) {
                throw new RuntimeException('Webhook inválido');
            }
        }

        $type = is_object($event) ? ($event->type ?? '') : '';
        $obj = is_object($event) ? ($event->data->object ?? null) : null;
        if ($type === 'checkout.session.completed' && $obj) {
            $orderId = (int) ($obj->metadata->order_id ?? $obj->client_reference_id ?? 0);
            if ($orderId) {
                self::markPaid($orderId, $obj->id ?? null, 'stripe');
            }
        }
    }

    public static function generateMultibancoRef(int $orderId, float $amount): array
    {
        $entity = env('PAYMENT_ENTITY', '12345');
        $ref = str_pad((string) (($orderId * 97 + (int) round($amount * 100)) % 1000000000), 9, '0', STR_PAD_LEFT);
        $refFmt = substr($ref, 0, 3) . ' ' . substr($ref, 3, 3) . ' ' . substr($ref, 6, 3);
        return [
            'entity' => $entity,
            'reference' => $refFmt,
            'amount' => $amount,
            'raw' => 'MB:' . $entity . ':' . $ref,
        ];
    }

    public static function generateMbWayToken(int $orderId): string
    {
        return 'MBWAY-' . strtoupper(bin2hex(random_bytes(4))) . '-' . $orderId;
    }

    public static function generateOrangeMoneyRef(int $orderId): string
    {
        return 'OM-' . strtoupper(bin2hex(random_bytes(4))) . '-' . $orderId;
    }

    public static function generateUemoaTransferRef(int $orderId, float $amount): array
    {
        $ref = 'UEMOA-' . str_pad((string) $orderId, 6, '0', STR_PAD_LEFT) . '-' . strtoupper(bin2hex(random_bytes(2)));
        return [
            'bank' => 'Orabank Guiné-Bissau (demo)',
            'iban' => 'GW04 ORAB 0000 0000 1234 5678 9',
            'reference' => $ref,
            'amount' => $amount,
            'raw' => $ref,
        ];
    }

    public static function markPaid(int $orderId, ?string $paymentRef = null, ?string $method = null): void
    {
        $order = Order::find($orderId);
        if (!$order) {
            return;
        }
        $wasPaid = $order['status'] === 'paid';
        if (!$wasPaid) {
            Order::markPaid($orderId, $paymentRef, $method);
        }
        try {
            TicketService::fulfillOrder($orderId);
        } catch (Throwable $e) {
            $log = __DIR__ . '/../../storage/checkout_error.log';
            file_put_contents(
                $log,
                date('c') . ' fulfillOrder #' . $orderId . ': ' . $e->getMessage() . "\n",
                FILE_APPEND
            );
            // Pagamento continua válido; bilhete pode ser regenerado ao abrir /bilhete/{code}
        }
        if (!$wasPaid) {
            AuditService::log('order.paid', 'order', $orderId, ['ref' => $paymentRef, 'method' => $method]);
        }
    }
}
