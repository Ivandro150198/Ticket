<?php

function absolute_url(string $path = ''): string
{
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['SERVER_PORT'] ?? null) == 443);
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $scheme = $https ? 'https' : 'http';
    return $scheme . '://' . $host . base_url($path);
}

function absolute_path(string $path): string
{
    if (preg_match('#^https?://#i', $path)) {
        return $path;
    }
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['SERVER_PORT'] ?? null) == 443);
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $scheme = $https ? 'https' : 'http';
    return $scheme . '://' . $host . (str_starts_with($path, '/') ? $path : '/' . $path);
}

function seo_defaults(array $overrides = []): array
{
    $site = config('name', 'EventTicket-GB');
    $defaults = [
        'title' => $site . ' — Bilhetes para eventos',
        'description' => 'Compre bilhetes para concertos, festas e eventos em Portugal. Bilhete digital com QR, pagamento seguro e validação na porta.',
        'keywords' => 'bilhetes, eventos, concertos, tickets, EventTicket-GB, Portugal, QR code',
        'image' => absolute_path(asset('img/og-default.svg')),
        'url' => absolute_path(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/'),
        'type' => 'website',
        'robots' => 'index,follow',
        'canonical' => null,
        'jsonld' => [],
    ];
    $seo = array_merge($defaults, array_filter($overrides, static fn($v) => $v !== null));
    if (empty($seo['canonical'])) {
        $seo['canonical'] = $seo['url'];
    }
    $addSuffix = $overrides['title_suffix'] ?? true;
    unset($seo['title_suffix']);
    if ($addSuffix && !str_contains((string) $seo['title'], (string) $site)) {
        $seo['title'] = $seo['title'] . ' | ' . $site;
    }
    return $seo;
}

function seo_excerpt(?string $text, int $length = 160): string
{
    $text = trim(preg_replace('/\s+/', ' ', strip_tags((string) $text)));
    if (mb_strlen($text) <= $length) {
        return $text;
    }
    return rtrim(mb_substr($text, 0, $length - 1), ' .,;:') . '…';
}

function organization_jsonld(): array
{
    return [
        '@context' => 'https://schema.org',
        '@type' => 'Organization',
        'name' => config('name', 'EventTicket-GB'),
        'url' => absolute_url(''),
        'logo' => absolute_path(asset('img/og-default.svg')),
        'email' => env('MAIL_FROM', 'noreply@eventticket-gb.local'),
        'telephone' => '+351219210273',
        'sameAs' => [],
    ];
}

function event_jsonld(array $event, array $types = []): array
{
    $prices = [];
    foreach ($types as $t) {
        $prices[] = TicketType::effectivePrice($t);
    }
    $low = $prices ? min($prices) : null;
    $high = $prices ? max($prices) : null;
    $data = [
        '@context' => 'https://schema.org',
        '@type' => 'Event',
        'name' => $event['title'],
        'description' => seo_excerpt($event['description'], 300),
        'startDate' => date('c', strtotime($event['starts_at'])),
        'eventAttendanceMode' => 'https://schema.org/OfflineEventAttendanceMode',
        'eventStatus' => 'https://schema.org/EventScheduled',
        'image' => [absolute_path(event_image($event['image'] ?? null))],
        'url' => absolute_url('evento/' . $event['slug']),
        'location' => [
            '@type' => 'Place',
            'name' => $event['venue'],
            'address' => [
                '@type' => 'PostalAddress',
                'addressLocality' => $event['city'],
                'addressCountry' => $event['country'] ?? 'PT',
            ],
        ],
        'organizer' => [
            '@type' => 'Organization',
            'name' => config('name', 'EventTicket-GB'),
            'url' => absolute_url(''),
        ],
    ];
    if ($low !== null) {
        $offer = [
            '@type' => 'Offer',
            'url' => absolute_url('evento/' . $event['slug']),
            'priceCurrency' => $event['currency'] ?? 'EUR',
            'availability' => 'https://schema.org/InStock',
            'validFrom' => date('c'),
        ];
        if ($high !== null && abs($high - $low) > 0.001) {
            $offer['price'] = number_format($low, 2, '.', '');
            $data['offers'] = [
                $offer,
                array_merge($offer, ['price' => number_format($high, 2, '.', '')]),
            ];
        } else {
            $offer['price'] = number_format($low, 2, '.', '');
            $data['offers'] = $offer;
        }
    }
    return $data;
}

function send_security_headers(): void
{
    header('X-Frame-Options: SAMEORIGIN');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header("Content-Security-Policy: default-src 'self' https: data: blob: 'unsafe-inline' 'unsafe-eval'; img-src 'self' data: https: blob:; worker-src 'self' blob:; manifest-src 'self'; frame-ancestors 'self'");
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }
}

function config(string $key, $default = null)
{
    static $app, $db;
    if ($app === null) {
        $app = require __DIR__ . '/../config/app.php';
        $db = require __DIR__ . '/../config/db.php';
    }
    $parts = explode('.', $key);
    $root = $parts[0] === 'db' ? $db : $app;
    if ($parts[0] === 'db') {
        array_shift($parts);
    }
    $value = $root;
    foreach ($parts as $part) {
        if (!is_array($value) || !array_key_exists($part, $value)) {
            return $default;
        }
        $value = $value[$part];
    }
    return $value;
}

function base_url(string $path = ''): string
{
    $base = rtrim(config('url', ''), '/');
    $path = ltrim($path, '/');
    return $path === '' ? $base : $base . '/' . $path;
}

function asset(string $path): string
{
    $path = ltrim($path, '/');
    $url = base_url('assets/' . $path);
    $file = __DIR__ . '/../public/assets/' . $path;
    if (is_file($file)) {
        $url .= (str_contains($url, '?') ? '&' : '?') . 'v=' . filemtime($file);
    }
    return $url;
}

function redirect(string $path): void
{
    header('Location: ' . base_url($path));
    exit;
}

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

/** Ícone SVG inline. Ex.: cart, user, login, logout, home, events, help… */
function icon(string $name, string $class = ''): string
{
    $paths = [
        'home' => '<path d="M4 10.5 12 4l8 6.5V20a1 1 0 0 1-1 1h-5v-6H10v6H5a1 1 0 0 1-1-1z"/>',
        'events' => '<rect x="4" y="6" width="16" height="15" rx="2"/><path d="M8 3v4M16 3v4M4 11h16"/>',
        'services' => '<path d="M12 3 4 7v5c0 4.5 3.2 8.2 8 9 4.8-.8 8-4.5 8-9V7l-8-4z"/>',
        'contact' => '<path d="M4 6h16v12H4z"/><path d="m4 7 8 6 8-6"/>',
        'help' => '<circle cx="12" cy="12" r="9"/><path d="M9.5 9.5a2.5 2.5 0 1 1 3.6 2.2c-.8.4-1.6 1-1.6 2.3v.5"/><circle cx="12" cy="17" r=".7" fill="currentColor" stroke="none"/>',
        'user' => '<circle cx="12" cy="8" r="3.5"/><path d="M5 19.5c1.5-3.5 4-5 7-5s5.5 1.5 7 5"/>',
        'admin' => '<path d="M12 3 4.5 6.5v4.8c0 4.6 3.1 8.5 7.5 9.7 4.4-1.2 7.5-5.1 7.5-9.7V6.5L12 3z"/><path d="m9 12 2 2 4-4"/>',
        'producer' => '<path d="M5 10v4M8 8v8M11 6v12M14 9v6M17 7v10M20 11v2"/>',
        'cart' => '<path d="M3 5h2l1.5 10h11L20 8H7"/><circle cx="9.5" cy="19" r="1.4"/><circle cx="16.5" cy="19" r="1.4"/>',
        'login' => '<path d="M10 4H6a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h4"/><path d="m12 16 4-4-4-4"/><path d="M15 12H8"/>',
        'logout' => '<path d="M14 4h4a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-4"/><path d="m10 16-4-4 4-4"/><path d="M14 12H7"/>',
        'menu' => '<path d="M4 7h16M4 12h16M4 17h16"/>',
        'site' => '<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c2.5 2.8 3.8 5.8 3.8 9S14.5 18.2 12 21c-2.5-2.8-3.8-5.8-3.8-9S9.5 5.8 12 3z"/>',
        'download' => '<path d="M12 4v10"/><path d="m8 10 4 4 4-4"/><path d="M5 18h14"/>',
        'close' => '<path d="m6 6 12 12M18 6 6 18"/>',
        'ticket' => '<path d="M4 8a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v2a2 2 0 0 0 0 4v2a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-2a2 2 0 0 0 0-4V8z"/><path d="M10 7v10"/>',
        'search' => '<circle cx="11" cy="11" r="6.5"/><path d="m16 16 4 4"/>',
        'plus' => '<path d="M12 5v14M5 12h14"/>',
        'eye' => '<path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/>',
    ];
    $body = $paths[$name] ?? $paths['help'];
    $class = trim('icon icon-' . $name . ($class !== '' ? ' ' . $class : ''));

    return '<svg class="' . e($class) . '" viewBox="0 0 24 24" width="1.15em" height="1.15em" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . $body . '</svg>';
}

function csrf_token(): string
{
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void
{
    $token = $_POST['_csrf'] ?? '';
    if (!$token || !hash_equals($_SESSION['_csrf'] ?? '', $token)) {
        http_response_code(419);
        exit('Token CSRF inválido.');
    }
}

function flash(string $key, ?string $message = null): ?string
{
    if ($message !== null) {
        $_SESSION['_flash'][$key] = $message;
        return null;
    }
    $value = $_SESSION['_flash'][$key] ?? null;
    unset($_SESSION['_flash'][$key]);
    return $value;
}

function old(string $key, string $default = ''): string
{
    return e($_SESSION['_old'][$key] ?? $default);
}

function store_old(array $data): void
{
    $_SESSION['_old'] = $data;
}

function clear_old(): void
{
    unset($_SESSION['_old']);
}

function auth_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function auth_check(): bool
{
    return auth_user() !== null;
}

function auth_id(): ?int
{
    return auth_user()['id'] ?? null;
}

function auth_role(): ?string
{
    return auth_user()['role'] ?? null;
}

function require_auth(): void
{
    if (!auth_check()) {
        flash('error', 'Inicie sessão para continuar.');
        redirect('login');
    }
}

function require_role(string ...$roles): void
{
    require_auth();
    if (!in_array(auth_role(), $roles, true)) {
        http_response_code(403);
        exit('Acesso negado.');
    }
}

function countries_config(): array
{
    static $cfg = null;
    if ($cfg === null) {
        $cfg = require __DIR__ . '/../config/countries.php';
    }
    return $cfg;
}

function country_meta(string $country): array
{
    $all = countries_config();
    return $all[$country] ?? $all['PT'];
}

function currency_for_country(string $country): string
{
    return country_meta($country)['currency'] ?? 'EUR';
}

function label_country(string $country): string
{
    return country_meta($country)['name'] ?? $country;
}

function payment_methods_for_country(string $country, bool $stripeEnabled = false): array
{
    $methods = country_meta($country)['payments'] ?? [];
    if ($stripeEnabled && isset($methods['cartao'])) {
        $methods['cartao'] = 'Cartão (Stripe)';
    } elseif (isset($methods['cartao'])) {
        $methods['cartao'] = $methods['cartao'] . ' (demonstração)';
    }
    return $methods;
}

function money(float $amount, ?string $currency = null): string
{
    $currency = strtoupper($currency ?: ($_SESSION['cart_currency'] ?? config('currency', 'EUR')));
    if ($currency === 'XOF') {
        return number_format(round($amount), 0, ',', '.') . ' FCFA';
    }
    return '€' . number_format($amount, 2, ',', '.');
}

function format_datetime(string $datetime): string
{
    $dt = new DateTime($datetime);
    $months = [
        1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril',
        5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto',
        9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro',
    ];
    $m = (int) $dt->format('n');
    return $dt->format('d') . ' ' . $months[$m] . ' ' . $dt->format('Y') . ' · ' . $dt->format('H:i');
}

/** Classificações etárias (espetáculos em Portugal). */
function age_ratings(): array
{
    return [
        'Todos' => 'Todos os públicos',
        'M/6' => 'M/6',
        'M/12' => 'M/12',
        'M/14' => 'M/14',
        'M/16' => 'M/16',
        'M/18' => 'M/18',
    ];
}

function label_age_rating(?string $rating): string
{
    $rating = $rating ?: 'Todos';
    return age_ratings()[$rating] ?? $rating;
}

function label_event_status(string $status): string
{
    return match ($status) {
        'draft' => 'Rascunho',
        'pending' => 'Pendente',
        'published' => 'Publicado',
        'cancelled' => 'Cancelado',
        default => $status,
    };
}

function label_order_status(string $status): string
{
    return match ($status) {
        'pending' => 'Pendente',
        'paid' => 'Pago',
        'cancelled' => 'Cancelado',
        default => $status,
    };
}

function label_role(string $role): string
{
    return match ($role) {
        'admin' => 'Administrador',
        'produtor' => 'Produtor',
        'cliente' => 'Cliente',
        default => $role,
    };
}

function label_payment_method(?string $method): string
{
    return match ($method) {
        'simulado' => 'Simulado',
        'mbway' => 'MB Way',
        'multibanco' => 'Multibanco',
        'cartao', 'stripe' => 'Cartão',
        'orange_money' => 'Orange Money',
        'transfer_uemoa' => 'Transferência UEMOA',
        default => $method ? (string) $method : '—',
    };
}

function slugify(string $text): string
{
    $text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
    $text = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $text));
    return trim($text, '-') ?: 'evento';
}

function view(string $name, array $data = [], string $layout = 'layouts/main'): void
{
    if ($layout === 'layouts/main') {
        $seoInput = $data['seo'] ?? [];
        if (!empty($data['title']) && empty($seoInput['title'])) {
            $seoInput['title'] = $data['title'];
        }
        $data['seo'] = seo_defaults($seoInput);
        $data['title'] = $data['seo']['title'];
    }
    extract($data, EXTR_SKIP);
    $contentView = __DIR__ . '/Views/' . $name . '.php';
    if (!is_file($contentView)) {
        http_response_code(500);
        exit('View não encontrada: ' . e($name));
    }
    ob_start();
    require $contentView;
    $content = ob_get_clean();
    require __DIR__ . '/Views/' . $layout . '.php';
}

function json_response(array $data, int $code = 200): void
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function cart(): array
{
    return $_SESSION['cart'] ?? [];
}

function cart_count(): int
{
    $n = 0;
    foreach (cart() as $item) {
        $n += (int) $item['qty'];
    }
    return $n;
}

function cart_total(): float
{
    $total = 0.0;
    foreach (cart() as $item) {
        $total += $item['unit_price'] * $item['qty'];
    }
    return $total;
}

function cart_discount(): float
{
    return (float) ($_SESSION['cart_discount'] ?? 0);
}

function cart_coupon(): ?array
{
    return $_SESSION['cart_coupon'] ?? null;
}

function cart_payable(): float
{
    return max(0, cart_total() - cart_discount());
}

function csv_download(string $filename, array $headers, array $rows): void
{
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    $out = fopen('php://output', 'w');
    fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
    fputcsv($out, $headers, ';');
    foreach ($rows as $row) {
        fputcsv($out, $row, ';');
    }
    fclose($out);
    exit;
}

function event_image(?string $image): string
{
    if ($image) {
        $upload = __DIR__ . '/../public/assets/uploads/' . $image;
        if (is_file($upload)) {
            return asset('uploads/' . $image);
        }
        $seed = __DIR__ . '/../public/assets/img/' . $image;
        if (is_file($seed)) {
            return asset('img/' . $image);
        }
    }
    return asset('img/event-placeholder.svg');
}

function event_price_label(?float $min, ?float $max, ?string $currency = null): string
{
    if ($min === null) {
        return 'Preço a anunciar';
    }
    $tax = ($currency === 'XOF') ? '' : ' <small>+IVA</small>';
    if ($max !== null && abs($max - $min) > 0.001) {
        return money($min, $currency) . ' – ' . money($max, $currency) . $tax;
    }
    return money($min, $currency) . $tax;
}

function event_stock_label(?int $stock): string
{
    $stock = (int) ($stock ?? 0);
    if ($stock <= 0) {
        return 'Esgotado';
    }
    if ($stock === 1) {
        return '1 bilhete restante';
    }
    return $stock . ' bilhetes restantes';
}
