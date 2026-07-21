<?php

class ProducerController
{
    private function gate(): void
    {
        require_role('produtor', 'admin');
    }

    public function dashboard(): void
    {
        $this->gate();
        $events = auth_role() === 'admin' ? Event::allAdmin() : Event::byProducer(auth_id());
        $orders = auth_role() === 'admin' ? Order::all() : Order::byProducer(auth_id());
        view('producer/dashboard', [
            'title' => 'Painel do Produtor',
            'events' => $events,
            'orders' => $orders,
        ], 'layouts/panel');
    }

    public function events(): void
    {
        $this->gate();
        $events = auth_role() === 'admin' ? Event::allAdmin() : Event::byProducer(auth_id());
        view('producer/events', [
            'title' => 'Gestão de eventos',
            'events' => $events,
        ], 'layouts/panel');
    }

    public function createForm(): void
    {
        $this->gate();
        view('producer/event_form', [
            'title' => 'Novo evento',
            'event' => null,
            'types' => [],
        ], 'layouts/panel');
    }

    public function create(): void
    {
        $this->gate();
        verify_csrf();
        $data = $this->eventPayload();
        if ($data['title'] === '' || $data['description'] === '' || $data['venue'] === '' || $data['city'] === '' || trim($data['starts_at']) === '') {
            flash('error', 'Preencha título, descrição, local, cidade e data.');
            redirect('produtor/eventos/novo');
        }
        if (empty($data['promoter_name']) || empty($data['promoter_nif'])) {
            flash('error', 'Indique o nome e o NIF do promotor (obrigatório para bilhetes).');
            redirect('produtor/eventos/novo');
        }
        $data['producer_id'] = auth_id();
        if (auth_role() === 'admin') {
            $data['status'] = $_POST['status'] ?? 'published';
            $data['featured'] = !empty($_POST['featured']) ? 1 : 0;
        } else {
            $data['status'] = 'pending';
        }
        try {
            $data['image'] = $this->handleImage();
        } catch (Throwable $e) {
            flash('error', $e->getMessage());
            redirect('produtor/eventos/novo');
        }
        try {
            $id = Event::create($data);
            $this->syncTypes($id);
            AuditService::log('event.created', 'event', $id);
            flash('success', auth_role() === 'admin'
                ? 'Evento criado com sucesso.'
                : 'Evento criado. Aguarda aprovação/publicação.');
            redirect('produtor/eventos');
        } catch (Throwable $e) {
            flash('error', 'Não foi possível criar o evento. Verifique se o slug é único.');
            redirect('produtor/eventos/novo');
        }
    }

    public function editForm(string $id): void
    {
        $this->gate();
        $event = $this->ownedEvent((int) $id);
        view('producer/event_form', [
            'title' => 'Editar evento',
            'event' => $event,
            'types' => TicketType::byEvent((int) $id),
        ], 'layouts/panel');
    }

    public function update(string $id): void
    {
        $this->gate();
        verify_csrf();
        $event = $this->ownedEvent((int) $id);
        $data = $this->eventPayload();
        if (auth_role() === 'admin') {
            $data['status'] = $_POST['status'] ?? $event['status'];
            $data['featured'] = !empty($_POST['featured']) ? 1 : 0;
        } else {
            $data['status'] = $event['status'] === 'published' ? 'published' : ($_POST['status'] ?? $event['status']);
            if (in_array($data['status'], ['published'], true) && $event['status'] !== 'published') {
                $data['status'] = 'pending';
            }
            $data['featured'] = (int) $event['featured'];
        }
        $img = $this->handleImage();
        $data['image'] = $img ?: $event['image'];
        Event::update((int) $id, $data);
        $this->syncTypes((int) $id);
        flash('success', 'Evento atualizado.');
        redirect('produtor/eventos/' . $id . '/editar');
    }

    public function delete(string $id): void
    {
        $this->gate();
        verify_csrf();
        $this->ownedEvent((int) $id);
        Event::delete((int) $id);
        flash('success', 'Evento eliminado.');
        redirect('produtor/eventos');
    }

    public function sales(): void
    {
        $this->gate();
        view('producer/sales', [
            'title' => 'Vendas',
            'orders' => Order::byProducer(auth_id()),
        ], 'layouts/panel');
    }

    public function validateForm(): void
    {
        $this->gate();
        view('producer/validate', [
            'title' => 'Validar bilhete',
            'result' => null,
        ], 'layouts/panel');
    }

    public function validateTicket(): void
    {
        $this->gate();
        verify_csrf();
        $code = strtoupper(trim($_POST['code'] ?? ''));
        $code = preg_replace('/^ETGB:/', '', $code);
        $ticket = Ticket::findByCode($code);
        $result = ['ok' => false, 'message' => 'Bilhete não encontrado.', 'ticket' => null];

        if ($ticket) {
            if (auth_role() === 'produtor' && (int) $ticket['producer_id'] !== auth_id()) {
                $result['message'] = 'Este bilhete não pertence aos seus eventos.';
            } elseif ($ticket['used_at']) {
                $result['message'] = 'Bilhete já utilizado em ' . $ticket['used_at'];
                $result['ticket'] = $ticket;
            } else {
                Ticket::markUsed((int) $ticket['id']);
                $ticket = Ticket::findByCode($code);
                $result = ['ok' => true, 'message' => 'Entrada válida — bilhete marcado como usado.', 'ticket' => $ticket];
            }
        }

        if (!empty($_POST['ajax'])) {
            json_response($result);
        }

        view('producer/validate', [
            'title' => 'Validar bilhete',
            'result' => $result,
        ], 'layouts/panel');
    }

    private function ownedEvent(int $id): array
    {
        $event = Event::find($id);
        if (!$event) {
            http_response_code(404);
            exit('Evento não encontrado.');
        }
        if (auth_role() !== 'admin' && (int) $event['producer_id'] !== auth_id()) {
            http_response_code(403);
            exit('Acesso negado.');
        }
        return $event;
    }

    private function eventPayload(): array
    {
        $title = trim($_POST['title'] ?? '');
        $slug = trim($_POST['slug'] ?? '') ?: slugify($title);
        $country = strtoupper(trim($_POST['country'] ?? 'PT'));
        if (!isset(countries_config()[$country])) {
            $country = 'PT';
        }
        return [
            'title' => $title,
            'slug' => $slug,
            'description' => trim($_POST['description'] ?? ''),
            'venue' => trim($_POST['venue'] ?? ''),
            'city' => trim($_POST['city'] ?? ''),
            'country' => $country,
            'currency' => currency_for_country($country),
            'starts_at' => str_replace('T', ' ', $_POST['starts_at'] ?? '') . (strlen($_POST['starts_at'] ?? '') === 16 ? ':00' : ''),
            'featured' => !empty($_POST['featured']) ? 1 : 0,
            'status' => $_POST['status'] ?? 'draft',
            'promoter_name' => trim($_POST['promoter_name'] ?? '') ?: null,
            'promoter_nif' => trim($_POST['promoter_nif'] ?? '') ?: null,
            'age_rating' => in_array($_POST['age_rating'] ?? '', array_keys(age_ratings()), true) ? $_POST['age_rating'] : 'Todos',
            'capacity' => ($_POST['capacity'] ?? '') !== '' ? (int) $_POST['capacity'] : null,
        ];
    }

    public function exportSales(): void
    {
        $this->gate();
        $rows = Order::exportRows(auth_role() === 'admin' ? null : auth_id());
        $out = [];
        foreach ($rows as $r) {
            $out[] = [
                $r['id'],
                $r['event_title'] ?? '',
                $r['buyer_name'],
                $r['buyer_email'],
                $r['total'],
                $r['discount'] ?? 0,
                $r['status'],
                $r['payment_method'],
                $r['created_at'],
            ];
        }
        AuditService::log('export.sales', 'order', null, ['count' => count($out)]);
        csv_download('vendas-eventticket.csv', ['ID', 'Evento', 'Nome', 'Email', 'Total', 'Desconto', 'Estado', 'Pagamento', 'Data'], $out);
    }

    public function generateSeats(string $id): void
    {
        $this->gate();
        verify_csrf();
        $event = $this->ownedEvent((int) $id);
        $rows = array_filter(array_map('trim', explode(',', $_POST['rows'] ?? 'A,B,C,D')));
        $perRow = max(1, min(40, (int) ($_POST['seats_per_row'] ?? 12)));
        $typeId = (int) ($_POST['ticket_type_id'] ?? 0) ?: null;
        Seat::generateGrid((int) $id, $typeId, $rows, $perRow);
        Event::update((int) $id, [
            'title' => $event['title'],
            'slug' => $event['slug'],
            'description' => $event['description'],
            'venue' => $event['venue'],
            'city' => $event['city'],
            'country' => $event['country'] ?? 'PT',
            'currency' => $event['currency'] ?? 'EUR',
            'starts_at' => $event['starts_at'],
            'image' => $event['image'],
            'status' => $event['status'],
            'featured' => $event['featured'],
        ]);
        Database::connection()->prepare('UPDATE events SET has_seats = 1 WHERE id = ?')->execute([(int) $id]);
        flash('success', 'Mapa de lugares gerado.');
        redirect('produtor/eventos/' . $id . '/editar');
    }

    public function offlineValidate(): void
    {
        $this->gate();
        view('producer/validate_offline', [
            'title' => 'Validação sem rede',
        ], 'layouts/panel');
    }

    private function handleImage(): ?string
    {
        if (empty($_FILES['image']['tmp_name'])) {
            return null;
        }
        return ImageService::storeUpload(
            $_FILES['image'],
            __DIR__ . '/../../public/assets/uploads',
            (int) config('upload_max_mb', 5)
        );
    }

    private function syncTypes(int $eventId): void
    {
        $names = $_POST['type_name'] ?? [];
        $prices = $_POST['type_price'] ?? [];
        $promos = $_POST['type_promo'] ?? [];
        $stocks = $_POST['type_stock'] ?? [];
        $ids = $_POST['type_id'] ?? [];

        foreach ($names as $i => $name) {
            $name = trim($name);
            if ($name === '') {
                continue;
            }
            $payload = [
                'event_id' => $eventId,
                'name' => $name,
                'price' => (float) ($prices[$i] ?? 0),
                'promo_price' => ($promos[$i] ?? '') !== '' ? (float) $promos[$i] : null,
                'stock' => (int) ($stocks[$i] ?? 0),
                'vat_rate' => 23,
            ];
            $tid = (int) ($ids[$i] ?? 0);
            if ($tid > 0) {
                $existing = TicketType::find($tid);
                if ($existing && (int) $existing['event_id'] === $eventId) {
                    TicketType::update($tid, $payload);
                }
            } else {
                TicketType::create($payload);
            }
        }
    }
}
