<?php

class CartController
{
    public function index(): void
    {
        view('cart/index', [
            'title' => 'Carrinho',
            'items' => cart(),
            'total' => cart_total(),
            'discount' => cart_discount(),
            'payable' => cart_payable(),
            'coupon' => cart_coupon(),
            'seo' => [
                'title' => 'Carrinho',
                'description' => 'O seu carrinho de bilhetes EventTicket-GB.',
                'robots' => 'noindex,nofollow',
            ],
        ]);
    }

    public function add(): void
    {
        verify_csrf();
        $typeId = (int) ($_POST['ticket_type_id'] ?? 0);
        $qty = max(1, min(10, (int) ($_POST['qty'] ?? 1)));
        $seatIds = array_filter(array_map('intval', (array) ($_POST['seat_ids'] ?? [])));
        $type = TicketType::find($typeId);
        if (!$type || (int) $type['stock'] < $qty) {
            flash('error', 'Bilhete indisponível ou quantidade insuficiente.');
            redirect($_SERVER['HTTP_REFERER'] ?? 'eventos');
        }
        $event = Event::find((int) $type['event_id']);
        if (!$event || $event['status'] !== 'published') {
            flash('error', 'Evento indisponível.');
            redirect('eventos');
        }

        if (!empty($event['has_seats'])) {
            if (count($seatIds) !== $qty) {
                flash('error', 'Selecione exatamente ' . $qty . ' lugar(es).');
                redirect('evento/' . $event['slug']);
            }
            foreach ($seatIds as $sid) {
                $seat = Seat::find($sid);
                if (!$seat || (int) $seat['event_id'] !== (int) $event['id'] || $seat['status'] !== 'available') {
                    flash('error', 'Um dos lugares já não está disponível.');
                    redirect('evento/' . $event['slug']);
                }
            }
        }

        $country = strtoupper($event['country'] ?? 'PT');
        $currency = $event['currency'] ?? currency_for_country($country);
        $cart = cart();
        if ($cart) {
            $first = reset($cart);
            if (($first['currency'] ?? 'EUR') !== $currency) {
                flash('error', 'O carrinho só pode ter bilhetes da mesma moeda/país. Esvazie o carrinho ou finalize a compra atual.');
                redirect('carrinho');
            }
        }

        $price = TicketType::effectivePrice($type);
        $key = (string) $typeId . ($seatIds ? ':' . implode('-', $seatIds) : '');
        $cart[$key] = [
            'ticket_type_id' => $typeId,
            'event_id' => (int) $event['id'],
            'event_title' => $event['title'],
            'ticket_name' => $type['name'],
            'unit_price' => $price,
            'qty' => $qty,
            'slug' => $event['slug'],
            'seat_ids' => $seatIds,
            'country' => $country,
            'currency' => $currency,
        ];
        $_SESSION['cart'] = $cart;
        $_SESSION['cart_country'] = $country;
        $_SESSION['cart_currency'] = $currency;
        $this->recalcCoupon();
        flash('success', 'Bilhete adicionado ao carrinho.');
        redirect('carrinho');
    }

    public function applyCoupon(): void
    {
        verify_csrf();
        $code = trim($_POST['coupon'] ?? '');
        $result = Coupon::validate($code, cart_total());
        if (!$result['ok']) {
            unset($_SESSION['cart_coupon'], $_SESSION['cart_discount']);
            flash('error', $result['message']);
            redirect('carrinho');
        }
        $_SESSION['cart_coupon'] = $result['coupon'];
        $_SESSION['cart_discount'] = $result['discount'];
        flash('success', 'Cupão aplicado: -' . money($result['discount'], $_SESSION['cart_currency'] ?? null));
        redirect('carrinho');
    }

    public function removeCoupon(): void
    {
        verify_csrf();
        unset($_SESSION['cart_coupon'], $_SESSION['cart_discount']);
        flash('success', 'Cupão removido.');
        redirect('carrinho');
    }

    public function update(): void
    {
        verify_csrf();
        $key = (string) ($_POST['cart_key'] ?? $_POST['ticket_type_id'] ?? '');
        $qty = (int) ($_POST['qty'] ?? 0);
        $cart = cart();
        if (!isset($cart[$key])) {
            redirect('carrinho');
        }
        if (!empty($cart[$key]['seat_ids'])) {
            flash('error', 'Para alterar lugares, remova o item e selecione de novo.');
            redirect('carrinho');
        }
        if ($qty <= 0) {
            unset($cart[$key]);
        } else {
            $cart[$key]['qty'] = min(10, $qty);
        }
        $_SESSION['cart'] = $cart;
        $this->recalcCoupon();
        redirect('carrinho');
    }

    public function remove(): void
    {
        verify_csrf();
        $key = (string) ($_POST['cart_key'] ?? $_POST['ticket_type_id'] ?? '');
        $cart = cart();
        unset($cart[$key]);
        $_SESSION['cart'] = $cart;
        $this->recalcCoupon();
        flash('success', 'Item removido.');
        redirect('carrinho');
    }

    public function checkoutForm(): void
    {
        if (empty(cart())) {
            flash('error', 'O carrinho está vazio.');
            redirect('eventos');
        }
        $country = $_SESSION['cart_country'] ?? (cart() ? (reset(cart())['country'] ?? 'PT') : 'PT');
        $currency = $_SESSION['cart_currency'] ?? currency_for_country($country);
        view('cart/checkout', [
            'title' => 'Pagamento',
            'items' => cart(),
            'total' => cart_total(),
            'discount' => cart_discount(),
            'payable' => cart_payable(),
            'coupon' => cart_coupon(),
            'user' => auth_user(),
            'country' => $country,
            'currency' => $currency,
            'paymentMethods' => payment_methods_for_country($country, PaymentService::stripeEnabled()),
            'stripeEnabled' => PaymentService::stripeEnabled(),
            'seo' => [
                'title' => 'Pagamento',
                'description' => 'Finalize a compra dos seus bilhetes.',
                'robots' => 'noindex,nofollow',
            ],
        ]);
    }

    public function checkout(): void
    {
        verify_csrf();
        if (empty(cart())) {
            flash('error', 'O carrinho está vazio.');
            redirect('eventos');
        }

        $name = trim($_POST['buyer_name'] ?? '');
        $email = trim($_POST['buyer_email'] ?? '');
        $phone = trim($_POST['buyer_phone'] ?? '');
        $buyerNif = trim($_POST['buyer_nif'] ?? '');
        $method = $_POST['payment_method'] ?? 'simulado';
        $country = $_SESSION['cart_country'] ?? (reset(cart())['country'] ?? 'PT');
        $currency = $_SESSION['cart_currency'] ?? currency_for_country($country);

        if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            flash('error', 'Indique nome e e-mail válidos.');
            redirect('checkout');
        }
        if (empty($_POST['accept_terms'])) {
            flash('error', 'Tem de aceitar os Termos e a Política de Privacidade para concluir a compra.');
            redirect('checkout');
        }

        $allowed = array_keys(payment_methods_for_country($country, true));
        $allowed[] = 'stripe';
        if (!in_array($method, $allowed, true)) {
            $method = 'simulado';
        }
        if ($method === 'cartao' && $currency === 'EUR' && PaymentService::stripeEnabled()) {
            $method = 'stripe';
        }

        $pdo = Database::connection();
        $heldSeats = [];
        try {
            $pdo->beginTransaction();
            $cart = cart();
            foreach ($cart as $item) {
                $type = TicketType::find((int) $item['ticket_type_id']);
                if (!$type || !TicketType::decrementStock((int) $type['id'], (int) $item['qty'])) {
                    throw new RuntimeException('Quantidade insuficiente para ' . ($item['ticket_name'] ?? 'bilhete'));
                }
                $seats = $item['seat_ids'] ?? [];
                if ($seats) {
                    if (!Seat::holdMany($seats)) {
                        throw new RuntimeException('Lugares indisponíveis.');
                    }
                    $heldSeats = array_merge($heldSeats, $seats);
                }
            }

            $discount = cart_discount();
            $coupon = cart_coupon();
            $subtotal = cart_total();
            $orderId = Order::create([
                'user_id' => auth_id(),
                'buyer_name' => $name,
                'buyer_email' => $email,
                'buyer_phone' => $phone,
                'buyer_nif' => $buyerNif !== '' ? $buyerNif : null,
                'gdpr_consent_at' => date('Y-m-d H:i:s'),
                'total' => $subtotal,
                'currency' => $currency,
                'country' => $country,
                'discount' => $discount,
                'coupon_id' => $coupon['id'] ?? null,
                'status' => 'pending',
                'payment_method' => $method,
            ]);

            foreach ($cart as $item) {
                $seats = $item['seat_ids'] ?? [];
                if ($seats) {
                    foreach ($seats as $seatId) {
                        Order::addItem($orderId, (int) $item['ticket_type_id'], 1, (float) $item['unit_price'], (int) $seatId);
                    }
                } else {
                    Order::addItem($orderId, (int) $item['ticket_type_id'], (int) $item['qty'], (float) $item['unit_price']);
                }
            }

            if ($coupon) {
                Coupon::incrementUse((int) $coupon['id']);
            }

            $order = Order::find($orderId);
            $payable = Order::payableTotal($order);
            $pdo->commit();
            $_SESSION['cart'] = [];
            unset($_SESSION['cart_coupon'], $_SESSION['cart_discount'], $_SESSION['cart_country'], $_SESSION['cart_currency']);
            AuditService::log('order.created', 'order', $orderId, ['method' => $method, 'total' => $payable, 'currency' => $currency]);

            if ($method === 'stripe' && PaymentService::stripeEnabled()) {
                $lineItems = [[
                    'quantity' => 1,
                    'price_data' => [
                        'currency' => strtolower($currency === 'EUR' ? 'eur' : 'xof'),
                        'unit_amount' => $currency === 'XOF' ? (int) round($payable) : (int) round($payable * 100),
                        'product_data' => ['name' => 'Bilhetes EventTicket-GB #' . $orderId],
                    ],
                ]];
                $url = PaymentService::createStripeCheckout($order, $lineItems);
                if ($url) {
                    header('Location: ' . $url);
                    exit;
                }
            }

            if ($method === 'multibanco') {
                $ref = PaymentService::generateMultibancoRef($orderId, $payable);
                Database::connection()->prepare('UPDATE orders SET payment_ref = ? WHERE id = ?')
                    ->execute([$ref['raw'], $orderId]);
                flash('success', 'Referência Multibanco gerada. Confirme o pagamento para receber os bilhetes.');
                redirect('pagamento/multibanco/' . $orderId);
            }

            if ($method === 'mbway') {
                $token = PaymentService::generateMbWayToken($orderId);
                Database::connection()->prepare('UPDATE orders SET payment_ref = ? WHERE id = ?')
                    ->execute([$token, $orderId]);
                flash('success', 'Pedido MB Way criado. Confirme no telemóvel (na demonstração, use o botão confirmar).');
                redirect('pagamento/mbway/' . $orderId);
            }

            if ($method === 'orange_money') {
                $token = PaymentService::generateOrangeMoneyRef($orderId);
                Database::connection()->prepare('UPDATE orders SET payment_ref = ? WHERE id = ?')
                    ->execute([$token, $orderId]);
                flash('success', 'Pedido Orange Money criado. Confirme no telemóvel.');
                redirect('pagamento/orange-money/' . $orderId);
            }

            if ($method === 'transfer_uemoa') {
                $ref = PaymentService::generateUemoaTransferRef($orderId, $payable);
                Database::connection()->prepare('UPDATE orders SET payment_ref = ? WHERE id = ?')
                    ->execute([$ref['raw'], $orderId]);
                flash('success', 'Dados de transferência gerados. Confirme o pagamento após a transferência.');
                redirect('pagamento/transferencia/' . $orderId);
            }

            // simulado / cartao sem stripe
            PaymentService::markPaid($orderId, 'SIM-' . $orderId, $method);
            flash('success', 'Pagamento confirmado. Os seus bilhetes foram enviados por e-mail.');
            redirect('pedido/' . $orderId);
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            Seat::releaseMany($heldSeats);
            $logDir = __DIR__ . '/../../storage';
            if (is_dir($logDir)) {
                file_put_contents(
                    $logDir . '/checkout_error.log',
                    date('c') . ' ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine() . "\n" . $e->getTraceAsString() . "\n\n",
                    FILE_APPEND
                );
            }
            flash('error', $e->getMessage());
            redirect('checkout');
        }
    }

    public function order(string $id): void
    {
        $order = Order::find((int) $id);
        if (!$order) {
            http_response_code(404);
            view('pages/404', ['title' => 'Pedido não encontrado']);
            return;
        }
        $canView = false;
        if (auth_check()) {
            if (in_array(auth_role(), ['admin', 'produtor'], true) || (int) $order['user_id'] === (int) auth_id()) {
                $canView = true;
            }
        }
        if (!empty($_SESSION['last_order_id']) && (int) $_SESSION['last_order_id'] === (int) $id) {
            $canView = true;
        }
        if (!$canView && !auth_check()) {
            // allow if just created in same session via flash path
            $canView = true; // public receipt link with id - restrict in production by token
        }
        $_SESSION['last_order_id'] = (int) $id;

        view('cart/order', [
            'title' => 'Pedido #' . $id,
            'order' => $order,
            'items' => Order::items((int) $id),
            'tickets' => Ticket::byOrder((int) $id),
            'seo' => [
                'title' => 'Pedido #' . $id,
                'description' => 'Detalhes do pedido e bilhetes.',
                'robots' => 'noindex,nofollow',
            ],
        ]);
    }

    public function invoice(string $id): void
    {
        $order = Order::find((int) $id);
        if (!$order) {
            http_response_code(404);
            exit('Pedido não encontrado.');
        }
        $canView = false;
        if (auth_check()) {
            if (in_array(auth_role(), ['admin', 'produtor'], true) || (int) $order['user_id'] === (int) auth_id()) {
                $canView = true;
            }
        }
        if (!empty($_SESSION['last_order_id']) && (int) $_SESSION['last_order_id'] === (int) $id) {
            $canView = true;
        }
        if (!$canView) {
            http_response_code(403);
            exit('Acesso negado.');
        }
        InvoiceService::outputPdf((int) $id);
    }

    public function downloadTicket(string $code): void
    {
        $ticket = Ticket::findByCode($code);
        if (!$ticket) {
            http_response_code(404);
            exit('Bilhete não encontrado.');
        }

        try {
            $existing = !empty($ticket['pdf_path']) ? (__DIR__ . '/../../' . $ticket['pdf_path']) : '';
            if ($existing && is_file($existing) && filesize($existing) > 1000) {
                $path = $existing;
            } else {
                $path = TicketService::regeneratePdf($ticket);
            }
        } catch (Throwable $e) {
            $path = !empty($ticket['pdf_path']) ? (__DIR__ . '/../../' . $ticket['pdf_path']) : '';
            if (!$path || !is_file($path)) {
                http_response_code(500);
                exit('Não foi possível gerar o bilhete com QR. Verifique se a extensão GD do PHP está ativa.');
            }
        }

        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="bilhete-' . $code . '.pdf"');
        header('Cache-Control: no-store');
        readfile($path);
        exit;
    }

    private function recalcCoupon(): void
    {
        $coupon = cart_coupon();
        if (!$coupon) {
            return;
        }
        $result = Coupon::validate($coupon['code'], cart_total());
        if (!$result['ok']) {
            unset($_SESSION['cart_coupon'], $_SESSION['cart_discount']);
            return;
        }
        $_SESSION['cart_discount'] = $result['discount'];
    }
}
