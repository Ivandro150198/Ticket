<?php

class PwaController
{
    public function manifest(): void
    {
        $base = rtrim((string) config('url', ''), '/') . '/';
        $manifest = [
            'id' => $base,
            'name' => 'EventTicket-GB',
            'short_name' => 'EventTicket',
            'description' => 'Bilhetes para eventos em Portugal e Guiné-Bissau. Compra, QR e validação.',
            'lang' => 'pt-PT',
            'dir' => 'ltr',
            'start_url' => $base,
            'scope' => $base,
            'display' => 'standalone',
            'display_override' => ['standalone', 'minimal-ui'],
            'orientation' => 'any',
            'background_color' => '#0c1016',
            'theme_color' => '#c45c26',
            'categories' => ['entertainment', 'shopping', 'lifestyle'],
            'icons' => [
                [
                    'src' => absolute_path(asset('img/icon-192.png')),
                    'sizes' => '192x192',
                    'type' => 'image/png',
                    'purpose' => 'any',
                ],
                [
                    'src' => absolute_path(asset('img/icon-512.png')),
                    'sizes' => '512x512',
                    'type' => 'image/png',
                    'purpose' => 'any',
                ],
                [
                    'src' => absolute_path(asset('img/icon-512.png')),
                    'sizes' => '512x512',
                    'type' => 'image/png',
                    'purpose' => 'maskable',
                ],
            ],
            'shortcuts' => [
                [
                    'name' => 'Eventos',
                    'short_name' => 'Eventos',
                    'url' => $base . 'eventos',
                    'icons' => [['src' => absolute_path(asset('img/icon-192.png')), 'sizes' => '192x192']],
                ],
                [
                    'name' => 'Carrinho',
                    'short_name' => 'Carrinho',
                    'url' => $base . 'carrinho',
                    'icons' => [['src' => absolute_path(asset('img/icon-192.png')), 'sizes' => '192x192']],
                ],
                [
                    'name' => 'Os meus bilhetes',
                    'short_name' => 'Bilhetes',
                    'url' => $base . 'conta/bilhetes',
                    'icons' => [['src' => absolute_path(asset('img/icon-192.png')), 'sizes' => '192x192']],
                ],
            ],
            'prefer_related_applications' => false,
        ];

        header('Content-Type: application/manifest+json; charset=utf-8');
        header('Cache-Control: public, max-age=3600');
        echo json_encode($manifest, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        exit;
    }

    public function serviceWorker(): void
    {
        $base = rtrim((string) config('url', ''), '/');
        $version = 'etgb-v1';
        $css = asset('css/app.css');
        $js = asset('js/app.js');
        $icon = asset('img/icon-192.png');

        header('Content-Type: application/javascript; charset=utf-8');
        header('Service-Worker-Allowed: ' . $base . '/');
        header('Cache-Control: no-cache');

        echo <<<JS
/* EventTicket-GB service worker */
const CACHE = '{$version}';
const BASE = '{$base}';
const PRECACHE = [
  BASE + '/',
  BASE + '/eventos',
  BASE + '/offline',
  '{$css}',
  '{$js}',
  '{$icon}'
];

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE).then((cache) => cache.addAll(PRECACHE)).then(() => self.skipWaiting())
  );
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keys) =>
      Promise.all(keys.filter((k) => k !== CACHE).map((k) => caches.delete(k)))
    ).then(() => self.clients.claim())
  );
});

self.addEventListener('fetch', (event) => {
  const req = event.request;
  if (req.method !== 'GET') return;
  const url = new URL(req.url);
  if (url.origin !== self.location.origin) return;
  if (!url.pathname.startsWith(BASE)) return;

  // Network-first for navigations; cache fallback to offline page
  if (req.mode === 'navigate') {
    event.respondWith(
      fetch(req).then((res) => {
        const copy = res.clone();
        caches.open(CACHE).then((c) => c.put(req, copy));
        return res;
      }).catch(() =>
        caches.match(req).then((hit) => hit || caches.match(BASE + '/offline'))
      )
    );
    return;
  }

  // Cache-first for static assets
  if (url.pathname.includes('/assets/')) {
    event.respondWith(
      caches.match(req).then((hit) =>
        hit || fetch(req).then((res) => {
          const copy = res.clone();
          caches.open(CACHE).then((c) => c.put(req, copy));
          return res;
        })
      )
    );
  }
});
JS;
        exit;
    }

    public function offline(): void
    {
        view('pages/offline', [
            'title' => 'Sem ligação',
            'seo' => [
                'title' => 'Sem ligação | EventTicket-GB',
                'description' => 'Está offline. Religue a internet para continuar.',
                'robots' => 'noindex,nofollow',
            ],
        ]);
    }
}
