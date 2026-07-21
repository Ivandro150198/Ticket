<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../config/env.php';
env_load(__DIR__ . '/../.env');

require __DIR__ . '/../app/helpers.php';
require __DIR__ . '/../app/Database.php';
require __DIR__ . '/../app/Router.php';

session_start();
send_security_headers();

date_default_timezone_set('Europe/Lisbon');

spl_autoload_register(function (string $class): void {
    $paths = [
        __DIR__ . '/../app/Controllers/' . $class . '.php',
        __DIR__ . '/../app/Models/' . $class . '.php',
        __DIR__ . '/../app/Services/' . $class . '.php',
    ];
    foreach ($paths as $path) {
        if (is_file($path)) {
            require $path;
            return;
        }
    }
});

$router = new Router();

$router->get('/', [HomeController::class, 'index']);
$router->get('/eventos', [EventController::class, 'index']);
$router->get('/evento/{slug}', [EventController::class, 'show']);
$router->get('/sitemap.xml', [SeoController::class, 'sitemap']);
$router->get('/robots.txt', [SeoController::class, 'robots']);
$router->get('/manifest.webmanifest', [PwaController::class, 'manifest']);
$router->get('/sw.js', [PwaController::class, 'serviceWorker']);
$router->get('/offline', [PwaController::class, 'offline']);

$router->get('/login', [AuthController::class, 'loginForm']);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/registo', [AuthController::class, 'registerForm']);
$router->post('/registo', [AuthController::class, 'register']);
$router->post('/logout', [AuthController::class, 'logout']);
$router->get('/recuperar', [AuthController::class, 'forgotForm']);
$router->post('/recuperar', [AuthController::class, 'forgot']);
$router->get('/repor-senha', [AuthController::class, 'resetForm']);
$router->post('/repor-senha', [AuthController::class, 'reset']);

$router->get('/carrinho', [CartController::class, 'index']);
$router->post('/carrinho/adicionar', [CartController::class, 'add']);
$router->post('/carrinho/atualizar', [CartController::class, 'update']);
$router->post('/carrinho/remover', [CartController::class, 'remove']);
$router->post('/carrinho/cupao', [CartController::class, 'applyCoupon']);
$router->post('/carrinho/cupao/remover', [CartController::class, 'removeCoupon']);
$router->get('/checkout', [CartController::class, 'checkoutForm']);
$router->post('/checkout', [CartController::class, 'checkout']);
$router->get('/pedido/{id}', [CartController::class, 'order']);
$router->get('/bilhete/{code}', [CartController::class, 'downloadTicket']);

$router->get('/pagamento/multibanco/{id}', [PaymentController::class, 'multibanco']);
$router->get('/pagamento/mbway/{id}', [PaymentController::class, 'mbway']);
$router->get('/pagamento/orange-money/{id}', [PaymentController::class, 'orangeMoney']);
$router->get('/pagamento/transferencia/{id}', [PaymentController::class, 'transferUemoa']);
$router->post('/pagamento/confirmar', [PaymentController::class, 'confirmLocal']);
$router->get('/pagamento/sucesso', [PaymentController::class, 'success']);
$router->get('/pagamento/cancelado', [PaymentController::class, 'cancel']);
$router->post('/pagamento/webhook/stripe', [PaymentController::class, 'webhook']);

$router->get('/conta', [AccountController::class, 'dashboard']);
$router->get('/conta/pedidos', [AccountController::class, 'orders']);
$router->get('/conta/bilhetes', [AccountController::class, 'tickets']);
$router->post('/conta/reembolso', [AccountController::class, 'requestRefund']);

$router->get('/contacto', [PageController::class, 'contact']);
$router->post('/contacto', [PageController::class, 'contactSubmit']);
$router->get('/faq', [PageController::class, 'faq']);
$router->get('/servicos', [PageController::class, 'services']);
$router->get('/termos', [PageController::class, 'terms']);
$router->get('/privacidade', [PageController::class, 'privacy']);
$router->get('/cookies', [PageController::class, 'cookies']);
$router->get('/resolucao-litigios', [PageController::class, 'ral']);
$router->get('/postos-de-venda', [PageController::class, 'outlets']);
$router->post('/newsletter', [PageController::class, 'newsletter']);
$router->get('/livro-de-reclamacoes', [PageController::class, 'complaints']);
$router->post('/livro-de-reclamacoes', [PageController::class, 'complaintsSubmit']);
$router->get('/pedido/{id}/fatura', [CartController::class, 'invoice']);

$router->get('/produtor', [ProducerController::class, 'dashboard']);
$router->get('/produtor/eventos', [ProducerController::class, 'events']);
$router->get('/produtor/eventos/novo', [ProducerController::class, 'createForm']);
$router->post('/produtor/eventos', [ProducerController::class, 'create']);
$router->get('/produtor/eventos/{id}/editar', [ProducerController::class, 'editForm']);
$router->post('/produtor/eventos/{id}', [ProducerController::class, 'update']);
$router->post('/produtor/eventos/{id}/eliminar', [ProducerController::class, 'delete']);
$router->post('/produtor/eventos/{id}/lugares', [ProducerController::class, 'generateSeats']);
$router->get('/produtor/vendas', [ProducerController::class, 'sales']);
$router->get('/produtor/vendas/export', [ProducerController::class, 'exportSales']);
$router->get('/produtor/validar', [ProducerController::class, 'validateForm']);
$router->post('/produtor/validar', [ProducerController::class, 'validateTicket']);
$router->get('/produtor/validar-offline', [ProducerController::class, 'offlineValidate']);

$router->get('/admin', [AdminController::class, 'dashboard']);
$router->get('/admin/utilizadores', [AdminController::class, 'users']);
$router->post('/admin/utilizadores/role', [AdminController::class, 'updateUserRole']);
$router->get('/admin/eventos', [AdminController::class, 'events']);
$router->post('/admin/eventos/estado', [AdminController::class, 'setEventStatus']);
$router->get('/admin/parceiros', [AdminController::class, 'partners']);
$router->post('/admin/parceiros', [AdminController::class, 'addPartner']);
$router->post('/admin/parceiros/toggle', [AdminController::class, 'togglePartner']);
$router->post('/admin/parceiros/eliminar', [AdminController::class, 'deletePartner']);
$router->get('/admin/newsletter', [AdminController::class, 'newsletter']);
$router->post('/admin/newsletter/eliminar', [AdminController::class, 'deleteSubscriber']);
$router->get('/admin/pedidos', [AdminController::class, 'orders']);
$router->post('/admin/pedidos/reembolso', [AdminController::class, 'refundOrder']);
$router->get('/admin/pedidos/export', [AdminController::class, 'exportOrders']);
$router->get('/admin/mensagens', [AdminController::class, 'messages']);
$router->get('/admin/cupoes', [AdminController::class, 'coupons']);
$router->post('/admin/cupoes', [AdminController::class, 'addCoupon']);
$router->post('/admin/cupoes/eliminar', [AdminController::class, 'deleteCoupon']);
$router->get('/admin/reclamacoes', [AdminController::class, 'complaints']);
$router->post('/admin/reclamacoes/estado', [AdminController::class, 'complaintStatus']);
$router->get('/admin/auditoria', [AdminController::class, 'audit']);
$router->get('/admin/backup', [AdminController::class, 'backup']);

try {
    $router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI'] ?? '/');
} catch (PDOException $e) {
    http_response_code(500);
    echo '<h1>Erro de base de dados</h1><p>Execute <code>php database/migrate.php</code> e verifique <code>config/db.php</code> / <code>.env</code>.</p>';
    if (env('APP_ENV') === 'local') {
        echo '<pre>' . htmlspecialchars($e->getMessage()) . '</pre>';
    }
}
