<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MailService
{
    public static function send(string $to, string $subject, string $html, array $attachments = []): bool
    {
        $host = env('MAIL_HOST', '');
        $log = __DIR__ . '/../../storage/mail.log';

        if ($host === '' || $host === null) {
            $line = date('c') . " | TO={$to} | SUBJECT={$subject}\n";
            file_put_contents($log, $line, FILE_APPEND);
            return true;
        }

        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = $host;
            $mail->Port = (int) env('MAIL_PORT', 587);
            $mail->SMTPAuth = true;
            $mail->Username = env('MAIL_USERNAME', '');
            $mail->Password = env('MAIL_PASSWORD', '');
            $enc = env('MAIL_ENCRYPTION', 'tls');
            if ($enc) {
                $mail->SMTPSecure = $enc;
            }
            $mail->CharSet = 'UTF-8';
            $mail->setFrom(env('MAIL_FROM', 'noreply@eventticket-gb.local'), env('MAIL_FROM_NAME', 'EventTicket-GB'));
            $mail->addAddress($to);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $html;
            $mail->AltBody = strip_tags($html);
            foreach ($attachments as $path => $name) {
                if (is_file($path)) {
                    $mail->addAttachment($path, $name);
                }
            }
            $mail->send();
            file_put_contents($log, date('c') . " | SENT TO={$to} | SUBJECT={$subject}\n", FILE_APPEND);
            return true;
        } catch (Exception $e) {
            file_put_contents($log, date('c') . " | FAIL TO={$to} | " . $e->getMessage() . "\n", FILE_APPEND);
            return false;
        }
    }
}
