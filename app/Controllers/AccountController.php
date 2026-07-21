<?php

class AccountController
{
    public function dashboard(): void
    {
        require_auth();
        view('account/dashboard', [
            'title' => 'A minha conta',
            'orders' => Order::byUser(auth_id()),
            'tickets' => Ticket::byUser(auth_id()),
            'seo' => ['title' => 'A minha conta', 'robots' => 'noindex,nofollow', 'description' => 'Área de cliente EventTicket-GB.'],
        ]);
    }

    public function orders(): void
    {
        require_auth();
        view('account/orders', [
            'title' => 'Os meus pedidos',
            'orders' => Order::byUser(auth_id()),
            'seo' => ['title' => 'Os meus pedidos', 'robots' => 'noindex,nofollow', 'description' => 'Histórico de pedidos.'],
        ]);
    }

    public function tickets(): void
    {
        require_auth();
        view('account/tickets', [
            'title' => 'Os meus bilhetes',
            'tickets' => Ticket::byUser(auth_id()),
            'seo' => ['title' => 'Os meus bilhetes', 'robots' => 'noindex,nofollow', 'description' => 'Os seus bilhetes digitais.'],
        ]);
    }

    public function requestRefund(): void
    {
        require_auth();
        verify_csrf();
        $orderId = (int) ($_POST['order_id'] ?? 0);
        $reason = trim($_POST['reason'] ?? 'Pedido pelo cliente');
        $order = Order::find($orderId);
        if (!$order || (int) $order['user_id'] !== auth_id()) {
            flash('error', 'Pedido não encontrado.');
            redirect('conta/pedidos');
        }
        if ($order['status'] !== 'paid' || $order['refunded_at']) {
            flash('error', 'Este pedido não pode ser reembolsado.');
            redirect('conta/pedidos');
        }
        // Pedido de reembolso: admin/produtor processa; aqui marcamos meta via audit e cancelamos bilhetes unused
        if (Order::refund($orderId, $reason)) {
            MailService::send(
                $order['buyer_email'],
                'Reembolso pedido #' . $orderId,
                '<p>O seu pedido #' . $orderId . ' foi reembolsado.</p><p>Motivo: ' . e($reason) . '</p>'
            );
            AuditService::log('order.refunded', 'order', $orderId, ['reason' => $reason]);
            flash('success', 'Reembolso efetuado. Disponibilidade e lugares restaurados.');
        } else {
            flash('error', 'Não foi possível reembolsar.');
        }
        redirect('conta/pedidos');
    }
}
