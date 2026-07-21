<?php

/**
 * php tests/smoke_test.php
 */
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../config/env.php';
env_load(__DIR__ . '/../.env');
require __DIR__ . '/../app/helpers.php';
require __DIR__ . '/../app/Database.php';

spl_autoload_register(function (string $class): void {
    foreach ([
        __DIR__ . '/../app/Models/' . $class . '.php',
        __DIR__ . '/../app/Services/' . $class . '.php',
    ] as $p) {
        if (is_file($p)) {
            require $p;
            return;
        }
    }
});

$failed = 0;
function assert_true($cond, string $msg): void
{
    global $failed;
    if ($cond) {
        echo "[OK] {$msg}\n";
    } else {
        echo "[FAIL] {$msg}\n";
        $failed++;
    }
}

assert_true(class_exists('Dompdf\\Dompdf'), 'Dompdf loaded');
assert_true(class_exists('chillerlan\\QRCode\\QRCode'), 'QRCode loaded');
assert_true(class_exists('PHPMailer\\PHPMailer\\PHPMailer'), 'PHPMailer loaded');

$png = __DIR__ . '/../storage/tickets/_test_qr.png';
QrService::savePng('ETGB:TEST-CODE', $png);
assert_true(is_file($png) && filesize($png) > 50, 'QR PNG generated');

$coupon = Coupon::validate('BEMVINDO10', 100);
assert_true(!empty($coupon['ok']) && abs($coupon['discount'] - 10) < 0.01, 'Coupon 10% works');

$user = User::findByEmail('admin@eventticket-gb.local');
assert_true($user && password_verify('password', $user['password']), 'Admin seed login');

$events = Event::published(1, 5);
assert_true(count($events) > 0, 'Published events exist');

$seats = Seat::byEvent((int) (Event::findBySlug('noite-de-gala-no-sta-djunto')['id'] ?? 0));
assert_true(count($seats) >= 10, 'Gala seats seeded');

echo $failed ? "\nFAILED: {$failed}\n" : "\nALL TESTS PASSED\n";
exit($failed ? 1 : 0);
