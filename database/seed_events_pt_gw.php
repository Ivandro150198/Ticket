<?php

/**
 * Substitui eventos de demonstração por eventos reais PT (EUR) e GW (XOF).
 * php database/seed_events_pt_gw.php
 */
require __DIR__ . '/../config/env.php';
env_load(__DIR__ . '/../.env');
require __DIR__ . '/../app/helpers.php';
require __DIR__ . '/../app/Database.php';

$pdo = Database::connection();
$pdo->exec('SET NAMES utf8mb4');

$producer = (int) $pdo->query("SELECT id FROM users WHERE email = 'produtor@eventticket-gb.local'")->fetchColumn();
if (!$producer) {
    $producer = (int) $pdo->query("SELECT id FROM users WHERE role IN ('produtor','admin') ORDER BY id ASC LIMIT 1")->fetchColumn();
}
if (!$producer) {
    fwrite(STDERR, "Nenhum produtor encontrado. Execute seed.sql primeiro.\n");
    exit(1);
}

$pdo->exec('SET FOREIGN_KEY_CHECKS=0');
foreach (['tickets', 'order_items', 'orders', 'seats', 'ticket_types', 'events'] as $table) {
    $pdo->exec("TRUNCATE TABLE {$table}");
}
$pdo->exec('SET FOREIGN_KEY_CHECKS=1');

$events = [
    // —— Portugal (EUR) — eventos previstos após jul/2026 ——
    [
        'title' => 'MEO Sudoeste 2026',
        'slug' => 'meo-sudoeste-2026',
        'description' => 'Um dos maiores festivais de verão em Portugal regressa à Praia de Zambujeira do Mar, com cartaz de música pop, rock, eletrónica e artistas nacionais e internacionais.',
        'venue' => 'Praia de Zambujeira do Mar',
        'city' => 'Odemira',
        'country' => 'PT',
        'currency' => 'EUR',
        'starts_at' => '2026-08-05 16:00:00',
        'image' => 'event-1.svg',
        'featured' => 1,
        'types' => [
            ['Geral dia', 65.00, null, 2000],
            ['Passe 4 dias', 165.00, 149.00, 800],
        ],
    ],
    [
        'title' => 'Vodafone Paredes de Coura 2026',
        'slug' => 'vodafone-paredes-de-coura-2026',
        'description' => 'Festival icónico às margens do rio Coura, com cartaz independente e alternativa. Ambiente fluvial, camping e concertos ao ar livre.',
        'venue' => 'Praia Fluvial do Taboão',
        'city' => 'Paredes de Coura',
        'country' => 'PT',
        'currency' => 'EUR',
        'starts_at' => '2026-08-19 17:00:00',
        'image' => 'event-2.svg',
        'featured' => 1,
        'types' => [
            ['Diário', 55.00, null, 1500],
            ['Passe festival', 140.00, null, 600],
        ],
    ],
    [
        'title' => 'MEO Kalorama 2026',
        'slug' => 'meo-kalorama-2026',
        'description' => 'Festival urbano em Lisboa com headliners internacionais e palcos espalhados pelo Parque da Bela Vista. Música, gastronomia e experiências.',
        'venue' => 'Parque da Bela Vista',
        'city' => 'Lisboa',
        'country' => 'PT',
        'currency' => 'EUR',
        'starts_at' => '2026-09-04 16:00:00',
        'image' => 'event-3.svg',
        'featured' => 1,
        'types' => [
            ['Geral', 75.00, null, 2500],
            ['VIP', 180.00, null, 300],
        ],
    ],
    [
        'title' => 'Festa do Avante! 2026',
        'slug' => 'festa-do-avante-2026',
        'description' => 'A maior festa político-cultural de Portugal, com concertos, teatro, desporto e gastronomia na Quinta da Atalaia, Amora (Seixal).',
        'venue' => 'Quinta da Atalaia',
        'city' => 'Seixal',
        'country' => 'PT',
        'currency' => 'EUR',
        'starts_at' => '2026-09-04 14:00:00',
        'image' => 'event-4.svg',
        'featured' => 0,
        'types' => [
            ['Entrada diária', 12.00, null, 5000],
            ['Passe 3 dias', 25.00, 22.00, 2000],
        ],
    ],
    [
        'title' => 'Noite de Gumbé — Coliseu dos Recreios',
        'slug' => 'noite-de-gumbe-coliseu-lisboa-2026',
        'description' => 'Grande noite de gumbé e ritmos guineenses em Lisboa, com artistas da diáspora e convidados especiais no Coliseu dos Recreios.',
        'venue' => 'Coliseu dos Recreios',
        'city' => 'Lisboa',
        'country' => 'PT',
        'currency' => 'EUR',
        'starts_at' => '2026-10-10 21:30:00',
        'image' => 'event-5.svg',
        'featured' => 1,
        'types' => [
            ['Plateia', 28.00, null, 400],
            ['Camarote', 45.00, 40.00, 80],
        ],
    ],
    [
        'title' => 'Casa da Música — Concertos de Outono',
        'slug' => 'casa-da-musica-outono-2026',
        'description' => 'Ciclo de concertos na Casa da Música, no Porto, com orquestra, jazz e propostas contemporâneas na Sala Suggia.',
        'venue' => 'Casa da Música — Sala Suggia',
        'city' => 'Porto',
        'country' => 'PT',
        'currency' => 'EUR',
        'starts_at' => '2026-11-14 21:00:00',
        'image' => 'event-6.svg',
        'featured' => 0,
        'types' => [
            ['Normal', 22.00, null, 300],
            ['Estudante', 12.00, null, 100],
        ],
    ],

    // —— Guiné-Bissau (XOF / FCFA) ——
    [
        'title' => 'Celebrações do Dia da Independência 2026',
        'slug' => 'dia-independencia-guine-bissau-2026',
        'description' => 'Celebrações oficiais e culturais do 24 de Setembro — Dia da Independência da Guiné-Bissau — com desfile, música tradicional, gumbé e espetáculos no Estádio 24 de Setembro.',
        'venue' => 'Estádio 24 de Setembro',
        'city' => 'Bissau',
        'country' => 'GW',
        'currency' => 'XOF',
        'starts_at' => '2026-09-24 09:00:00',
        'image' => 'event-7.svg',
        'featured' => 1,
        'types' => [
            ['Entrada geral', 2500, null, 3000],
            ['Tribuna', 7500, null, 400],
        ],
    ],
    [
        'title' => 'Festival de Cultura Guineense no CCFBG',
        'slug' => 'festival-cultura-guineense-ccfbg-2026',
        'description' => 'Encontro de artes, cinema, música e literatura no Centro Cultural Franco-Bissau-Guineense (CCFBG), com artistas locais e convidados da África Ocidental.',
        'venue' => 'Centro Cultural Franco-Bissau-Guineense',
        'city' => 'Bissau',
        'country' => 'GW',
        'currency' => 'XOF',
        'starts_at' => '2026-10-15 18:00:00',
        'image' => 'event-8.svg',
        'featured' => 1,
        'types' => [
            ['Sessão / dia', 1500, null, 500],
            ['Passe festival', 5000, 4000, 200],
        ],
    ],
    [
        'title' => 'Noite de Gumbé em Bissau',
        'slug' => 'noite-de-gumbe-bissau-2026',
        'description' => 'Noite dedicada ao gumbé e à música popular guineense, com bandas locais, dança e gastronomia no Palácio do Governo / espaço cultural anexado.',
        'venue' => 'Palácio do Governo — esplanada cultural',
        'city' => 'Bissau',
        'country' => 'GW',
        'currency' => 'XOF',
        'starts_at' => '2026-11-07 21:00:00',
        'image' => 'event-1.svg',
        'featured' => 1,
        'types' => [
            ['Entrada', 3000, null, 800],
            ['Mesa VIP', 15000, null, 40],
        ],
    ],
    [
        'title' => 'Encontro de Mandjuandadi',
        'slug' => 'encontro-mandjuandadi-bissau-2026',
        'description' => 'Celebração da tradição Mandjuandadi — cantos, danças e encontros intergeracionais — aberta a famílias e comunidade, no coração de Bissau.',
        'venue' => 'Praça dos Heróis Nacionais',
        'city' => 'Bissau',
        'country' => 'GW',
        'currency' => 'XOF',
        'starts_at' => '2026-12-05 16:00:00',
        'image' => 'event-2.svg',
        'featured' => 0,
        'types' => [
            ['Entrada livre / apoio', 1000, null, 1500],
            ['Apoio solidário', 5000, null, 300],
        ],
    ],
    [
        'title' => 'Carnaval de Bissau 2027',
        'slug' => 'carnaval-de-bissau-2027',
        'description' => 'O Carnaval de Bissau é uma das maiores celebrações culturais da Guiné-Bissau: desfiles étnicos, máscaras, ritmos e grupos de todo o país nas ruas da capital.',
        'venue' => 'Avenida Amílcar Cabral / centro da cidade',
        'city' => 'Bissau',
        'country' => 'GW',
        'currency' => 'XOF',
        'starts_at' => '2027-02-13 10:00:00',
        'image' => 'event-3.svg',
        'featured' => 1,
        'types' => [
            ['Zona público', 2000, null, 5000],
            ['Bancada / camarote', 10000, null, 250],
        ],
    ],
    [
        'title' => 'Festival Kanta Na Kasa — edição Bissau',
        'slug' => 'kanta-na-kasa-bissau-2027',
        'description' => 'Festival de música e empreendedorismo cultural inspirado na edição Kanta Na Kasa, com foco em artistas guineenses, mandjuandadi e público familiar.',
        'venue' => 'Espaço cultural Mandjuandadi',
        'city' => 'Bissau',
        'country' => 'GW',
        'currency' => 'XOF',
        'starts_at' => '2027-04-10 17:00:00',
        'image' => 'event-4.svg',
        'featured' => 0,
        'types' => [
            ['Geral', 3500, null, 600],
            ['VIP', 12000, 10000, 80],
        ],
    ],
];

$insEvent = $pdo->prepare(
    'INSERT INTO events (producer_id, title, slug, description, venue, city, country, currency, starts_at, image, status, featured)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, \'published\', ?)'
);
$insType = $pdo->prepare(
    'INSERT INTO ticket_types (event_id, name, price, promo_price, stock, vat_rate) VALUES (?, ?, ?, ?, ?, ?)'
);

foreach ($events as $ev) {
    $insEvent->execute([
        $producer,
        $ev['title'],
        $ev['slug'],
        $ev['description'],
        $ev['venue'],
        $ev['city'],
        $ev['country'],
        $ev['currency'],
        $ev['starts_at'],
        $ev['image'],
        $ev['featured'],
    ]);
    $eid = (int) $pdo->lastInsertId();
    $vat = $ev['country'] === 'GW' ? 0 : 23;
    foreach ($ev['types'] as $t) {
        $insType->execute([$eid, $t[0], $t[1], $t[2], $t[3], $vat]);
    }
    echo "OK {$ev['country']} {$ev['currency']} — {$ev['title']}\n";
}

echo "\nInseridos " . count($events) . " eventos (PT em EUR, GW em XOF/FCFA).\n";
