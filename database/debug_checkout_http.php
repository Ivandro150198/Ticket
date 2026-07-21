<?php

/**
 * Simula adicionar ao carrinho + checkout via HTTP (cookie jar).
 */
$base = 'http://localhost/Ticket/public';
$jar = sys_get_temp_dir() . '/etgb_cookies.txt';
@unlink($jar);

function req(string $method, string $url, array $opts = []): array
{
    global $jar;
    $ch = curl_init($url);
    $headers = [];
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_HEADER => true,
        CURLOPT_COOKIEJAR => $jar,
        CURLOPT_COOKIEFILE => $jar,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HEADERFUNCTION => static function ($ch, $header) use (&$headers) {
            $headers[] = $header;
            return strlen($header);
        },
    ]);
    if (!empty($opts['post'])) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($opts['post']));
    }
    $raw = curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headerSize = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    curl_close($ch);
    $body = substr((string) $raw, $headerSize);
    $loc = null;
    foreach ($headers as $h) {
        if (stripos($h, 'Location:') === 0) {
            $loc = trim(substr($h, 9));
        }
    }
    return compact('code', 'body', 'loc');
}

// 1) Home to get session + csrf from a form page
$eventos = req('GET', $base . '/eventos');
echo "GET eventos {$eventos['code']}\n";

// Get event page for add to cart
$show = req('GET', $base . '/evento/chito-kaharam-killer-ao-vivo-no-porto');
echo "GET evento {$show['code']} len=" . strlen($show['body']) . "\n";
if (!preg_match('/name="_csrf" value="([^"]+)"/', $show['body'], $m)) {
    // try login page
    $login = req('GET', $base . '/login');
    preg_match('/name="_csrf" value="([^"]+)"/', $login['body'], $m);
}
$csrf = $m[1] ?? '';
echo "csrf=" . ($csrf ? 'yes' : 'no') . "\n";

if (!$csrf) {
    echo "NO CSRF\n";
    file_put_contents(sys_get_temp_dir() . '/etgb_show.html', $show['body']);
    exit(1);
}

// Find ticket type id from page
preg_match('/name="ticket_type_id" value="(\d+)"/', $show['body'], $tm);
$typeId = $tm[1] ?? '1';
echo "typeId=$typeId\n";

$add = req('POST', $base . '/carrinho/adicionar', [
    'post' => [
        '_csrf' => $csrf,
        'ticket_type_id' => $typeId,
        'qty' => 1,
    ],
]);
echo "POST add {$add['code']} loc={$add['loc']}\n";

// Refresh csrf from cart
$cart = req('GET', $base . '/carrinho');
echo "GET cart {$cart['code']}\n";
preg_match('/name="_csrf" value="([^"]+)"/', $cart['body'], $m2);
$csrf = $m2[1] ?? $csrf;

$checkoutPage = req('GET', $base . '/checkout');
echo "GET checkout {$checkoutPage['code']} loc={$checkoutPage['loc']}\n";
if (preg_match('/name="_csrf" value="([^"]+)"/', $checkoutPage['body'], $m3)) {
    $csrf = $m3[1];
}
if (preg_match('/Forma de pagamento[\s\S]*?<select[^>]*name="payment_method"[^>]*>([\s\S]*?)<\/select>/', $checkoutPage['body'], $sm)) {
    echo "methods html:\n" . strip_tags(str_replace('</option>', "\n", $sm[1])) . "\n";
} else {
    echo "NO payment select. snippet:\n";
    echo substr(strip_tags($checkoutPage['body']), 0, 400) . "\n";
}

foreach (['simulado', 'cartao'] as $method) {
    $show = req('GET', $base . '/evento/chito-kaharam-killer-ao-vivo-no-porto');
    preg_match('/name="_csrf" value="([^"]+)"/', $show['body'], $mx);
    $csrf = $mx[1] ?? $csrf;
    req('POST', $base . '/carrinho/adicionar', ['post' => ['_csrf' => $csrf, 'ticket_type_id' => $typeId, 'qty' => 1]]);
    $checkoutPage = req('GET', $base . '/checkout');
    preg_match('/name="_csrf" value="([^"]+)"/', $checkoutPage['body'], $my);
    $csrf = $my[1] ?? $csrf;

    $pay = req('POST', $base . '/checkout', [
        'post' => [
            '_csrf' => $csrf,
            'buyer_name' => 'Cliente Teste',
            'buyer_email' => 'cliente@test.local',
            'buyer_phone' => '912345678',
            'payment_method' => $method,
        ],
    ]);
    echo "PAY $method => {$pay['code']} loc={$pay['loc']}\n";
    if ($pay['loc']) {
        $url = str_starts_with($pay['loc'], 'http') ? $pay['loc'] : ('http://localhost' . $pay['loc']);
        $follow = req('GET', $url);
        // may bounce checkout -> eventos
        if ($follow['loc']) {
            $url2 = str_starts_with($follow['loc'], 'http') ? $follow['loc'] : ('http://localhost' . $follow['loc']);
            $follow = req('GET', $url2);
        }
        if (preg_match('/class="flash[^"]*"[^>]*>([^<]+)/', $follow['body'], $err)) {
            echo "  FLASH: {$err[1]}\n";
        } else {
            preg_match('/<title>([^<]+)/', $follow['body'], $t);
            echo "  title: " . ($t[1] ?? '?') . "\n";
            if (preg_match('/Erro|error|Exception|Stack/i', $follow['body'])) {
                echo "  has error keyword\n";
            }
        }
    }
}

// Log last orders
require __DIR__ . '/../config/env.php';
env_load(__DIR__ . '/../.env');
require __DIR__ . '/../app/Database.php';
$rows = Database::connection()->query('SELECT id,status,payment_method,payment_ref,total FROM orders ORDER BY id DESC LIMIT 8')->fetchAll(PDO::FETCH_ASSOC);
echo "\nRecent orders:\n";
foreach ($rows as $r) {
    echo "#{$r['id']} {$r['status']} {$r['payment_method']} ref={$r['payment_ref']} total={$r['total']}\n";
}
