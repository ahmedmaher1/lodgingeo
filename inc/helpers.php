<?php
declare(strict_types=1);

/** Escape for HTML output. */
function e(mixed $v): string
{
    return htmlspecialchars((string) ($v ?? ''), ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8');
}

/** Absolute canonical URL for a site path. */
function abs_url(string $path = '/'): string
{
    return CANONICAL_URL . $path;
}

/** Asset URL with a content-hash cache buster. */
function asset(string $file): string
{
    static $cache = [];
    if (!isset($cache[$file])) {
        $path = ROOT . '/' . ltrim($file, '/');
        $cache[$file] = '/' . ltrim($file, '/') . '?v=' . (is_file($path) ? substr(md5_file($path), 0, 10) : '0');
    }
    return $cache[$file];
}

/** Load a data/*.json file; null when the file does not exist (blocks then stay hidden). */
function data_json(string $name): ?array
{
    static $cache = [];
    if (!array_key_exists($name, $cache)) {
        $file = ROOT . '/data/' . $name . '.json';
        $cache[$name] = is_file($file) ? json_decode((string) file_get_contents($file), true, 512, JSON_THROW_ON_ERROR) : null;
    }
    return $cache[$name];
}

/** Read a nested value from sample_stats.json, null when missing. */
function stat_val(string $path): mixed
{
    $v = data_json('sample_stats');
    foreach (explode('.', $path) as $k) {
        if (!is_array($v) || !array_key_exists($k, $v)) {
            return null;
        }
        $v = $v[$k];
    }
    return $v;
}

function n(int|float|null $v, int $dec = 0): string
{
    return $v === null ? '' : number_format((float) $v, $dec);
}

/** Percentage of $part in $total, rounded. */
function pct(int|float|null $part, int|float|null $total): string
{
    if (!$part || !$total) {
        return '0%';
    }
    return round($part / $total * 100) . '%';
}

/** Column name as inline code, linked to its dictionary anchor. */
function col(string $name, bool $link = true): string
{
    $code = '<code>' . e($name) . '</code>';
    return $link ? '<a href="' . e(col_href($name)) . '">' . $code . '</a>' : $code;
}

/** Dictionary link: sample columns have #name, full-dataset-only fields #full-name. */
function col_href(string $name): string
{
    return '/data-dictionary/#' . (in_sample($name) ? '' : 'full-') . col_anchor($name);
}

/** Meaning of a sample column from columns.json, or null. */
function col_meaning(string $name): ?string
{
    foreach (data_json('columns')['sheets'] ?? [] as $sheet) {
        foreach ($sheet['columns'] ?? [] as $c) {
            if ($c['name'] === $name) {
                return $c['meaning'] ?? null;
            }
        }
    }
    return null;
}

/**
 * Table of fields: [column => meaning|null]. Meaning falls back to columns.json.
 */
function fields_table(array $fields, string $caption): string
{
    $h = '<div class="table-wrap"><table><caption>' . e($caption) . '</caption><thead><tr><th scope="col">Column</th>'
       . '<th scope="col">Meaning</th><th scope="col">Availability</th></tr></thead><tbody>';
    foreach ($fields as $name => $meaning) {
        if (is_int($name)) {
            $name = $meaning;
            $meaning = null;
        }
        $h .= '<tr><th scope="row">' . col($name) . '</th><td>' . e($meaning ?? col_meaning($name) ?? '') . '</td><td>' . sample_badge($name) . '</td></tr>';
    }
    return $h . '</tbody></table></div>';
}

function col_anchor(string $name): string
{
    return trim((string) preg_replace('/[^a-z0-9_]+/', '-', strtolower($name)), '-');
}

/** Names of the Hotels-sheet columns present in the sample (from columns.json). */
function sample_column_names(): array
{
    static $names = null;
    if ($names === null) {
        $names = [];
        foreach (data_json('columns')['sheets'] ?? [] as $sheet) {
            foreach ($sheet['columns'] ?? [] as $c) {
                $names[$c['name']] = true;
            }
        }
    }
    return $names;
}

function in_sample(string $column): bool
{
    return isset(sample_column_names()[$column]);
}

/** "full dataset" label for fields that are not in the sample header row. */
function sample_badge(string $column): string
{
    return in_sample($column) ? '<span class="tag">in sample</span>' : '<span class="tag tag-muted">full dataset</span>';
}

function redirect(string $location, int $status = 302): never
{
    header('Location: ' . $location, true, $status);
    header('Cache-Control: no-store');
    exit;
}

function request_path(): string
{
    $p = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    return is_string($p) && $p !== '' ? rawurldecode($p) : '/';
}

function client_ip(): string
{
    return (string) ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
}

function ip_hash(): string
{
    return hash_hmac('sha256', client_ip(), (string) cfg('IP_SALT', 'unset'));
}

function now_utc(): string
{
    return gmdate('Y-m-d H:i:s');
}

/** Format a YYYY-MM release as "October 2026". */
function month_label(string $ym): string
{
    $d = DateTime::createFromFormat('!Y-m', $ym, new DateTimeZone('UTC'));
    return $d ? $d->format('F Y') : $ym;
}

function sample_file_path(): string
{
    return rtrim((string) cfg('SAMPLE_DIR', ROOT . '/storage/samples'), '/') . '/' . SAMPLE_FILENAME;
}

function sample_available(): bool
{
    return is_file(sample_file_path());
}

function whatsapp_link(bool $withLabel = true): string
{
    $svg = '<svg class="icon" width="20" height="20" viewBox="0 0 24 24" aria-hidden="true" focusable="false">'
         . '<path fill="currentColor" d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.3A10 10 0 1 0 12 2zm0 18.2a8.2 8.2 0 0 1-4.2-1.2l-.3-.2-3 .8.8-2.9-.2-.3A8.2 8.2 0 1 1 12 20.2zm4.5-6.1c-.2-.1-1.5-.7-1.7-.8s-.4-.1-.6.1-.7.8-.8 1-.3.2-.5.1a6.7 6.7 0 0 1-3.3-2.9c-.3-.4.2-.4.7-1.3.1-.2 0-.3 0-.4l-.8-1.8c-.2-.5-.4-.4-.6-.4h-.5a1 1 0 0 0-.7.3 2.9 2.9 0 0 0-.9 2.2 5 5 0 0 0 1 2.7 11.5 11.5 0 0 0 4.4 3.9c1.6.7 2.3.8 3.1.6a2.7 2.7 0 0 0 1.8-1.2 2.2 2.2 0 0 0 .2-1.3c-.1-.1-.3-.2-.5-.3z"/></svg>';
    return '<a class="wa" href="/whatsapp/" rel="nofollow" aria-label="Chat with us on WhatsApp">' . $svg
         . ($withLabel ? '<span aria-hidden="true">WhatsApp</span>' : '') . '</a>';
}

/** Render a partial from inc/layout with local variables. */
function partial(string $name, array $vars = []): string
{
    extract($vars, EXTR_SKIP);
    ob_start();
    include ROOT . '/inc/layout/' . $name . '.php';
    return (string) ob_get_clean();
}
