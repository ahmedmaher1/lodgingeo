<?php
/**
 * Smoke test (brief §13). Usage: php tests/smoke_test.php
 * Starts `php -S 127.0.0.1:8080 index.php` with SQLite and MAIL_TRANSPORT=log.
 * Env: SMOKE_PORT (default 8080).
 */
declare(strict_types=1);

$root = dirname(__DIR__);
$port = (int) (getenv('SMOKE_PORT') ?: 8080);
$base = "http://127.0.0.1:$port";
$tmp  = sys_get_temp_dir() . '/lodgingeo-smoke-' . getmypid();
@mkdir($tmp . '/samples', 0777, true);
$fakeWa = '19995550123';
$sampleName = 'lodgingeo-hotel-data-sample-2026-10.xlsx';

file_put_contents("$tmp/config.php", '<?php return ' . var_export([
    'ENV' => 'testing', 'DB_DSN' => "sqlite:$tmp/test.sqlite", 'MAIL_TRANSPORT' => 'log', 'MAIL_LOG' => "$tmp/mail.log",
    'ERROR_LOG' => "$tmp/error.log", 'OWNER_NOTIFY_EMAIL' => 'owner@example.com', 'IP_SALT' => 'test-salt', 'APP_SECRET' => 'test-secret',
    'WHATSAPP_NUMBER' => $fakeWa, 'CHECK_DNS' => false, 'SAMPLE_DIR' => "$tmp/samples", 'RATE_LIMIT_IP_HOUR' => 50,
    'MIN_SUBMIT_SECONDS' => 3,
], true) . ';');

$fails = 0;
$passes = 0;
function ok(bool $cond, string $msg): void
{
    global $fails, $passes;
    if ($cond) {
        $passes++;
    } else {
        $fails++;
        echo "FAIL: $msg\n";
    }
}

/** @return array{0:int,1:array<string,string>,2:string} */
function req(string $method, string $path, ?array $post = null, array $headers = []): array
{
    global $base;
    $ch = curl_init($base . $path);
    $respHeaders = [];
    curl_setopt_array($ch, [
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_TIMEOUT => 20,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_HEADERFUNCTION => function ($ch, $line) use (&$respHeaders) {
            $p = strpos($line, ':');
            if ($p !== false) {
                $respHeaders[strtolower(trim(substr($line, 0, $p)))] = trim(substr($line, $p + 1));
            }
            return strlen($line);
        },
    ]);
    if ($post !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
    }
    $body = (string) curl_exec($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    curl_close($ch);
    return [$status, $respHeaders, $body];
}

function form_fields(string $html, string $formId): array
{
    if (!preg_match('~<form[^>]*id="' . preg_quote($formId, '~') . '"[^>]*>(.*?)</form>~s', $html, $m)) {
        return [];
    }
    preg_match_all('~<input[^>]*type="hidden"[^>]*name="([^"]+)"[^>]*value="([^"]*)"~', $m[1], $mm, PREG_SET_ORDER);
    $out = [];
    foreach ($mm as $x) {
        $out[$x[1]] = html_entity_decode($x[2], ENT_QUOTES);
    }
    $out['website'] = '';
    return $out;
}

function pdo(): PDO
{
    global $tmp;
    static $p = null;
    return $p ??= new PDO("sqlite:$tmp/test.sqlite", null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
}

function count_rows(string $table): int
{
    try {
        return (int) pdo()->query("SELECT COUNT(*) FROM $table")->fetchColumn();
    } catch (PDOException) {
        return 0;
    }
}

// ---------------------------------------------------------------- start server
$env = array_merge(getenv(), ['LODGINGEO_CONFIG' => "$tmp/config.php"]);
$proc = proc_open([PHP_BINARY, '-S', "127.0.0.1:$port", 'index.php'], [1 => ['file', "$tmp/server.log", 'a'], 2 => ['file', "$tmp/server.log", 'a']], $pipes, $root, $env);
register_shutdown_function(function () use ($proc, $tmp) {
    proc_terminate($proc);
    if (is_file("$tmp/error.log") && filesize("$tmp/error.log") > 0) {
        echo "--- PHP error log ---\n" . file_get_contents("$tmp/error.log");
    }
});
for ($i = 0; $i < 50; $i++) {
    usleep(100000);
    if (@fsockopen('127.0.0.1', $port)) {
        break;
    }
}

require $root . '/inc/routes.php';
$routes = routes();

// ---------------------------------------------------------------- sitemap pages
[$st, $h, $xml] = req('GET', '/sitemap.xml');
ok($st === 200, 'sitemap.xml returns 200');
ok(str_contains($h['content-type'] ?? '', 'xml'), 'sitemap.xml has an XML content type');
ok(simplexml_load_string($xml) !== false, 'sitemap.xml is valid XML');
preg_match_all('~<loc>([^<]+)</loc>~', $xml, $m);
$locs = $m[1];
ok(count($locs) === count($routes), 'sitemap lists every route (' . count($locs) . '/' . count($routes) . ')');

$titles = $descs = [];
$links = [];
$pages = [];
foreach ($locs as $loc) {
    ok(str_starts_with($loc, 'https://lodgingeo.com/'), "sitemap URL is canonical: $loc");
    $path = substr($loc, strlen('https://lodgingeo.com'));
    [$st, $h, $html] = req('GET', $path);
    $pages[$path] = $html;
    ok($st === 200, "$path returns 200 (got $st)");
    ok(strlen($html) < 60 * 1024, "$path HTML under 60 KB (" . round(strlen($html) / 1024, 1) . ' KB)');
    ok(str_contains($h['content-security-policy'] ?? '', "default-src 'self'"), "$path sends CSP");
    ok(($h['x-content-type-options'] ?? '') === 'nosniff', "$path sends nosniff");
    ok(($h['referrer-policy'] ?? '') === 'strict-origin-when-cross-origin', "$path sends Referrer-Policy");
    ok(str_contains($html, '<html lang="en">'), "$path has lang=en");
    ok(str_contains($html, 'class="skip"'), "$path has a skip link");
    preg_match('~<title>(.*?)</title>~s', $html, $t);
    $title = html_entity_decode($t[1] ?? '', ENT_QUOTES);
    ok($title !== '' && mb_strlen($title) <= 60, "$path title <= 60 chars ($title)");
    preg_match('~<meta name="description" content="([^"]*)"~', $html, $d);
    $desc = html_entity_decode($d[1] ?? '', ENT_QUOTES);
    ok(mb_strlen($desc) >= 140 && mb_strlen($desc) <= 160, "$path description 140-160 chars (" . mb_strlen($desc) . ')');
    $titles[] = $title;
    $descs[] = $desc;
    ok(preg_match_all('~<h1[\s>]~', $html) === 1, "$path has exactly one h1");
    ok(str_contains($html, '<link rel="canonical" href="' . $loc . '">'), "$path canonical is $loc");
    foreach (['og:title', 'og:description', 'og:url', 'og:type', 'og:site_name', 'og:image'] as $og) {
        ok(str_contains($html, 'property="' . $og . '"'), "$path has $og");
    }
    ok(str_contains($html, 'name="twitter:card" content="summary_large_image"'), "$path has twitter:card");
    preg_match_all('~<script type="application/ld\+json">(.*?)</script>~s', $html, $js);
    ok(count($js[1]) >= 1, "$path has JSON-LD");
    foreach ($js[1] as $block) {
        ok(json_decode($block, true) !== null, "$path JSON-LD parses");
    }
    if ($path !== '/') {
        ok(str_contains($html, 'aria-label="Breadcrumb"'), "$path has breadcrumbs");
        ok(str_contains($html, '"BreadcrumbList"'), "$path has BreadcrumbList schema");
    }
    ok(str_contains($html, 'class="cta"'), "$path has the closing CTA");
    ok(!str_contains($html, $fakeWa), "$path does not contain the WhatsApp number");
    $text = strip_tags((string) preg_replace('~<(script|style)\b.*?</\1>~s', '', $html));
    ok(!preg_match('/\bAPIs?\b|\bendpoints?\b|query param/i', $text), "$path never mentions an API");
    preg_match('~<main[^>]*>(.*)</main>~s', $html, $main);
    preg_match_all('~href="(/[^"/][^"]*|/)"~', $main[1] ?? '', $hm);
    ok(count(array_unique($hm[1])) >= 3, "$path has at least 3 internal links in main");
    preg_match_all('~href="(/[^"]*)"~', $html, $am);
    foreach ($am[1] as $href) {
        if (!str_starts_with($href, '//')) {
            $links[html_entity_decode($href)][] = $path;
        }
    }
    if (!empty($routes[$path]['guide'])) {
        preg_match('~<article>(.*)</article>~s', $html, $art);
        $words = str_word_count(strip_tags((string) preg_replace('~<pre>.*?</pre>~s', '', $art[1] ?? '')));
        ok($words >= 800 && $words <= 1200, "$path guide is 800-1200 words ($words)");
        ok(str_contains($html, '"@type":"Article"'), "$path has Article schema");
    }
}
ok(count(array_unique($titles)) === count($titles), 'titles are unique');
ok(count(array_unique($descs)) === count($descs), 'descriptions are unique');
ok(str_contains($pages['/'], '"@type":"Organization"') && str_contains($pages['/'], '"@type":"WebSite"'), 'home has Organization and WebSite schema');
foreach (['/', '/hotel-database-sample/', '/data-dictionary/'] as $p) {
    ok(str_contains($pages[$p], '"@type":"Dataset"'), "$p has Dataset schema");
}
foreach (['/', '/faq/'] as $p) {
    ok(str_contains($pages[$p], '"@type":"FAQPage"'), "$p has FAQPage schema");
}

// ---------------------------------------------------------------- internal links and anchors
$cache = $pages;
foreach ($links as $href => $from) {
    $frag = parse_url($href, PHP_URL_FRAGMENT);
    $p = (string) parse_url($href, PHP_URL_PATH);
    $q = parse_url($href, PHP_URL_QUERY);
    $target = $p . ($q ? '?' . $q : '');
    if (!isset($cache[$target])) {
        [$st, $h, $body] = req('GET', $target);
        $expected = $p === '/whatsapp/' ? 302 : 200;
        ok($st === $expected, "internal link $href resolves (got $st, from {$from[0]})");
        $cache[$target] = $body;
    }
    if ($frag) {
        ok(str_contains($cache[$target], 'id="' . $frag . '"'), "anchor #$frag exists on $p (linked from {$from[0]})");
    }
}

// ---------------------------------------------------------------- status codes, redirects, denied paths
[$st, $h, $body] = req('GET', '/nope/');
ok($st === 404, '/nope/ returns 404');
ok(str_contains($body, 'noindex'), '404 page is noindex');
[$st, $h] = req('GET', '/index.php');
ok($st === 301 && ($h['location'] ?? '') === '/', '/index.php 301 to /');
[$st, $h] = req('GET', '/data');
ok($st === 301 && ($h['location'] ?? '') === '/data/', '/data 301 to /data/');
foreach (['/inc/site.php', '/data/columns.json', '/data/redirects.csv', '/docs/PLAN.md', '/storage/samples/' . $sampleName,
          '/vendor/phpmailer/src/PHPMailer.php', '/tests/smoke_test.php', '/tools/build_data.py', '/.hostinger/site.json',
          '/.github/workflows/ci.yml', '/.htaccess'] as $denied) {
    [$st] = req('GET', $denied);
    ok(in_array($st, [403, 404], true), "$denied is denied (got $st)");
}
$csv = array_map('str_getcsv', file($root . '/data/redirects.csv', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES));
array_shift($csv);
foreach ($csv as [$old, $new, $code]) {
    [$st, $h] = req('GET', $old);
    ok($st === (int) $code && ($h['location'] ?? '') === $new, "redirect $old -> $new ($st " . ($h['location'] ?? '') . ')');
}
[$st, $h, $body] = req('GET', '/robots.txt');
ok($st === 200 && str_contains($body, 'Sitemap: https://lodgingeo.com/sitemap.xml') && str_contains($body, 'Disallow: /download/'), 'robots.txt is valid');
[$st] = req('GET', '/llms.txt');
ok($st === 200, 'llms.txt returns 200');
$siteSrc = (string) file_get_contents($root . '/inc/site.php');
preg_match("~INDEXNOW_KEY\s*=\s*'([a-f0-9]+)'~", $siteSrc, $km);
[$st, $h, $body] = req('GET', '/' . $km[1] . '.txt');
ok($st === 200 && trim($body) === $km[1], 'IndexNow key file is served');
ok(filesize($root . '/assets/site.css') < 15 * 1024, 'CSS under 15 KB');
ok(filesize($root . '/assets/site.js') < 5 * 1024, 'JS under 5 KB');

// WhatsApp
[$st, $h] = req('GET', '/whatsapp/');
ok($st === 302 && str_starts_with($h['location'] ?? '', 'https://wa.me/'), '/whatsapp/ 302 to https://wa.me/');

// ---------------------------------------------------------------- sample-row blocks (Phase A: hidden)
$hasPreview = is_file($root . '/data/sample_preview.json');
ok(str_contains($pages['/'], 'Polygon grade</th>') === $hasPreview, 'home preview table shown only when sample_preview.json exists');
ok(str_contains($pages['/data/hotel-prices-by-booking-site/'], 'class="chart"') === is_file($root . '/data/rates_example.json'), 'rates chart shown only when rates_example.json exists');
ok(str_contains($pages['/data/hotel-contact-data/'], 'id="example"') === $hasPreview, 'contact example rows shown only when sample_preview.json exists');

// ---------------------------------------------------------------- sample form
[, , $html] = req('GET', '/hotel-database-sample/');
$f = form_fields($html, 'sample-form');
ok(isset($f['ts'], $f['csrf'], $f['source_page']), 'sample form has ts, csrf and source_page');
$valid = fn(string $email) => $f + ['name' => 'Test Person', 'email' => $email, 'company' => 'Example Ltd', 'countries' => 'Spain', 'opt_in' => '1'];

[$st, , $body] = req('POST', '/sample-request/', $valid('fast@example.com'));
ok(in_array($st, [400, 422], true) && str_contains($body, 'very quick'), 'too-fast submit is rejected');
ok(count_rows('leads') === 0, 'too-fast submit creates no lead');
sleep(3);
[$st] = req('POST', '/sample-request/', ['website' => 'http://spam.example'] + $valid('hp@example.com'));
ok($st === 400 && count_rows('leads') === 0, 'filled honeypot is rejected');
[$st, , $body] = req('POST', '/sample-request/', array_merge($valid(''), ['email' => '']));
ok($st === 422 && str_contains($body, 'Enter your email address.') && str_contains($body, 'aria-describedby="err-sample-email"'), 'missing email is rejected with a linked field error');
[$st] = req('POST', '/sample-request/', array_merge($valid('x@example.com'), ['csrf' => str_repeat('0', 64)]));
ok($st === 400 && count_rows('leads') === 0, 'bad CSRF token is rejected');
[$st] = req('POST', '/sample-request/', $valid('x@mailinator.com'));
ok($st === 422 && count_rows('leads') === 0, 'disposable email domain is rejected');
[$st] = req('POST', '/sample-request/', $valid('x@example.com'), ['Origin: https://evil.example']);
ok($st === 400 && count_rows('leads') === 0, 'cross-origin POST is rejected');

// Phase A pending path: no workbook on disk.
@unlink("$tmp/samples/$sampleName");
[$st, $h] = req('POST', '/sample-request/', $valid('pending@example.com'));
ok($st === 303 && str_starts_with($h['location'] ?? '', '/hotel-database-sample/thank-you/?t='), 'valid sample request redirects 303 to thank-you');
$tok1 = substr($h['location'] ?? '', strlen('/hotel-database-sample/thank-you/?t='));
ok(count_rows('leads') === 1 && preg_match('/^[a-f0-9]{64}$/', $tok1) === 1, 'valid sample request creates a lead and a token');
[$st, $h, $body] = req('GET', '/hotel-database-sample/thank-you/?t=' . $tok1);
ok($st === 200 && str_contains($body, "We're preparing the latest sample") && str_contains($body, 'noindex'), 'pending thank-you state shows (noindex)');
$mail = (string) @file_get_contents("$tmp/mail.log");
ok(str_contains($mail, '[send sample manually]'), 'owner notification is tagged [send sample manually]');
ok(str_contains($mail, '"to":"pending@example.com"') && !str_contains($mail, '/download/' . $tok1 . '/","html"'), 'visitor email sent without a link while pending');
$lead = pdo()->query("SELECT * FROM leads WHERE email='pending@example.com'")->fetch(PDO::FETCH_ASSOC);
ok($lead['company'] === 'Example Ltd' && $lead['source_page'] === '/hotel-database-sample/' && strlen($lead['ip_hash']) === 64, 'lead stores fields, source page and IP hash');

// Same email within 7 days reuses the token.
[$st, $h] = req('POST', '/sample-request/', $valid('pending@example.com'));
ok(str_ends_with($h['location'] ?? '', $tok1) && count_rows('leads') === 1, 'repeat request reuses the valid token');

// Download path: workbook present.
$xlsx = "PK\x03\x04" . str_repeat('x', 2048);
file_put_contents("$tmp/samples/$sampleName", $xlsx);
[$st, $h] = req('POST', '/sample-request/', $valid('ready@example.com'));
$tok2 = substr($h['location'] ?? '', strlen('/hotel-database-sample/thank-you/?t='));
[$st, , $body] = req('GET', '/hotel-database-sample/thank-you/?t=' . $tok2);
ok(str_contains($body, '/download/' . $tok2 . '/') && str_contains($body, "We've also emailed you the link."), 'ready thank-you shows the download button');
[$st, $h, $body] = req('GET', '/download/' . $tok2 . '/');
ok($st === 200 && $body === $xlsx, '/download/{token}/ streams the xlsx');
ok(($h['content-type'] ?? '') === 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'download Content-Type');
ok(($h['content-disposition'] ?? '') === 'attachment; filename="' . $sampleName . '"', 'download Content-Disposition');
ok(($h['x-robots-tag'] ?? '') === 'noindex' && ($h['cache-control'] ?? '') === 'private, no-store', 'download X-Robots-Tag and Cache-Control');
$cnt = (int) pdo()->query("SELECT download_count FROM leads WHERE token='$tok2'")->fetchColumn();
ok($cnt === 1, 'download increments the counter');
ok(str_contains((string) file_get_contents("$tmp/mail.log"), '/download/' . $tok2 . '/'), 'visitor email contains the download link');
[$st] = req('GET', '/download/' . str_repeat('0', 64) . '/');
ok($st === 410, 'unknown token returns 410');
$expired = str_repeat('e', 64);
pdo()->exec("INSERT INTO leads (created_at,name,email,email_domain,is_free_email,marketing_opt_in,ip_hash,token,token_expires_at,download_count)
             VALUES ('2026-01-01 00:00:00','Old','old@example.com','example.com',0,0,'" . str_repeat('a', 64) . "','$expired','2026-01-08 00:00:00',0)");
[$st, , $body] = req('GET', '/download/' . $expired . '/');
ok($st === 410 && str_contains($body, 'id="sample-form"'), 'expired token returns 410 with the form');
pdo()->exec("UPDATE leads SET download_count = 5 WHERE token='$tok2'");
[$st] = req('GET', '/download/' . $tok2 . '/');
ok($st === 410, 'token past 5 downloads returns 410');
[$st] = req('GET', '/storage/samples/' . $sampleName);
ok(in_array($st, [403, 404], true), 'sample file is not reachable by direct URL');

// ---------------------------------------------------------------- contact forms
[, , $html] = req('GET', '/contact/');
$c = form_fields($html, 'contact-form');
ok(isset($c['ts'], $c['csrf']), 'contact form has ts and csrf');
sleep(3);
[$st, , $body] = req('POST', '/contact-submit/', $c + ['name' => 'Kept Name', 'email' => 'c@example.com', 'message' => '']);
ok($st === 422 && str_contains($body, 'value="Kept Name"') && str_contains($body, 'Enter a message.'), 'contact error keeps typed values');
[$st, $h] = req('POST', '/contact-submit/', $c + ['name' => 'Contact Person', 'email' => 'c@example.com', 'company' => 'Acme',
    'countries' => 'Spain, Greece', 'data' => ['contacts', 'polygons'], 'format' => 'csv', 'frequency' => 'monthly', 'message' => 'Counts for Spain please.']);
ok($st === 303 && ($h['location'] ?? '') === '/contact/?sent=1#contact-form', 'contact form redirects 303 with success');
ok(count_rows('messages') === 1, 'contact form stores a message');
$msg = pdo()->query('SELECT * FROM messages')->fetch(PDO::FETCH_ASSOC);
ok($msg['data_needed'] === 'Contact details, Building polygons' && $msg['frequency'] === 'monthly', 'contact message stores checkboxes and options');
[$st, $h, $body] = req('GET', '/contact/?sent=1');
ok(str_contains($body, 'Your message has been sent') && str_contains($body, 'noindex'), 'contact success message shows (noindex)');
$mail = (string) file_get_contents("$tmp/mail.log");
ok(str_contains($mail, '[Lodgingeo] Contact: Contact Person') && str_contains($mail, '"reply_to":"c@example.com"'), 'owner contact notification with Reply-To');
ok(str_contains($mail, 'We received your message') && str_contains($mail, 'We reply within 24 hours.'), 'sender acknowledgement sent');
[, , $html] = req('GET', '/');
$s = form_fields($html, 'contact-short');
sleep(3);
[$st, $h] = req('POST', '/contact-submit/', $s + ['name' => 'Short Form', 'email' => 's@example.com', 'message' => 'Hello']);
ok($st === 303 && ($h['location'] ?? '') === '/?sent=1#contact', 'home short contact form works');
ok(count_rows('messages') === 2, 'short form stores a message');

echo "\n$passes passed, $fails failed\n";
exit($fails > 0 ? 1 : 0);
