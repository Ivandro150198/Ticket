# Deploy EventTicket-GB

## Ambiente local (XAMPP)

1. Apache + MySQL ativos
2. `composer install`
3. Copiar `.env.example` → `.env` e preencher SMTP/Stripe se necessário
4. `php database/migrate.php` (após schema/seed inicial)
5. URL: `http://localhost/Ticket/public`

## Produção (checklist)

1. **PHP 8+**, MySQL/MariaDB, `mod_rewrite`, extensões `gd`, `mbstring`, `pdo_mysql`
2. Document root apontar para `public/`
3. `composer install --no-dev --optimize-autoloader`
4. `.env` com `APP_ENV=production`, credenciais BD, SMTP e Stripe
5. HTTPS obrigatório (Let's Encrypt) — headers HSTS já preparados
6. Permissões de escrita em `storage/` e `public/assets/uploads/`
7. Cron opcional de backup: `php` + download admin `/admin/backup` ou `mysqldump`
8. Webhook Stripe: `https://seudominio/pagamento/webhook/stripe`
9. Desativar listagem de diretórios; não expor `.env`, `storage/`, `vendor/` fora do necessário

## Pagamentos

| Método | Comportamento |
|--------|----------------|
| Simulado | Confirma imediato + PDF/e-mail |
| Multibanco / MB Way | Gera referência; botão demo confirma |
| Cartão | Stripe Checkout se `STRIPE_SECRET_KEY` definido |

## E-mail

Sem `MAIL_HOST`, as mensagens vão para `storage/mail.log`. Com SMTP (PHPMailer), envia bilhetes PDF em anexo.
