<?php

class HomeController
{
    public function index(): void
    {
        $heroSlides = Event::forHero(6);
        $ogImage = !empty($heroSlides[0]['image'])
            ? absolute_path(event_image($heroSlides[0]['image']))
            : null;

        view('home/index', [
            'title' => 'EventTicket-GB — O seu bilhete para eventos',
            'heroSlides' => $heroSlides,
            'featured' => Event::featured(6),
            'events' => Event::published(1, 8),
            'partners' => Partner::active(),
            'seo' => [
                'title' => 'EventTicket-GB — O seu bilhete para eventos',
                'description' => 'Compre bilhetes para concertos, festas e eventos em Portugal e Guiné-Bissau. PDF com QR único e pagamento por país.',
                'canonical' => absolute_url(''),
                'type' => 'website',
                'image' => $ogImage,
                'jsonld' => [
                    organization_jsonld(),
                    [
                        '@context' => 'https://schema.org',
                        '@type' => 'WebSite',
                        'name' => 'EventTicket-GB',
                        'url' => absolute_url(''),
                        'potentialAction' => [
                            '@type' => 'SearchAction',
                            'target' => absolute_url('eventos'),
                            'query-input' => 'required name=search_term_string',
                        ],
                    ],
                ],
            ],
        ]);
    }
}
