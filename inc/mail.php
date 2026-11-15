<?php
/**
 * PHPMailer wrapper. Transport from the server config:
 *   smtp - Hostinger (or other) SMTP, SSL on 465 by default
 *   mail - PHP mail() on the hosting server (interim, no credentials needed)
 *   log  - append JSON lines to MAIL_LOG (tests and local development)
 */
declare(strict_types=1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as MailException;

require_once ROOT . '/vendor/phpmailer/src/Exception.php';
require_once ROOT . '/vendor/phpmailer/src/PHPMailer.php';
require_once ROOT . '/vendor/phpmailer/src/SMTP.php';

/**
 * @return bool true when the message was handed to the transport
 */
function send_mail(string $to, string $subject, string $text, ?string $html = null, ?string $replyTo = null, ?string $replyName = null): bool
{
    $transport = (string) cfg('MAIL_TRANSPORT', 'log');

    if ($transport === 'log') {
        $line = json_encode([
            'at' => gmdate('c'), 'to' => $to, 'subject' => $subject, 'reply_to' => $replyTo,
            'text' => $text, 'html' => $html !== null,
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n";
        return file_put_contents((string) cfg('MAIL_LOG', sys_get_temp_dir() . '/lodgingeo-mail.log'), $line, FILE_APPEND | LOCK_EX) !== false;
    }

    $m = new PHPMailer(true);
    try {
        $m->CharSet = 'UTF-8';
        if ($transport === 'smtp') {
            $m->isSMTP();
            $m->Host       = (string) cfg('SMTP_HOST', 'smtp.hostinger.com');
            $m->Port       = (int) cfg('SMTP_PORT', 465);
            $m->SMTPAuth   = true;
            $m->Username   = (string) cfg('SMTP_USER');
            $m->Password   = (string) cfg('SMTP_PASS');
            $m->SMTPSecure = cfg('SMTP_SECURE', 'ssl') === 'tls' ? PHPMailer::ENCRYPTION_STARTTLS : PHPMailer::ENCRYPTION_SMTPS;
            $m->Timeout    = 15;
        } else {
            $m->isMail();
        }
        $m->setFrom(PUBLIC_EMAIL, SITE_NAME);
        $m->Sender = PUBLIC_EMAIL;
        $m->addAddress($to);
        if ($replyTo !== null) {
            $m->addReplyTo($replyTo, $replyName ?? '');
        } else {
            $m->addReplyTo(PUBLIC_EMAIL, SITE_NAME);
        }
        $m->Subject = $subject;
        if ($html !== null) {
            $m->isHTML(true);
            $m->Body    = $html;
            $m->AltBody = $text;
        } else {
            $m->Body = $text;
        }
        $m->send();
        return true;
    } catch (MailException $ex) {
        error_log('[lodgingeo] mail failed to ' . $to . ': ' . $m->ErrorInfo);
        return false;
    }
}

/** Minimal HTML version of a plain-text email: paragraphs and links, no template. */
function text_to_html(string $text): string
{
    $paras = preg_split("/\n{2,}/", trim($text)) ?: [];
    $out = '';
    foreach ($paras as $p) {
        $p = nl2br(e($p), false);
        $p = (string) preg_replace('~(https://[^\s<]+)~', '<a href="$1">$1</a>', $p);
        $out .= '<p>' . $p . "</p>\n";
    }
    return '<!doctype html><html><body style="font-family:Arial,sans-serif;font-size:15px;line-height:1.5;color:#1a1a1a">' . $out . '</body></html>';
}
