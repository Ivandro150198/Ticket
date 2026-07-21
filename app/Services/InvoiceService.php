<?php

use Dompdf\Dompdf;
use Dompdf\Options;

/**
 * Comprovativo/fatura modelo (demonstração).
 * Em produção: software certificado AT + comunicação SAF-T/webservice.
 */
class InvoiceService
{
    public static function ensureNumber(int $orderId): string
    {
        $order = Order::find($orderId);
        if (!$order) {
            throw new RuntimeException('Pedido não encontrado.');
        }
        if (!empty($order['invoice_number'])) {
            return $order['invoice_number'];
        }
        $number = 'ETGB-' . date('Y') . '-' . str_pad((string) $orderId, 6, '0', STR_PAD_LEFT);
        $st = Database::connection()->prepare('UPDATE orders SET invoice_number = ? WHERE id = ? AND (invoice_number IS NULL OR invoice_number = \'\')');
        $st->execute([$number, $orderId]);
        return $number;
    }

    public static function outputPdf(int $orderId): void
    {
        $order = Order::find($orderId);
        if (!$order || $order['status'] !== 'paid') {
            http_response_code(404);
            exit('Fatura indisponível.');
        }
        $number = self::ensureNumber($orderId);
        $items = Order::items($orderId);
        $cur = $order['currency'] ?? 'EUR';
        $payable = max(0, (float) $order['total'] - (float) ($order['discount'] ?? 0));

        $rows = '';
        foreach ($items as $item) {
            $line = (float) $item['unit_price'] * (int) $item['qty'];
            $rows .= '<tr><td>' . e($item['event_title'] . ' — ' . $item['ticket_name']) . '</td><td>' . (int) $item['qty'] . '</td><td>' . e(money((float) $item['unit_price'], $cur)) . '</td><td>' . e(money($line, $cur)) . '</td></tr>';
        }

        $html = '<!DOCTYPE html><html><head><meta charset="utf-8"><style>
body{font-family:DejaVu Sans,sans-serif;font-size:12px;color:#111;margin:32px}
h1{font-size:18px;margin:0 0 8px}
.meta{color:#444;margin-bottom:16px}
table{width:100%;border-collapse:collapse;margin-top:12px}
th,td{border:1px solid #ccc;padding:8px;text-align:left}
th{background:#f4efe6}
.note{margin-top:18px;font-size:10px;color:#666;border-top:1px dashed #bbb;padding-top:10px}
</style></head><body>
<h1>Comprovativo / Fatura modelo</h1>
<div class="meta">
  <div><strong>N.º</strong> ' . e($number) . '</div>
  <div><strong>Data</strong> ' . e($order['created_at'] ?? '') . '</div>
  <div><strong>Cliente</strong> ' . e($order['buyer_name']) . ' · ' . e($order['buyer_email']) . '</div>
  ' . (!empty($order['buyer_nif']) ? '<div><strong>NIF</strong> ' . e($order['buyer_nif']) . '</div>' : '') . '
  <div><strong>Pedido</strong> #' . (int) $orderId . ' · ' . e(label_payment_method($order['payment_method'] ?? null)) . '</div>
</div>
<table>
  <thead><tr><th>Descrição</th><th>Qtd</th><th>Preço</th><th>Total</th></tr></thead>
  <tbody>' . $rows . '</tbody>
</table>
<p><strong>Total pago: ' . e(money($payable, $cur)) . '</strong></p>
<div class="note">Documento demonstrativo da EventTicket-GB. Em produção, a faturação em Portugal exige software certificado pela Autoridade Tributária e Aduaneira e comunicação de faturas (SAF-T ou webservice), com IVA aplicável aos bilhetes de espetáculos.</div>
</body></html>';

        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="fatura-' . $number . '.pdf"');
        echo $dompdf->output();
        exit;
    }
}
