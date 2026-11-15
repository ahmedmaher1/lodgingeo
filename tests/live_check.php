<?php
/**
 * Post-deploy verification of the live site (brief §15). Read-only.
 * Usage: php tests/live_check.php [https://lodgingeo.com]
 * Env: WA_DIGITS  digits of the WhatsApp number that must never appear in page HTML (not stored in Git).
 */
declare(strict_types=1);

$base = rtrim($argv[1] ?? 'https://lodgingeo.com', '/');
$wa = preg_replace('/\D+/', '', (string) getenv('WA_DIGITS'));
require dirname(__DIR__) . '/inc/routes.php';

$fails = 0;
$passes = 0;
function ok(bool $c, string $m): void
{
    global $fails, $passes;
    $c ? $passes++ : ($fails++ || true);
    if (!$c) {
        echo "FAIL: $m\n";
    }
}

/** @return array{0:int,1:array<string,string>,2:string} */
function get(string $url, string $method = 'GET'): array
{
    $h = [];
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_CUSTOMREQUEST => $method, CURLOPT_RETURNTRANSFER => true, CURLOPT_FOLLOWLOCATION => false, CURLOPT_TIMEOUT => 30,
        CURLOPT_USERAGENT => 'lodgingeo-live-check',
        CURLOPT_HEADERFUNCTION => function ($ch, $line) use (&$h) {
            if (preg_match('/^HTTP\//', $line)) {
                $h = [];
            }
            $p = strpos($line, ':');
            if ($p !== false) {
                $k = strtolower(trim(substr($line, 0, $p)));
                $v = trim(substr($line, $p + 1));
                $h[$k] = isset($h[$k]) ? $h[$k] . ', ' . $v : $v;
            }
            return strlen($line);
        },
    ]);
    $body = (string) curl_exec($ch);
    $st = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    curl_close($ch);
    return [$st, $h, $body];
}

// Sitemap: every URL 200 over HTTPS with its real H1.
[$st, $h, $xml] = get("$base/sitemap.xml");
ok($st === 200 && @simplexml_load_string($xml) !== false, 'sitemap.xml is valid XML (200)');
preg_match_all('~<loc>([^<]+)</loc>~', $xml, $m);
ok(count($m[1]) === count(routes()), 'sitemap lists ' . count(routes()) . ' pages');
foreach ($m[1] as $loc) {
    $path = (string) parse_url($loc, PHP_URL_PATH);
    [$st, $h, $html] = get($base . $path);
    $h1 = routes()[$path]['h1'] ?? '';
    ok($st === 200, "$path 200 (got $st)");
    ok($h1 !== '' && str_contains($html, '>' . htmlspecialchars($h1, ENT_QUOTES | ENT_HTML5) . '</h1>'), "$path shows its H1 \"$h1\"");
    if ($wa !== '') {
        ok(!str_contains($html, $wa) && !str_contains($html, substr($wa, -10)), "$path does not contain the WhatsApp number");
    }
    if (in_array($path, ['/', '/hotel-database-sample/'], true)) {
        preg_match_all('~<script type="application/ld\+json">(.*?)</script>~s', $html, $js);
        foreach ($js[1] as $b) {
            ok(json_decode($b, true) !== null, "$path JSON-LD parses");
        }
        ok(count($js[1]) > 0, "$path has JSON-LD");
    }
    if ($path === '/') {
        ok(str_contains($h['content-security-policy'] ?? '', "default-src 'self'"), 'CSP header present');
        ok(($h['x-content-type-options'] ?? '') === 'nosniff', 'nosniff header present');
        ok(($h['referrer-policy'] ?? '') === 'strict-origin-when-cross-origin', 'Referrer-Policy header present');
        ok(str_starts_with($h['strict-transport-security'] ?? '', 'max-age=31536000'), 'HSTS header present');
    }
}

// Canonical redirects.
$host = (string) parse_url($base, PHP_URL_HOST);
foreach (["http://$host/" => "$base/", "https://www.$host/" => "$base/", "http://www.$host/" => "$base/", "$base/index.php" => '/', "$base/data" => '/data/'] as $from => $to) {
    [$st, $h] = get($from);
    $loc = $h['location'] ?? '';
    if ($st === 0) {
        echo "SKIP: $from not reachable from this network (plain HTTP blocked by a proxy?)\n";
        continue;
    }
    ok($st === 301 && ($loc === $to || $loc === "$base$to" || rtrim($loc, '/') === rtrim($to, '/')), "$from 301 -> $to (got $st $loc)");
}

// robots.txt
[$st, , $robots] = get("$base/robots.txt");
ok($st === 200 && str_contains($robots, "Sitemap: $base/sitemap.xml"), 'robots.txt valid');

// Denied paths.
foreach (['/.git/config', '/inc/site.php', '/data/columns.json', '/storage/samples/lodgingeo-hotel-data-sample-2026-10.xlsx', '/docs/PLAN.md',
          '/lodgingeo-config.php', '/../lodgingeo-config.php', '/.lodgingeo-config.incoming.php', '/vendor/phpmailer/src/PHPMailer.php',
          '/tests/smoke_test.php', '/.htaccess', '/.hostinger/site.json', '/data/redirects.csv'] as $p) {
    [$st] = get($base . $p);
    ok(in_array($st, [403, 404], true), "$p denied (got $st)");
}

// 404 and WhatsApp.
[$st, , $body] = get("$base/nope/");
ok($st === 404 && str_contains($body, 'noindex'), '/nope/ 404 noindex');
[$st, $h] = get("$base/whatsapp/");
ok($st === 302 && str_starts_with($h['location'] ?? '', 'https://wa.me/'), '/whatsapp/ 302 to wa.me');

echo "\n$passes passed, $fails failed\n";
exit($fails ? 1 : 0);
