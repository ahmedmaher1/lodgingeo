<?php
/**
 * Front controller. LiteSpeed/Apache rewrite every non-file request here (.htaccess);
 * the PHP built-in server uses this file as its router script (tests, local dev).
 */
declare(strict_types=1);

require __DIR__ . '/inc/bootstrap.php';
require ROOT . '/inc/routes.php';
require ROOT . '/inc/render.php';

$path   = request_path();
$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

if ($path === '/index.php' || $path === '/index.php/') {
    redirect('/', 301);
}

// Built-in server: mirror the .htaccess rules for real files.
if (PHP_SAPI === 'cli-server' && $path !== '/' && is_file(ROOT . $path)) {
    if (dev_static_allowed($path)) {
        return false;
    }
    send_security_headers();
    render_not_found(403);
    exit;
}

send_security_headers();

if ($method === 'GET' || $method === 'HEAD') {
    remember_attribution();
}

// Form endpoints (POST only).
if ($path === '/sample-request/' || $path === '/contact-submit/') {
    header('X-Robots-Tag: noindex');
    if ($method !== 'POST') {
        redirect($path === '/sample-request/' ? '/hotel-database-sample/' : '/contact/', 303);
    }
    $path === '/sample-request/' ? handle_sample_request() : handle_contact_submit();
    exit;
}

if ($method !== 'GET' && $method !== 'HEAD') {
    http_response_code(405);
    header('Allow: GET, HEAD');
    exit;
}

if ($path === '/hotel-database-sample/thank-you/') {
    render_thank_you((string) ($_GET['t'] ?? ''));
    exit;
}
if (preg_match('~^/download/([a-f0-9]{64})/?$~', $path, $m)) {
    handle_download($m[1]);
    exit;
}
if ($path === '/download/' || str_starts_with($path, '/download/')) {
    render_invalid_token();
    exit;
}
if ($path === '/whatsapp/' || $path === '/whatsapp') {
    $num = preg_replace('/\D+/', '', (string) cfg('WHATSAPP_NUMBER', ''));
    header('X-Robots-Tag: noindex, nofollow');
    if ($num === '') {
        redirect('/contact/', 302);
    }
    redirect('https://wa.me/' . $num . '?text=' . rawurlencode("Hello, I'm interested in Lodgingeo hotel data."), 302);
}
if ($path === '/sitemap.xml') {
    render_sitemap();
    exit;
}

// Old URLs (data/redirects.csv).
if (($target = legacy_redirect($path)) !== null) {
    redirect($target, 301);
}

// Trailing slash on every page URL.
if (!str_ends_with($path, '/') && isset(routes()[$path . '/'])) {
    $qs = $_SERVER['QUERY_STRING'] ?? '';
    redirect($path . '/' . ($qs !== '' ? '?' . $qs : ''), 301);
}

if (isset(routes()[$path])) {
    render_route($path);
    exit;
}

render_not_found(404);
