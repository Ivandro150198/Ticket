<?php

return [
    'name' => 'EventTicket-GB',
    'url' => env('APP_URL', '/Ticket/public'),
    'timezone' => 'Europe/Lisbon',
    'currency' => 'EUR',
    'vat_default' => 23.00,
    'mail_from' => env('MAIL_FROM', 'noreply@eventticket-gb.local'),
    'upload_max_mb' => 5,
    'env' => env('APP_ENV', 'local'),
];
