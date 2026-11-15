<?php
/** Page rendering, sitemap, thank-you and download handling. */
declare(strict_types=1);

function dev_static_allowed(string $path): bool
{
    return str_starts_with($path, '/assets/')
        || in_array($path, ['/favicon.svg', '/apple-touch-icon.png', '/og-image.png', '/robots.txt', '/llms.txt', '/' . INDEXNOW_KEY . '.txt'], true);
}

function render_route(string $path, int $status = 200, array $extra = []): void
{
    $route = routes()[$path];
    $page = array_merge($route, ['path' => $path, 'has_form' => !empty($route['sample_form']) || in_array($path, ['/', '/contact/'], true)], $extra);
    if (isset($_GET['sent'])) {
        $page['noindex'] = true;
        $page['nocache'] = true;
    }
    render_page($page, $status);
}

function render_page(array $page, int $status = 200): void
{
    $GLOBALS['PAGE'] = $page;
    ob_start();
    include ROOT . '/inc/pages/' . $page['tpl'] . '.php';
    $content = (string) ob_get_clean();
    $page = $GLOBALS['PAGE'];

    http_response_code($status);
    header('Content-Type: text/html; charset=utf-8');
    $state = !empty($GLOBALS['FORM_STATE']) || !empty($page['nocache']) || $status !== 200;
    if (!empty($page['noindex'])) {
        header('X-Robots-Tag: noindex');
    }
    if ($state || session_status() === PHP_SESSION_ACTIVE) {
        header('Cache-Control: private, no-store');
    } else {
        header('Cache-Control: public, max-age=600');
    }
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'HEAD') {
        return;
    }
    include ROOT . '/inc/layout/header.php';
    echo $content;
    include ROOT . '/inc/layout/footer.php';
}

function render_not_found(int $status = 404): void
{
    render_page([
        'tpl' => '404', 'path' => request_path(), 'noindex' => true, 'parent' => '/', 'crumb' => 'Page not found',
        'title' => 'Page not found — Lodgingeo', 'desc' => 'The page you asked for does not exist on lodgingeo.com.',
        'h1' => 'Page not found',
    ], $status);
}

function lead_by_token(string $token): ?array
{
    if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
        return null;
    }
    $stmt = db()->prepare('SELECT * FROM leads WHERE token = ? ORDER BY id DESC LIMIT 1');
    $stmt->execute([$token]);
    $lead = $stmt->fetch();
    return $lead ?: null;
}

function token_valid(?array $lead): bool
{
    return $lead !== null && $lead['token_expires_at'] > now_utc() && (int) $lead['download_count'] < 5;
}

function render_invalid_token(): void
{
    render_page([
        'tpl' => 'link-expired', 'path' => '/hotel-database-sample/thank-you/', 'noindex' => true, 'has_form' => true,
        'parent' => '/hotel-database-sample/', 'crumb' => 'Link expired',
        'title' => 'Download link expired — Lodgingeo', 'desc' => 'This sample download link is no longer valid. Request a new link below.',
        'h1' => 'This download link has expired',
    ], 410);
}

function render_thank_you(string $token): void
{
    $lead = lead_by_token($token);
    if (!token_valid($lead)) {
        render_invalid_token();
        return;
    }
    render_page([
        'tpl' => 'thank-you', 'path' => '/hotel-database-sample/thank-you/', 'noindex' => true, 'nocache' => true,
        'parent' => '/hotel-database-sample/', 'crumb' => 'Thank you', 'lead' => $lead, 'ready' => sample_available(),
        'title' => 'Thank you — Lodgingeo hotel data sample', 'desc' => 'Your Lodgingeo hotel data sample request was received.',
        'h1' => 'Thank you',
    ]);
}

function handle_download(string $token): void
{
    $lead = lead_by_token($token);
    if (!token_valid($lead)) {
        render_invalid_token();
        return;
    }
    if (!sample_available()) {
        render_thank_you($token);
        return;
    }
    $file = sample_file_path();
    db()->prepare('UPDATE leads SET download_count = download_count + 1, first_downloaded_at = COALESCE(first_downloaded_at, ?), last_downloaded_at = ? WHERE id = ?')
        ->execute([now_utc(), now_utc(), $lead['id']]);
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . SAMPLE_FILENAME . '"');
    header('Content-Length: ' . filesize($file));
    header('X-Robots-Tag: noindex');
    header('Cache-Control: private, no-store');
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'HEAD') {
        readfile($file);
    }
}

function render_sitemap(): void
{
    header('Content-Type: application/xml; charset=utf-8');
    header('Cache-Control: public, max-age=600');
    $x = '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
    foreach (routes() as $path => $r) {
        $lastmod = !empty($r['data'])
            ? LATEST_RELEASE
            : gmdate('Y-m-d', (int) @filemtime(ROOT . '/inc/pages/' . $r['tpl'] . '.php'));
        $x .= '  <url><loc>' . e(abs_url($path)) . '</loc><lastmod>' . $lastmod . '</lastmod></url>' . "\n";
    }
    echo $x . '</urlset>' . "\n";
}

/** Look up an old path in data/redirects.csv. */
function legacy_redirect(string $path): ?string
{
    $file = ROOT . '/data/redirects.csv';
    if (!is_file($file)) {
        return null;
    }
    $h = fopen($file, 'r');
    $target = null;
    $header = true;
    $needle = strtolower(rtrim($path, '/'));
    while (($row = fgetcsv($h, 0, ',', '"', '\\')) !== false) {
        if ($header) {
            $header = false;
            continue;
        }
        if (count($row) >= 2 && strtolower(rtrim(trim($row[0]), '/')) === $needle && $needle !== '') {
            $target = trim($row[1]);
            break;
        }
    }
    fclose($h);
    return $target;
}
