?<?php

/**
 * Prepara cartazes (uploads/events) e imagens para eventos PT/GW.
 * Atualiza a BD com eventos + fotos.
 * php database/fetch_event_images.php
 */

require __DIR__ . '/../config/env.php';
env_load(__DIR__ . '/../.env');
require __DIR__ . '/../app/helpers.php';
require __DIR__ . '/../app/Database.php';

$pdo = Database::connection();
$pdo->exec('SET NAMES utf8mb4');

$uploadDir = __DIR__ . '/../public/assets/uploads/events';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0775, true);
}

function http_get(string $url): string
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 45,
        CURLOPT_USERAGENT => 'EventTicket-GB/1.0 (local demo; image sync)',
        CURLOPT_SSL_VERIFYPEER => true,
    ]);
    $body = curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($body === false || $code >= 400) {
        throw new RuntimeException("Falha HTTP {$code} em {$url}");
    }
    return $body;
}

function extract_og_image(string $html): ?string
{
    if (preg_match('/property=["\']og:image["\']\s+content=["\']([^"\']+)["\']/i', $html, $m)) {
        return html_entity_decode($m[1], ENT_QUOTES, 'UTF-8');
    }
    if (preg_match('/content=["\']([^"\']+)["\']\s+property=["\']og:image["\']/i', $html, $m)) {
        return html_entity_decode($m[1], ENT_QUOTES, 'UTF-8');
    }
    return null;
}

function download_image(string $url, string $destPath): void
{
    $bin = http_get($url);
    if (strlen($bin) < 500) {
        throw new RuntimeException('Imagem demasiado pequena: ' . $url);
    }
    file_put_contents($destPath, $bin);
}

function save_as_jpeg_or_copy(string $srcPath, string $destJpg): void
{
    if (!extension_loaded('gd')) {
        copy($srcPath, $destJpg);
        return;
    }
    $info = @getimagesize($srcPath);
    if (!$info) {
        copy($srcPath, $destJpg);
        return;
    }
    $mime = $info['mime'] ?? '';
    $img = match ($mime) {
        'image/jpeg' => imagecreatefromjpeg($srcPath),
        'image/png' => imagecreatefrompng($srcPath),
        'image/webp' => function_exists('imagecreatefromwebp') ? imagecreatefromwebp($srcPath) : false,
        default => false,
    };
    if (!$img) {
        copy($srcPath, $destJpg);
        return;
    }
    $w = imagesx($img);
    $h = imagesy($img);
    $maxW = 1200;
    if ($w > $maxW) {
        $nw = $maxW;
        $nh = (int) round($h * ($maxW / $w));
        $dst = imagecreatetruecolor($nw, $nh);
        imagecopyresampled($dst, $img, 0, 0, 0, 0, $nw, $nh, $w, $h);
        imagedestroy($img);
        $img = $dst;
    }
    imagejpeg($img, $destJpg, 85);
    imagedestroy($img);
}

// Eventos PT/EUR – cartazes em public/assets/uploads/events/
$ptEvents = [
    [
        'title' => 'Chito Kaharam Killer – Ao Vivo no Porto',
        'slug' => 'chito-kaharam-killer-ao-vivo-no-porto',
        'description' => 'Noite de música e energia com Chito Kaharam Killer na Number One Discoteca, no Porto. Ritmos e animação da cena guineense e da diáspora.',
        'venue' => 'Number One Discoteca',
        'city' => 'Porto',
        'starts_at' => '2026-08-15 23:00:00',
        'featured' => 1,
        'types' => [['Geral', 18.87, null, 200], ['VIP', 23.58, null, 50]],
    ],
    [
    // Se a data ja passou, adia para manter o evento visivel
        'slug' => 'festas-de-quintal-estao-de-volta',
    // Se a data ja passou, adia para manter o evento visivel
        'venue' => 'Quinta da Boavista',
        'city' => 'Lisboa',
        'starts_at' => '2026-07-19 15:00:00',
        'featured' => 1,
        'types' => [['Entrada', 15.00, null, 300]],
    ],
    [
        'title' => 'Tony Dudu regressa a Lisboa – Gumbé e guitarra guineense',
        'slug' => 'tony-dudu-lisboa-gumbe-2026',
        'description' => 'Tony Dudu regressa a Lisboa para uma noite inesquecível de gumbé e guitarra guineense.',
        'venue' => 'Sala de espetáculos – Lisboa',
        'city' => 'Lisboa',
        'starts_at' => '2026-08-27 21:30:00',
        'featured' => 1,
        'types' => [['Geral', 10.00, null, 250]],
    ],
    [
        'title' => 'II Edição – Noite de Gala Nô Sta Djunto',
        'slug' => 'noite-de-gala-no-sta-djunto-2026',
        'description' => 'II Edição da Noite de Gala Nô Sta Djunto. Traje elegante, jantar e espetáculo musical.',
        'venue' => 'Hotel Cascais Miragem',
        'city' => 'Cascais',
        'starts_at' => '2026-08-08 22:00:00',
        'featured' => 1,
        'types' => [['Normal', 70.00, 65.80, 150], ['VIP Mesa', 95.00, 89.00, 40]],
    ],
    [
        'title' => 'Especial Tabasky em Portugal, Lisboa',
        'slug' => 'especial-tabasky-lisboa-2026',
        'description' => 'Celebração especial do Tabasky em Lisboa com música, gastronomia e comunidade.',
        'venue' => 'Centro Cultural de Belém',
        'city' => 'Lisboa',
        'starts_at' => '2026-09-30 22:00:00',
        'featured' => 0,
        'types' => [['Entrada', 20.00, 18.80, 180]],
    ],
    [
        'title' => 'Black Woman – Power Collective',
        'slug' => 'black-woman-power-collective',
        'description' => 'Espetáculo que celebra a força, a arte e a voz das mulheres negras.',
        'venue' => 'Teatro Politeama',
        'city' => 'Lisboa',
        'starts_at' => '2026-10-12 23:00:00',
        'featured' => 1,
        'types' => [['Geral', 22.00, null, 220]],
    ],
    [
        'title' => 'Cruzeiro Talentos Guineenses',
        'slug' => 'cruzeiro-talentos-guineenses-2026',
        'description' => 'Noite a bordo com os melhores talentos da música guineense.',
        'venue' => 'Terminal de Cruzeiros',
        'city' => 'Lisboa',
        'starts_at' => '2026-10-05 20:30:00',
        'featured' => 1,
        'types' => [['Pista', 41.70, null, 100], ['Camarote', 57.14, null, 40]],
    ],
    [
        'title' => 'Show de Stand-up –Oi Vidas�?�',
        'slug' => 'stand-up-oi-vidas-2026',
        'description' => 'Humor afiado e histórias do quotidiano num espetáculo de stand-up imperdível.',
        'venue' => 'Clube de Comédia',
        'city' => 'Lisboa',
        'starts_at' => '2026-09-12 21:00:00',
        'featured' => 0,
        'types' => [['Plateia', 15.00, null, 120]],
    ],
    [
        'title' => 'Justino Delgado – ritmos da Guiné-Bissau no Porto',
        'slug' => 'justino-delgado-porto-2026',
        'description' => 'Justino Delgado leva os ritmos da Guiné-Bissau à cidade do Porto.',
        'venue' => 'Sala de concertos – Porto',
        'city' => 'Porto',
        'starts_at' => '2026-09-20 22:00:00',
        'featured' => 0,
        'types' => [['Geral', 30.00, 26.24, 200]],
    ],
    [
        'title' => 'Negrinho OG – Em Portugal pela primeira vez!',
        'slug' => 'negrinho-og-portugal-2026',
        'description' => 'Negrinho OG em Portugal pela primeira vez – noite de música e energia.',
        'venue' => 'Lisboa',
        'city' => 'Lisboa',
        'starts_at' => '2026-11-08 22:00:00',
        'featured' => 0,
        'types' => [['Geral', 20.00, null, 250]],
    ],
];

// Eventos Guiné-Bissau – imagens de domínio público / Wikimedia quando possível
$gwEvents = [
    [
        'title' => 'Celebrações do Dia da Independência 2026',
        'slug' => 'dia-independencia-guine-bissau-2026',
        'description' => 'Celebrações do 24 de Setembro – Dia da Independência da Guiné-Bissau – com música tradicional, desfile e espetáculos em Bissau.',
        'venue' => 'Estádio 24 de Setembro',
        'city' => 'Bissau',
        'starts_at' => '2026-09-24 09:00:00',
        'featured' => 1,
        'image_url' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/0/00/Flag_of_Guinea-Bissau.svg/1280px-Flag_of_Guinea-Bissau.svg.png',
        'file' => 'gw-independencia.jpg',
        'types' => [['Entrada geral', 2500, null, 3000], ['Tribuna', 7500, null, 400]],
    ],
    [
        'title' => 'Carnaval de Bissau 2027',
        'slug' => 'carnaval-de-bissau-2027',
        'description' => 'O Carnaval de Bissau: desfiles étnicos, máscaras e ritmos nas ruas da capital – uma das maiores celebrações culturais do país.',
        'venue' => 'Avenida Amílcar Cabral',
        'city' => 'Bissau',
        'starts_at' => '2027-02-13 10:00:00',
        'featured' => 1,
        // Foto Wikimedia: carnival / West Africa cultural (fallback to GW flag if fails)
        'image_url' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/8/89/Bissau_Avenue.jpg/1280px-Bissau_Avenue.jpg',
        'file' => 'gw-carnaval.jpg',
        'types' => [['Zona público', 2000, null, 5000], ['Bancada', 10000, null, 250]],
    ],
    [
        'title' => 'Noite de Gumbé em Bissau',
        'slug' => 'noite-de-gumbe-bissau-2026',
        'description' => 'Noite dedicada ao gumbé e à música popular guineense, com bandas locais em Bissau.',
        'venue' => 'Palácio do Governo – esplanada cultural',
        'city' => 'Bissau',
        'starts_at' => '2026-11-07 21:00:00',
        'featured' => 1,
        'image_url' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/4/4e/Pra%C3%A7a_dos_Her%C3%B3is_Nacionais.jpg/1280px-Pra%C3%A7a_dos_Her%C3%B3is_Nacionais.jpg',
        'file' => 'gw-gumbe.jpg',
        'types' => [['Entrada', 3000, null, 800], ['Mesa VIP', 15000, null, 40]],
    ],
    [
        'title' => 'Festival de Cultura no CCFBG',
        'slug' => 'festival-cultura-ccfbg-2026',
        'description' => 'Encontro de artes, cinema e música no Centro Cultural Franco-Bissau-Guineense em Bissau.',
        'venue' => 'Centro Cultural Franco-Bissau-Guineense',
        'city' => 'Bissau',
        'starts_at' => '2026-10-15 18:00:00',
        'featured' => 1,
        'image_url' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/9/91/Map_of_Guinea-Bissau.svg/1280px-Map_of_Guinea-Bissau.svg.png',
        'file' => 'gw-ccfbg.jpg',
        'types' => [['Sessão / dia', 1500, null, 500], ['Passe festival', 5000, 4000, 200]],
    ],
];

$producer = (int) $pdo->query("SELECT id FROM users WHERE email = 'produtor@eventticket-gb.local'")->fetchColumn();
if (!$producer) {
    $producer = (int) $pdo->query("SELECT id FROM users WHERE role IN ('produtor','admin') ORDER BY id LIMIT 1")->fetchColumn();
}

echo "A limpar eventos antigos...\n";
$pdo->exec('SET FOREIGN_KEY_CHECKS=0');
foreach (['tickets', 'order_items', 'orders', 'seats', 'ticket_types', 'events'] as $t) {
    $pdo->exec("TRUNCATE TABLE {$t}");
}
$pdo->exec('SET FOREIGN_KEY_CHECKS=1');

$insE = $pdo->prepare(
    'INSERT INTO events (producer_id, title, slug, description, venue, city, country, currency, starts_at, image, status, featured)
     VALUES (?,?,?,?,?,?,?,?,?,?,\'published\',?)'
);
$insT = $pdo->prepare(
    'INSERT INTO ticket_types (event_id, name, price, promo_price, stock, vat_rate) VALUES (?,?,?,?,?,?)'
);

echo "=== Portugal (PT / EUR) ===\n";
foreach ($ptEvents as $ev) {
    $file = $ev['slug'] . '.jpg';
    $abs = $uploadDir . '/' . $file;
    $rel = 'events/' . $file;
    if (is_file($abs)) {
        echo "  OK cartaz local: {$file}\n";
    } else {
        echo "  AVISO sem cartaz local ({$file}) — placeholder\n";
        $rel = 'event-1.svg';
    }

    // Se a data ja passou, adia para manter o evento visivel
    $starts = $ev['starts_at'];
    if (strtotime($starts) < time()) {
        $starts = date('Y-m-d H:i:s', strtotime('+21 days'));
    }

    $insE->execute([
        $producer, $ev['title'], $ev['slug'], $ev['description'], $ev['venue'], $ev['city'],
        'PT', 'EUR', $starts, $rel, $ev['featured'],
    ]);
    $eid = (int) $pdo->lastInsertId();
    foreach ($ev['types'] as $t) {
        $insT->execute([$eid, $t[0], $t[1], $t[2], $t[3], 23]);
    }
    echo "  Evento #{$eid} inserido\n";
}

echo "=== Guiné-Bissau (XOF) ===\n";
foreach ($gwEvents as $ev) {
    $abs = $uploadDir . '/' . $ev['file'];
    $rel = 'events/' . $ev['file'];
    try {
        $tmp = $abs . '.tmp';
        download_image($ev['image_url'], $tmp);
        // SVG/PNG from wikimedia – jpeg if possible
        $info = @getimagesize($tmp);
        if ($info && ($info['mime'] ?? '') === 'image/png' && extension_loaded('gd')) {
            $src = imagecreatefrompng($tmp);
            $w = imagesx($src);
            $h = imagesy($src);
            $dst = imagecreatetruecolor(min(1200, $w), (int) round(min(1200, $w) * $h / $w));
            imagefill($dst, 0, 0, imagecolorallocate($dst, 20, 24, 32));
            imagecopyresampled($dst, $src, 0, 0, 0, 0, imagesx($dst), imagesy($dst), $w, $h);
            imagejpeg($dst, $abs, 85);
            imagedestroy($src);
            imagedestroy($dst);
            @unlink($tmp);
        } else {
            save_as_jpeg_or_copy($tmp, $abs);
            @unlink($tmp);
        }
        echo "OK {$ev['title']}\n";
    } catch (Throwable $e) {
        echo "AVISO {$ev['title']}: {$e->getMessage()}\n";
        // gerar cartaz local simples
        if (extension_loaded('gd')) {
            $im = imagecreatetruecolor(900, 1200);
            $bg = imagecolorallocate($im, 196, 92, 38);
            $fg = imagecolorallocate($im, 255, 255, 255);
            imagefilledrectangle($im, 0, 0, 900, 1200, $bg);
            imagestring($im, 5, 40, 100, 'EventTicket-GB', $fg);
            imagestring($im, 5, 40, 140, substr($ev['title'], 0, 40), $fg);
            imagejpeg($im, $abs, 85);
            imagedestroy($im);
        } else {
            $rel = 'event-7.svg';
        }
    }
    $insE->execute([
        $producer, $ev['title'], $ev['slug'], $ev['description'], $ev['venue'], $ev['city'],
        'GW', 'XOF', $ev['starts_at'], $rel, $ev['featured'],
    ]);
    $eid = (int) $pdo->lastInsertId();
    foreach ($ev['types'] as $t) {
        $insT->execute([$eid, $t[0], $t[1], $t[2], $t[3], 0]);
    }
}

$count = (int) $pdo->query('SELECT COUNT(*) FROM events')->fetchColumn();
$withImg = (int) $pdo->query("SELECT COUNT(*) FROM events WHERE image LIKE 'events/%'")->fetchColumn();
echo "\nConcluído: {$count} eventos, {$withImg} com foto em uploads/events/\n";
echo "Cartazes PT: ficheiros locais em uploads/events/\n";
