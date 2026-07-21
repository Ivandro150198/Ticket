<?php

class PaymentController
{
    public function multibanco(string $id): void
    {
        $order = Order::find((int) $id);
        if (!$order) {
            http_response_code(404);
            view('pages/404', ['title' => 'Pedido não encontrado']);
            return;
        }
        $payable = Order::payableTotal($order);
        $ref = PaymentService::generateMultibancoRef((int) $id, $payable);
        view('cart/multibanco', [
            'title' => 'Pagamento Multibanco',
            'order' => $order,
            'ref' => $ref,
            'payable' => $payable,
            'seo' => ['title' => 'Pagamento Multibanco', 'robots' => 'noindex,nofollow', 'description' => 'Pagamento Multibanco do pedido.'],
        ]);
    }

    public function mbway(string $id): void
    {
        $order = Order::find((int) $id);
        if (!$order) {
            http_response_code(404);
            view('pages/404', ['title' => 'Pedido não encontrado']);
            return;
        }
        view('cart/mbway', [
            'title' => 'Pagamento MB Way',
            'order' => $order,
            'payable' => Order::payableTotal($order),
            'seo' => ['title' => 'Pagamento MB Way', 'robots' => 'noindex,nofollow', 'description' => 'Pagamento MB Way do pedido.'],
        ]);
    }

    public function orangeMoney(string $id): void
    {
        $order = Order::find((int) $id);
        if (!$order) {
            http_response_code(404);
            view('pages/404', ['title' => 'Pedido não encontrado']);
            return;
        }
        view('cart/orange_money', [
            'title' => 'Pagamento Orange Money',
            'order' => $order,
            'payable' => Order::payableTotal($order),
            'seo' => ['title' => 'Orange Money', 'robots' => 'noindex,nofollow', 'description' => 'Pagamento Orange Money do pedido.'],
        ]);
    }

    public function transferUemoa(string $id): void
    {
        $order = Order::find((int) $id);
        if (!$order) {
            http_response_code(404);
            view('pages/404', ['title' => 'Pedido não encontrado']);
            return;
        }
        $payable = Order::payableTotal($order);
        $ref = PaymentService::generateUemoaTransferRef((int) $id, $payable);
        view('cart/transfer_uemoa', [
            'title' => 'Transferência bancária',
            'order' => $order,
            'ref' => $ref,
            'payable' => $payable,
            'seo' => ['title' => 'Transferência UEMOA', 'robots' => 'noindex,nofollow', 'description' => 'Transferência bancária do pedido.'],
        ]);
    }

    public function confirmLocal(): void
    {
        verify_csrf();
        $orderId = (int) ($_POST['order_id'] ?? 0);
        $order = Order::find($orderId);
        if (!$order || $order['status'] !== 'pending') {
            flash('error', 'Pedido inválido.');
            redirect('');
        }
        PaymentService::markPaid($orderId, $order['payment_ref'] ?? ('LOCAL-' . $orderId), $order['payment_method']);
        flash('success', 'Pagamento confirmado. Bilhetes enviados.');
        redirect('pedido/' . $orderId);
    }

    public function success(): void
    {
        $sessionId = $_GET['session_id'] ?? '';
        if ($sessionId && PaymentService::stripeEnabled()) {
            \Stripe\Stripe::setApiKey(env('STRIPE_SECRET_KEY'));
            $session = \Stripe\Checkout\Session::retrieve($sessionId);
            $orderId = (int) ($session->metadata->order_id ?? $session->client_reference_id ?? 0);
            if ($orderId) {
                PaymentService::markPaid($orderId, $sessionId, 'stripe');
                flash('success', 'Pagamento Stripe confirmado.');
                redirect('pedido/' . $orderId);
            }
        }
        $order = $sessionId ? Order::findByStripeSession($sessionId) : null;
        if ($order) {
            redirect('pedido/' . $order['id']);
        }
        flash('success', 'Pagamento processado.');
        redirect('conta/pedidos');
    }

    public function cancel(): void
    {
        $orderId = (int) ($_GET['order'] ?? 0);
        flash('error', 'Pagamento cancelado. Pode tentar novamente.');
        redirect($orderId ? 'pedido/' . $orderId : 'carrinho');
    }

    public function webhook(): void
    {
        $payload = file_get_contents('php://input') ?: '';
        $sig = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';
        try {
            PaymentService::handleStripeWebhook($payload, $sig);
            http_response_code(200);
            echo json_encode(['ok' => true]);
        } catch (Throwable $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
        }
        exit;
    }
}
