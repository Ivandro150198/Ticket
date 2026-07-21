<?php

class EventController
{
    public function index(): void
    {
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 9;
        $total = Event::countPublished();
        $pages = max(1, (int) ceil($total / $perPage));
        $canonical = absolute_url('eventos' . ($page > 1 ? '?page=' . $page : ''));
        view('events/index', [
            'title' => 'Eventos disponíveis',
            'events' => Event::published($page, $perPage),
            'page' => $page,
            'pages' => $pages,
            'total' => $total,
            'seo' => [
                'title' => $page > 1 ? "Eventos disponíveis — página {$page}" : 'Eventos disponíveis',
                'description' => "Explore {$total} eventos publicados. Compre bilhetes online com QR code e entrega imediata.",
                'canonical' => $canonical,
                'jsonld' => [organization_jsonld()],
            ],
        ]);
    }

    public function show(string $slug): void
    {
        $event = Event::findBySlug($slug);
        if (!$event || $event['status'] !== 'published') {
            http_response_code(404);
            view('pages/404', [
                'title' => 'Evento não encontrado',
                'seo' => [
                    'title' => 'Evento não encontrado',
                    'description' => 'O evento que procura não existe ou já não está disponível.',
                    'robots' => 'noindex,follow',
                ],
            ]);
            return;
        }
        $types = TicketType::byEvent((int) $event['id']);
        $seats = !empty($event['has_seats']) ? Seat::byEvent((int) $event['id']) : [];
        $desc = seo_excerpt($event['description'] . ' · ' . $event['venue'] . ', ' . $event['city'] . ' · ' . format_datetime($event['starts_at']));
        view('events/show', [
            'title' => $event['title'],
            'event' => $event,
            'types' => $types,
            'seats' => $seats,
            'seo' => [
                'title' => $event['title'],
                'description' => $desc,
                'image' => absolute_path(event_image($event['image'] ?? null)),
                'type' => 'website',
                'canonical' => absolute_url('evento/' . $event['slug']),
                'jsonld' => [organization_jsonld(), event_jsonld($event, $types)],
            ],
        ]);
    }
}
