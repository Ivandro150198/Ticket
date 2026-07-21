<?php

class AdminController
{
    private function gate(): void
    {
        require_role('admin');
    }

    public function dashboard(): void
    {
        $this->gate();
        view('admin/dashboard', [
            'title' => 'Administração',
            'stats' => [
                'users' => User::count(),
                'events' => Event::count(),
                'orders' => Order::count(),
                'revenue' => Order::revenue(),
                'newsletter' => Newsletter::count(),
            ],
            'events' => Event::allAdmin(),
            'orders' => array_slice(Order::all(), 0, 10),
        ], 'layouts/panel');
    }

    public function users(): void
    {
        $this->gate();
        view('admin/users', [
            'title' => 'Utilizadores',
            'users' => User::all(),
        ], 'layouts/panel');
    }

    public function updateUserRole(): void
    {
        $this->gate();
        verify_csrf();
        $id = (int) ($_POST['user_id'] ?? 0);
        $role = $_POST['role'] ?? 'cliente';
        if (!in_array($role, ['cliente', 'produtor', 'admin'], true)) {
            flash('error', 'Perfil inválido.');
            redirect('admin/utilizadores');
        }
        if ($id === auth_id() && $role !== 'admin') {
            flash('error', 'Não pode remover o seu próprio acesso de admin.');
            redirect('admin/utilizadores');
        }
        User::updateRole($id, $role);
        flash('success', 'Perfil atualizado.');
        redirect('admin/utilizadores');
    }

    public function events(): void
    {
        $this->gate();
        view('admin/events', [
            'title' => 'Eventos',
            'events' => Event::allAdmin(),
        ], 'layouts/panel');
    }

    public function setEventStatus(): void
    {
        $this->gate();
        verify_csrf();
        $id = (int) ($_POST['event_id'] ?? 0);
        $status = $_POST['status'] ?? 'draft';
        if (!in_array($status, ['draft', 'pending', 'published', 'cancelled'], true)) {
            flash('error', 'Estado inválido.');
            redirect('admin/eventos');
        }
        Event::setStatus($id, $status);
        flash('success', 'Estado do evento atualizado.');
        redirect('admin/eventos');
    }

    public function partners(): void
    {
        $this->gate();
        view('admin/partners', [
            'title' => 'Parceiros',
            'partners' => Partner::all(),
        ], 'layouts/panel');
    }

    public function addPartner(): void
    {
        $this->gate();
        verify_csrf();
        Partner::create([
            'name' => trim($_POST['name'] ?? ''),
            'website' => trim($_POST['website'] ?? ''),
            'active' => 1,
            'sort_order' => (int) ($_POST['sort_order'] ?? 0),
        ]);
        flash('success', 'Parceiro adicionado.');
        redirect('admin/parceiros');
    }

    public function togglePartner(): void
    {
        $this->gate();
        verify_csrf();
        Partner::toggle((int) ($_POST['id'] ?? 0));
        redirect('admin/parceiros');
    }

    public function deletePartner(): void
    {
        $this->gate();
        verify_csrf();
        Partner::delete((int) ($_POST['id'] ?? 0));
        flash('success', 'Parceiro removido.');
        redirect('admin/parceiros');
    }

    public function newsletter(): void
    {
        $this->gate();
        view('admin/newsletter', [
            'title' => 'Newsletter',
            'subscribers' => Newsletter::all(),
        ], 'layouts/panel');
    }

    public function deleteSubscriber(): void
    {
        $this->gate();
        verify_csrf();
        Newsletter::delete((int) ($_POST['id'] ?? 0));
        redirect('admin/newsletter');
    }

    public function orders(): void
    {
        $this->gate();
        view('admin/orders', [
            'title' => 'Pedidos',
            'orders' => Order::all(),
        ], 'layouts/panel');
    }

    public function messages(): void
    {
        $this->gate();
        view('admin/messages', [
            'title' => 'Mensagens de contacto',
            'messages' => ContactMessage::all(),
        ], 'layouts/panel');
    }

    public function coupons(): void
    {
        $this->gate();
        view('admin/coupons', [
            'title' => 'Cupões',
            'coupons' => Coupon::all(),
        ], 'layouts/panel');
    }

    public function addCoupon(): void
    {
        $this->gate();
        verify_csrf();
        Coupon::create([
            'code' => trim($_POST['code'] ?? ''),
            'type' => $_POST['type'] ?? 'percent',
            'value' => (float) ($_POST['value'] ?? 0),
            'max_uses' => ($_POST['max_uses'] ?? '') !== '' ? (int) $_POST['max_uses'] : null,
            'min_total' => (float) ($_POST['min_total'] ?? 0),
            'expires_at' => $_POST['expires_at'] ?: null,
            'active' => 1,
        ]);
        flash('success', 'Cupão criado.');
        redirect('admin/cupoes');
    }

    public function deleteCoupon(): void
    {
        $this->gate();
        verify_csrf();
        Coupon::delete((int) ($_POST['id'] ?? 0));
        redirect('admin/cupoes');
    }

    public function complaints(): void
    {
        $this->gate();
        view('admin/complaints', [
            'title' => 'Livro de reclamações',
            'complaints' => Complaint::all(),
        ], 'layouts/panel');
    }

    public function complaintStatus(): void
    {
        $this->gate();
        verify_csrf();
        Complaint::setStatus((int) ($_POST['id'] ?? 0), $_POST['status'] ?? 'open');
        redirect('admin/reclamacoes');
    }

    public function audit(): void
    {
        $this->gate();
        view('admin/audit', [
            'title' => 'Auditoria',
            'logs' => AuditService::recent(200),
        ], 'layouts/panel');
    }

    public function refundOrder(): void
    {
        $this->gate();
        verify_csrf();
        $id = (int) ($_POST['order_id'] ?? 0);
        $reason = trim($_POST['reason'] ?? 'Reembolso admin');
        if (Order::refund($id, $reason)) {
            $order = Order::find($id);
            if ($order) {
                MailService::send($order['buyer_email'], 'Reembolso #' . $id, '<p>O pedido foi reembolsado.</p><p>' . e($reason) . '</p>');
            }
            AuditService::log('order.refunded', 'order', $id, ['reason' => $reason, 'by' => 'admin']);
            flash('success', 'Pedido reembolsado.');
        } else {
            flash('error', 'Não foi possível reembolsar.');
        }
        redirect('admin/pedidos');
    }

    public function exportOrders(): void
    {
        $this->gate();
        $rows = [];
        foreach (Order::exportRows() as $r) {
            $rows[] = [$r['id'], $r['buyer_name'], $r['buyer_email'], $r['total'], $r['discount'] ?? 0, $r['status'], $r['payment_method'], $r['created_at']];
        }
        csv_download('pedidos.csv', ['ID', 'Nome', 'Email', 'Total', 'Desconto', 'Estado', 'Pagamento', 'Data'], $rows);
    }

    public function backup(): void
    {
        $this->gate();
        $cfg = require __DIR__ . '/../../config/db.php';
        $file = __DIR__ . '/../../storage/backups/backup_' . date('Ymd_His') . '.sql';
        if (!is_dir(dirname($file))) {
            mkdir(dirname($file), 0775, true);
        }
        $mysqldump = 'C:\\xampp\\mysql\\bin\\mysqldump.exe';
        if (is_file($mysqldump)) {
            $cmd = sprintf(
                '"%s" -u%s %s %s > "%s"',
                $mysqldump,
                escapeshellarg($cfg['username']),
                $cfg['password'] !== '' ? '-p' . escapeshellarg($cfg['password']) : '',
                escapeshellarg($cfg['database']),
                $file
            );
            // Windows-friendly
            $cmd = '"' . $mysqldump . '" -u' . $cfg['username']
                . ($cfg['password'] !== '' ? ' -p' . $cfg['password'] : '')
                . ' ' . $cfg['database'] . ' > "' . $file . '"';
            exec($cmd);
        }
        if (!is_file($file) || filesize($file) < 10) {
            // fallback PHP dump of key tables
            $pdo = Database::connection();
            $tables = ['users', 'events', 'ticket_types', 'orders', 'order_items', 'tickets', 'coupons', 'seats', 'complaints', 'audit_logs'];
            $sql = "-- EventTicket-GB backup " . date('c') . "\n";
            foreach ($tables as $t) {
                try {
                    $rows = $pdo->query('SELECT * FROM `' . $t . '`')->fetchAll(PDO::FETCH_ASSOC);
                } catch (Throwable $e) {
                    continue;
                }
                $sql .= "\n-- TABLE {$t}\n";
                foreach ($rows as $row) {
                    $cols = array_map(fn($c) => '`' . $c . '`', array_keys($row));
                    $vals = array_map(function ($v) use ($pdo) {
                        return $v === null ? 'NULL' : $pdo->quote((string) $v);
                    }, array_values($row));
                    $sql .= 'INSERT INTO `' . $t . '` (' . implode(',', $cols) . ') VALUES (' . implode(',', $vals) . ");\n";
                }
            }
            file_put_contents($file, $sql);
        }
        AuditService::log('db.backup', 'system', null, ['file' => basename($file)]);
        header('Content-Type: application/sql');
        header('Content-Disposition: attachment; filename="' . basename($file) . '"');
        readfile($file);
        exit;
    }
}
