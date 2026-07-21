# EventTicket-GB

Plataforma de venda de bilhetes (PHP + MySQL) para **Portugal (EUR)** e **Guiné-Bissau (FCFA/XOF)**, com pagamentos por país, PDF/QR, e-mail, cupões, lugares, área de cliente, painéis e validação offline.

## Requisitos

- XAMPP (Apache + MySQL + PHP 8+)
- Composer
- Extensões PHP: `pdo_mysql`, `gd`, `mbstring`, `fileinfo`

## Instalação

```bash
cd C:\xampp\htdocs\Ticket
composer install
copy .env.example .env
C:\xampp\mysql\bin\mysql.exe -u root < database\schema.sql
C:\xampp\mysql\bin\mysql.exe -u root < database\seed.sql
php database\migrate.php
php tests\smoke_test.php
```

Abrir: [http://localhost/Ticket/public](http://localhost/Ticket/public)

### App no telemóvel (PWA)

No telemóvel (Chrome/Edge/Safari), abra o site e:

- **Android:** menu → **Instalar aplicação** / **Adicionar ao ecrã principal**
- **iPhone:** Safari → Partilhar → **Adicionar ao ecrã principal**

A app abre em ecrã completo (modo standalone), com ícone próprio.

Ver também [DEPLOY.md](DEPLOY.md).

## Contas demo

Password: `password`

| Role | E-mail |
|------|--------|
| Admin | admin@eventticket-gb.local |
| Produtor | produtor@eventticket-gb.local |
| Cliente | cliente@eventticket-gb.local |

Cupões seed: `BEMVINDO10` (10%), `GALA5` (−5€).

## Funcionalidades

- Site público, eventos PT/GW com moeda (€ ou FCFA), carrinho, cupões
- Pagamentos PT: MB Way, Multibanco, cartão · GW: Orange Money, transferência UEMOA, cartão (+ simulado)
- Bilhete **PDF** (Dompdf) + **QR real** (chillerlan) + e-mail (PHPMailer / log local)
- Área cliente: pedidos, bilhetes, reembolso
- Produtor: eventos, lugares, vendas CSV, validação QR + offline
- Admin: users, cupões, reclamações, auditoria, backup, export CSV
- Segurança: CSRF, rate-limit login, headers, validação de uploads
- Livro de reclamações eletrónico

## Estrutura

```
Ticket/
├── public/           # front controller
├── app/              # MVC
├── config/           # app, db, env
├── database/         # schema, seed, migrate.php
├── storage/          # tickets, mail.log, backups
├── tests/            # smoke tests
└── vendor/           # composer
```
