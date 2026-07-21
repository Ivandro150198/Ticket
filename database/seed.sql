USE eventticket_gb;

-- Password for all seed users: password
SET @pwd = '$2y$10$C8Kb1KS5MDHPVlww/bUD3.AXlULGn7yURoktvrT2Nn92FtjE6vBb6';

INSERT INTO users (name, email, password, phone, role) VALUES
('Admin EventTicket', 'admin@eventticket-gb.local', @pwd, '219210273', 'admin'),
('Produtor Lisboa', 'produtor@eventticket-gb.local', @pwd, '912345678', 'produtor'),
('Cliente Demo', 'cliente@eventticket-gb.local', @pwd, '911111111', 'cliente');

SET @producer = (SELECT id FROM users WHERE email = 'produtor@eventticket-gb.local');

-- Eventos Portugal (EUR) e Guiné-Bissau (XOF)
INSERT INTO events (producer_id, title, slug, description, venue, city, country, currency, starts_at, image, status, featured) VALUES
(@producer, 'MEO Sudoeste 2026', 'meo-sudoeste-2026',
 'Um dos maiores festivais de verão em Portugal regressa à Praia de Zambujeira do Mar, com cartaz de música pop, rock, eletrónica e artistas nacionais e internacionais.',
 'Praia de Zambujeira do Mar', 'Odemira', 'PT', 'EUR', '2026-08-05 16:00:00', 'event-1.svg', 'published', 1),
(@producer, 'Vodafone Paredes de Coura 2026', 'vodafone-paredes-de-coura-2026',
 'Festival icónico às margens do rio Coura, com cartaz independente e alternativa. Ambiente fluvial, camping e concertos ao ar livre.',
 'Praia Fluvial do Taboão', 'Paredes de Coura', 'PT', 'EUR', '2026-08-19 17:00:00', 'event-2.svg', 'published', 1),
(@producer, 'MEO Kalorama 2026', 'meo-kalorama-2026',
 'Festival urbano em Lisboa com headliners internacionais e palcos espalhados pelo Parque da Bela Vista.',
 'Parque da Bela Vista', 'Lisboa', 'PT', 'EUR', '2026-09-04 16:00:00', 'event-3.svg', 'published', 1),
(@producer, 'Festa do Avante! 2026', 'festa-do-avante-2026',
 'A maior festa político-cultural de Portugal, com concertos, teatro, desporto e gastronomia na Quinta da Atalaia.',
 'Quinta da Atalaia', 'Seixal', 'PT', 'EUR', '2026-09-04 14:00:00', 'event-4.svg', 'published', 0),
(@producer, 'Noite de Gumbé — Coliseu dos Recreios', 'noite-de-gumbe-coliseu-lisboa-2026',
 'Grande noite de gumbé e ritmos guineenses em Lisboa, com artistas da diáspora no Coliseu dos Recreios.',
 'Coliseu dos Recreios', 'Lisboa', 'PT', 'EUR', '2026-10-10 21:30:00', 'event-5.svg', 'published', 1),
(@producer, 'Casa da Música — Concertos de Outono', 'casa-da-musica-outono-2026',
 'Ciclo de concertos na Casa da Música, no Porto, com orquestra, jazz e propostas contemporâneas.',
 'Casa da Música — Sala Suggia', 'Porto', 'PT', 'EUR', '2026-11-14 21:00:00', 'event-6.svg', 'published', 0),
(@producer, 'Celebrações do Dia da Independência 2026', 'dia-independencia-guine-bissau-2026',
 'Celebrações do 24 de Setembro — Dia da Independência da Guiné-Bissau — com música tradicional e espetáculos.',
 'Estádio 24 de Setembro', 'Bissau', 'GW', 'XOF', '2026-09-24 09:00:00', 'event-7.svg', 'published', 1),
(@producer, 'Festival de Cultura Guineense no CCFBG', 'festival-cultura-guineense-ccfbg-2026',
 'Encontro de artes, cinema e música no Centro Cultural Franco-Bissau-Guineense.',
 'Centro Cultural Franco-Bissau-Guineense', 'Bissau', 'GW', 'XOF', '2026-10-15 18:00:00', 'event-8.svg', 'published', 1),
(@producer, 'Noite de Gumbé em Bissau', 'noite-de-gumbe-bissau-2026',
 'Noite dedicada ao gumbé e à música popular guineense com bandas locais.',
 'Palácio do Governo — esplanada cultural', 'Bissau', 'GW', 'XOF', '2026-11-07 21:00:00', 'event-1.svg', 'published', 1),
(@producer, 'Encontro de Mandjuandadi', 'encontro-mandjuandadi-bissau-2026',
 'Celebração da tradição Mandjuandadi — cantos, danças e encontros intergeracionais.',
 'Praça dos Heróis Nacionais', 'Bissau', 'GW', 'XOF', '2026-12-05 16:00:00', 'event-2.svg', 'published', 0),
(@producer, 'Carnaval de Bissau 2027', 'carnaval-de-bissau-2027',
 'Desfiles étnicos, máscaras e ritmos nas ruas da capital — uma das maiores celebrações culturais do país.',
 'Avenida Amílcar Cabral / centro da cidade', 'Bissau', 'GW', 'XOF', '2027-02-13 10:00:00', 'event-3.svg', 'published', 1),
(@producer, 'Festival Kanta Na Kasa — edição Bissau', 'kanta-na-kasa-bissau-2027',
 'Festival de música e empreendedorismo cultural com foco em artistas guineenses e público familiar.',
 'Espaço cultural Mandjuandadi', 'Bissau', 'GW', 'XOF', '2027-04-10 17:00:00', 'event-4.svg', 'published', 0);

INSERT INTO ticket_types (event_id, name, price, promo_price, stock, vat_rate) VALUES
(1, 'Geral dia', 65.00, NULL, 2000, 23),
(1, 'Passe 4 dias', 165.00, 149.00, 800, 23),
(2, 'Diário', 55.00, NULL, 1500, 23),
(2, 'Passe festival', 140.00, NULL, 600, 23),
(3, 'Geral', 75.00, NULL, 2500, 23),
(3, 'VIP', 180.00, NULL, 300, 23),
(4, 'Entrada diária', 12.00, NULL, 5000, 23),
(4, 'Passe 3 dias', 25.00, 22.00, 2000, 23),
(5, 'Plateia', 28.00, NULL, 400, 23),
(5, 'Camarote', 45.00, 40.00, 80, 23),
(6, 'Normal', 22.00, NULL, 300, 23),
(6, 'Estudante', 12.00, NULL, 100, 23),
(7, 'Entrada geral', 2500, NULL, 3000, 0),
(7, 'Tribuna', 7500, NULL, 400, 0),
(8, 'Sessão / dia', 1500, NULL, 500, 0),
(8, 'Passe festival', 5000, 4000, 200, 0),
(9, 'Entrada', 3000, NULL, 800, 0),
(9, 'Mesa VIP', 15000, NULL, 40, 0),
(10, 'Entrada livre / apoio', 1000, NULL, 1500, 0),
(10, 'Apoio solidário', 5000, NULL, 300, 0),
(11, 'Zona público', 2000, NULL, 5000, 0),
(11, 'Bancada / camarote', 10000, NULL, 250, 0),
(12, 'Geral', 3500, NULL, 600, 0),
(12, 'VIP', 12000, 10000, 80, 0);

INSERT INTO partners (name, logo, website, active, sort_order) VALUES
('Studio Live', NULL, 'https://example.com', 1, 1),
('SoundWave PT', NULL, 'https://example.com', 1, 2),
('Cultura Africana Hub', NULL, 'https://example.com', 1, 3),
('Lisboa Nights', NULL, 'https://example.com', 1, 4);

INSERT INTO faqs (question, answer, sort_order) VALUES
('Como recebo o meu bilhete?', 'Após o pagamento, recebe o bilhete em PDF com código QR no e-mail e pode descarregá-lo na sua área de pedido.', 1),
('Posso transferir o bilhete?', 'Os bilhetes são nominativos. Contacte o suporte se precisar de alterar o nome do titular.', 2),
('Que métodos de pagamento aceitam?', 'Em Portugal: MB Way, Multibanco e cartão (EUR). Na Guiné-Bissau: Orange Money, transferência UEMOA e cartão (FCFA). Nesta demonstração o pagamento pode ser simulado.', 3),
('Como funciona a validação na porta?', 'O staff lê o código QR do PDF. Cada bilhete só pode ser validado uma vez.', 4),
('Sou produtor. Como publico eventos?', 'Registe-se ou peça acesso de produtor. No painel pode criar eventos em Portugal (EUR) ou Guiné-Bissau (FCFA).', 5);

INSERT INTO newsletter_subscribers (email, accepted) VALUES
('news@example.com', 1);
