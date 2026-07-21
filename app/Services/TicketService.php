<?php

use Dompdf\Dompdf;
use Dompdf\Options;

class TicketService
{
    public static function fulfillOrder(int $orderId): array
    {
        $order = Order::find($orderId);
        if (!$order) {
            throw new RuntimeException('Pedido não encontrado.');
        }
        if ($order['status'] !== 'paid') {
            throw new RuntimeException('Pedido ainda não está pago.');
        }

        $existing = Ticket::byOrder($orderId);
        if ($existing) {
            return $existing;
        }

        $items = Order::items($orderId);
        $created = [];
        $dir = self::ticketsDir();

        $attachments = [];
        foreach ($items as $item) {
            for ($i = 0; $i < (int) $item['qty']; $i++) {
                $code = Ticket::generateCode();
                $payload = 'ETGB:' . $code;
                $seatLabel = null;
                if (!empty($item['seat_id'])) {
                    $seat = Seat::find((int) $item['seat_id']);
                    if ($seat) {
                        $seatLabel = $seat['row_label'] . $seat['seat_number'];
                        Seat::markSold((int) $seat['id']);
                    }
                }

                $ticketId = Ticket::create([
                    'order_item_id' => $item['id'],
                    'code' => $code,
                    'qr_payload' => $payload,
                    'seat_label' => $seatLabel,
                    'holder_name' => $order['buyer_name'],
                ]);

                $pdfPath = self::writeTicketPdf([
                    'code' => $code,
                    'payload' => $payload,
                    'buyer' => $order['buyer_name'],
                    'email' => $order['buyer_email'],
                    'event' => $item['event_title'],
                    'ticket_name' => $item['ticket_name'],
                    'venue' => $item['venue'],
                    'city' => $item['city'],
                    'starts_at' => $item['starts_at'],
                    'order_id' => $orderId,
                    'seat_label' => $seatLabel,
                    'price' => (float) $item['unit_price'],
                    'currency' => $item['currency'] ?? ($order['currency'] ?? 'EUR'),
                    'promoter_name' => $item['promoter_name'] ?: ($item['producer_user_name'] ?? 'Promotor'),
                    'promoter_nif' => $item['promoter_nif'] ?? '',
                    'age_rating' => $item['age_rating'] ?? 'Todos',
                    'capacity' => $item['capacity'] ?? null,
                ], $dir);

                Ticket::updatePdf($ticketId, 'storage/tickets/' . basename($pdfPath));
                $attachments[$pdfPath] = 'bilhete-' . $code . '.pdf';
                $created[] = Ticket::findByCode($code);
            }
        }

        $html = '<p>Olá ' . e($order['buyer_name']) . ',</p>'
            . '<p>Obrigado pela compra no <strong>EventTicket-GB</strong>. O pedido #' . (int) $orderId
            . ' está confirmado. Em anexo encontra os bilhetes em PDF com código QR.</p>'
            . '<p>Total: ' . money((float) $order['total'] - (float) ($order['discount'] ?? 0)) . '</p>';

        MailService::send($order['buyer_email'], 'Os seus bilhetes EventTicket-GB #' . $orderId, $html, $attachments);
        AuditService::log('tickets.issued', 'order', $orderId, ['count' => count($created)]);

        return $created;
    }

    /** Regenera o PDF do bilhete (útil quando o QR falhou na geração original). */
    public static function regeneratePdf(array $ticket): string
    {
        $dir = self::ticketsDir();
        $payload = $ticket['qr_payload'] ?: ('ETGB:' . $ticket['code']);
        $pdfPath = self::writeTicketPdf([
            'code' => $ticket['code'],
            'payload' => $payload,
            'buyer' => $ticket['buyer_name'] ?? $ticket['holder_name'] ?? '',
            'email' => $ticket['buyer_email'] ?? '',
            'event' => $ticket['event_title'] ?? 'Evento',
            'ticket_name' => $ticket['ticket_name'] ?? '',
            'venue' => $ticket['venue'] ?? '',
            'city' => $ticket['city'] ?? '',
            'starts_at' => $ticket['starts_at'] ?? date('Y-m-d H:i:s'),
            'order_id' => (int) ($ticket['order_id'] ?? 0),
            'seat_label' => $ticket['seat_label'] ?? null,
            'price' => isset($ticket['unit_price']) ? (float) $ticket['unit_price'] : null,
            'currency' => $ticket['currency'] ?? 'EUR',
            'promoter_name' => $ticket['promoter_name'] ?? ($ticket['producer_user_name'] ?? ''),
            'promoter_nif' => $ticket['promoter_nif'] ?? '',
            'age_rating' => $ticket['age_rating'] ?? 'Todos',
            'capacity' => $ticket['capacity'] ?? null,
        ], $dir);

        $relative = 'storage/tickets/' . basename($pdfPath);
        Ticket::updatePdf((int) $ticket['id'], $relative);
        return __DIR__ . '/../../' . $relative;
    }

    private static function ticketsDir(): string
    {
        $dir = __DIR__ . '/../../storage/tickets';
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        return $dir;
    }

    private static function writeTicketPdf(array $data, string $dir): string
    {
        $file = $dir . '/ticket_' . $data['code'] . '.pdf';
        $hasGd = extension_loaded('gd') && function_exists('imagecreatetruecolor');

        // Dompdf precisa de GD para embutir PNG; sem GD usamos SVG inline.
        if ($hasGd) {
            $qrFile = $dir . '/qr_' . $data['code'] . '.png';
            QrService::savePng($data['payload'], $qrFile);
            $real = realpath($qrFile) ?: $qrFile;
            $qrMarkup = '<img class="qr" src="file:///' . str_replace('\\', '/', $real) . '" width="140" height="140">';
        } else {
            $svg = QrService::svg($data['payload']);
            // Remover declaração XML se existir (evita problemas no HTML do Dompdf)
            $svg = preg_replace('/<\?xml[^>]*\?>/', '', $svg) ?? $svg;
            $qrMarkup = '<div class="qr">' . $svg . '</div>';
        }

        $starts = format_datetime($data['starts_at']);
        $seat = !empty($data['seat_label'])
            ? '<div><strong>Lugar:</strong> ' . e($data['seat_label']) . '</div>'
            : (!empty($data['capacity'])
                ? '<div><strong>Lotação do recinto:</strong> ' . (int) $data['capacity'] . '</div>'
                : '');
        $price = isset($data['price']) && $data['price'] !== null
            ? '<div><strong>Preço:</strong> ' . e(money((float) $data['price'], $data['currency'] ?? 'EUR')) . '</div>'
            : '';
        $promoter = '<div><strong>Promotor:</strong> ' . e($data['promoter_name'] ?? '—')
            . (!empty($data['promoter_nif']) ? ' · NIF ' . e($data['promoter_nif']) : '')
            . '</div>';
        $age = '<div><strong>Classificação etária:</strong> ' . e(label_age_rating($data['age_rating'] ?? 'Todos')) . '</div>';

        $html = '<!DOCTYPE html><html><head><meta charset="utf-8"><style>
body{font-family:DejaVu Sans,sans-serif;color:#111;margin:0;padding:24px;background:#fff}
.ticket{border:2px solid #c45c26;border-radius:12px;overflow:hidden}
.brand{background:#c45c26;color:#fff;padding:14px 20px;font-size:20px;font-weight:bold}
.body{padding:20px;overflow:hidden}
.left{float:left;width:68%}
.right{float:right;width:28%;text-align:center}
.clear{clear:both}
h1{font-size:18px;margin:0 0 10px}
.meta{font-size:12px;line-height:1.6;color:#333}
.code{margin-top:12px;font-size:14px;font-weight:bold;letter-spacing:1px}
.qr-box{background:#fff;border:1px solid #ddd;padding:8px;display:inline-block}
.qr{width:140px;height:140px}
.qr svg{width:140px;height:140px;display:block}
.foot{padding:10px 20px;border-top:1px dashed #ccc;font-size:10px;color:#666}
</style></head><body>
<div class="ticket">
  <div class="brand">EventTicket-GB</div>
  <div class="body">
    <div class="left">
      <h1>' . e($data['event']) . '</h1>
      <div class="meta">
        ' . $promoter . '
        <div><strong>Tipo:</strong> ' . e($data['ticket_name']) . '</div>
        ' . $price . '
        ' . $seat . '
        ' . $age . '
        <div><strong>Quando:</strong> ' . e($starts) . '</div>
        <div><strong>Onde:</strong> ' . e($data['venue']) . ', ' . e($data['city']) . '</div>
        <div><strong>Titular:</strong> ' . e($data['buyer']) . '</div>
        <div><strong>Pedido:</strong> #' . (int) $data['order_id'] . '</div>
      </div>
      <div class="code">CODIGO: ' . e($data['code']) . '</div>
    </div>
    <div class="right">
      <div class="qr-box">' . $qrMarkup . '</div>
      <div style="font-size:9px;margin-top:6px;color:#666">Apresente o QR na entrada</div>
    </div>
    <div class="clear"></div>
  </div>
  <div class="foot">Bilhete nominativo de uso unico. Proibida a revenda nao autorizada. © EventTicket-GB · DL 23/2014</div>
</div>
</body></html>';

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');
        $options->setChroot([realpath($dir) ?: $dir, realpath(__DIR__ . '/../../') ?: __DIR__ . '/../../']);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        file_put_contents($file, $dompdf->output());

        // Se PNG não entrou no PDF, tentar data-URI (só com GD)
        if ($hasGd) {
            $bin = file_get_contents($file);
            if ($bin !== false && !preg_match('/\/Subtype\s*\/Image/', $bin)) {
                $dataUri = QrService::pngDataUri($data['payload']);
                $html2 = preg_replace('/src="file:\/\/\/[^"]+"/', 'src="' . $dataUri . '"', $html, 1) ?? $html;
                $dompdf = new Dompdf($options);
                $dompdf->loadHtml($html2, 'UTF-8');
                $dompdf->setPaper('A4', 'portrait');
                $dompdf->render();
                file_put_contents($file, $dompdf->output());
            }
        }

        return $file;
    }
}
