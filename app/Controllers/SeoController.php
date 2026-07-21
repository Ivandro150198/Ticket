<?php

class SeoController
{
    public function sitemap(): void
    {
        $urls = [
            ['loc' => absolute_url(''), 'changefreq' => 'daily', 'priority' => '1.0'],
            ['loc' => absolute_url('eventos'), 'changefreq' => 'daily', 'priority' => '0.9'],
            ['loc' => absolute_url('servicos'), 'changefreq' => 'monthly', 'priority' => '0.7'],
            ['loc' => absolute_url('contacto'), 'changefreq' => 'monthly', 'priority' => '0.6'],
            ['loc' => absolute_url('faq'), 'changefreq' => 'monthly', 'priority' => '0.6'],
            ['loc' => absolute_url('postos-de-venda'), 'changefreq' => 'monthly', 'priority' => '0.5'],
            ['loc' => absolute_url('termos'), 'changefreq' => 'yearly', 'priority' => '0.3'],
            ['loc' => absolute_url('privacidade'), 'changefreq' => 'yearly', 'priority' => '0.3'],
            ['loc' => absolute_url('cookies'), 'changefreq' => 'yearly', 'priority' => '0.3'],
            ['loc' => absolute_url('resolucao-litigios'), 'changefreq' => 'yearly', 'priority' => '0.4'],
            ['loc' => absolute_url('livro-de-reclamacoes'), 'changefreq' => 'yearly', 'priority' => '0.4'],
        ];

        try {
            $events = Event::published(1, 500);
            foreach ($events as $ev) {
                $urls[] = [
                    'loc' => absolute_url('evento/' . $ev['slug']),
                    'changefreq' => 'weekly',
                    'priority' => '0.8',
                    'lastmod' => !empty($ev['updated_at'])
                        ? date('Y-m-d', strtotime($ev['updated_at']))
                        : date('Y-m-d', strtotime($ev['starts_at'])),
                ];
            }
        } catch (Throwable $e) {
            // BD indisponível: sitemap mínimo
        }

        header('Content-Type: application/xml; charset=utf-8');
        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach ($urls as $u) {
            echo "  <url>\n";
            echo '    <loc>' . htmlspecialchars($u['loc'], ENT_XML1) . "</loc>\n";
            if (!empty($u['lastmod'])) {
                echo '    <lastmod>' . htmlspecialchars($u['lastmod'], ENT_XML1) . "</lastmod>\n";
            }
            echo '    <changefreq>' . htmlspecialchars($u['changefreq'], ENT_XML1) . "</changefreq>\n";
            echo '    <priority>' . htmlspecialchars($u['priority'], ENT_XML1) . "</priority>\n";
            echo "  </url>\n";
        }
        echo '</urlset>';
        exit;
    }

    public function robots(): void
    {
        header('Content-Type: text/plain; charset=utf-8');
        $base = rtrim((string) config('url', ''), '/');
        $sitemap = absolute_url('sitemap.xml');
        $paths = [
            '/admin', '/produtor', '/conta', '/carrinho', '/checkout',
            '/pedido/', '/pagamento/', '/login', '/registo', '/recuperar',
            '/repor-senha', '/bilhete/',
        ];
        echo "User-agent: *\n";
        echo "Allow: /\n";
        foreach ($paths as $p) {
            echo 'Disallow: ' . $base . $p . "\n";
        }
        echo "\nSitemap: {$sitemap}\n";
        exit;
    }
}
