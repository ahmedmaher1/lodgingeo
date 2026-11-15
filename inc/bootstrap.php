<?php
/**
 * Loads settings and the server-only config, sets error handling and security headers.
 * Fails closed with a generic 500 when the server config is missing.
 */
declare(strict_types=1);

define('ROOT', dirname(__DIR__));
require ROOT . '/inc/site.php';

/**
 * Locate the server config. Production: one level above public_html.
 * A freshly uploaded config (uploads can only land inside public_html) is moved
 * out of the web root on the first request.
 */
function load_server_config(): ?array
{
    $outside  = dirname(ROOT) . '/lodgingeo-config.php';
    $incoming = ROOT . '/.lodgingeo-config.incoming.php';
    if (is_file($incoming)) {
        if (@rename($incoming, $outside)) {
            @chmod($outside, 0600);
        }
    }
    $candidates = array_filter([getenv('LODGINGEO_CONFIG') ?: null, $outside]);
    foreach ($candidates as $file) {
        if (is_file($file)) {
            $cfg = require $file;
            if (is_array($cfg)) {
                return $cfg;
            }
        }
    }
    return null;
}

$GLOBALS['CONFIG'] = load_server_config();
if ($GLOBALS['CONFIG'] === null) {
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    header('Cache-Control: no-store');
    echo "Service temporarily unavailable.\n";
    exit;
}

function cfg(string $key, mixed $default = null): mixed
{
    return $GLOBALS['CONFIG'][$key] ?? $default;
}

function is_production(): bool
{
    return cfg('ENV', 'production') === 'production';
}

// Errors: never shown in production, logged outside the web root.
error_reporting(E_ALL);
ini_set('display_errors', is_production() ? '0' : '1');
ini_set('log_errors', '1');
$errorLog = cfg('ERROR_LOG', dirname(ROOT) . '/lodgingeo-error.log');
ini_set('error_log', $errorLog);
date_default_timezone_set('UTC');

set_exception_handler(function (Throwable $e): void {
    error_log('[lodgingeo] ' . get_class($e) . ': ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: text/html; charset=utf-8');
        header('Cache-Control: no-store');
        header('X-Robots-Tag: noindex');
    }
    echo '<!doctype html><html lang="en"><meta charset="utf-8"><title>Error · Lodgingeo</title>'
        . '<meta name="robots" content="noindex"><body style="font-family:sans-serif;max-width:40em;margin:3em auto;padding:0 16px">'
        . '<h1>Something went wrong</h1><p>Please try again in a moment, or email '
        . '<a href="mailto:' . PUBLIC_EMAIL . '">' . PUBLIC_EMAIL . '</a>.</p></body></html>';
    if (!is_production()) {
        echo '<pre>' . htmlspecialchars((string) $e) . '</pre>';
    }
});

require ROOT . '/inc/helpers.php';
require ROOT . '/inc/db.php';
require ROOT . '/inc/mail.php';
require ROOT . '/inc/forms.php';
require ROOT . '/inc/seo.php';

/** Security headers for every PHP response (§12). */
function send_security_headers(): void
{
    $script  = "'self'";
    $connect = "'self'";
    $frame   = "'none'";
    $img     = "'self' data:";
    if (turnstile_enabled()) {
        $script  .= ' https://challenges.cloudflare.com';
        $frame    = 'https://challenges.cloudflare.com';
    }
    if (cfg('GA4_ID')) {
        $script  .= ' https://www.googletagmanager.com';
        $connect .= ' https://*.google-analytics.com https://*.analytics.google.com https://www.googletagmanager.com';
        $img     .= ' https://*.google-analytics.com https://www.googletagmanager.com';
    }
    $csp = "default-src 'self'; img-src $img; script-src $script; style-src 'self'; connect-src $connect; "
         . "frame-src $frame; form-action 'self'; frame-ancestors 'none'; base-uri 'self'; object-src 'none'";
    header('Content-Security-Policy: ' . $csp);
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=(), payment=(), usb=()');
    header('X-Frame-Options: DENY');
    if (cfg('HSTS', false)) {
        header('Strict-Transport-Security: max-age=31536000');
    }
    header_remove('X-Powered-By');
}

/** Sessions: only started when needed (UTM fallback). Cookie is Secure, HttpOnly, SameSite=Lax. */
function start_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    session_name('lgsess');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => is_production(),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    ini_set('session.use_strict_mode', '1');
    session_start();
}
