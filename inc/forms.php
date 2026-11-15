<?php
/**
 * Form protection and handlers (brief §8, §9).
 *
 * CSRF and the time-trap use signed tokens rather than server sessions, so
 * HTML pages can be cached for 10 minutes without breaking forms:
 *   ts   = "<unix time>.<hmac>"   signed render time (time-trap, max age)
 *   csrf = hmac("csrf|<ts>|<form>") bound to the same render
 * plus an Origin/Referer same-host check on every POST.
 */
declare(strict_types=1);

const FREE_EMAIL_DOMAINS = [
    'gmail.com', 'googlemail.com', 'yahoo.com', 'yahoo.co.uk', 'yahoo.fr', 'yahoo.es', 'yahoo.de', 'ymail.com',
    'hotmail.com', 'hotmail.co.uk', 'hotmail.fr', 'hotmail.es', 'hotmail.de', 'hotmail.it', 'outlook.com', 'outlook.es',
    'live.com', 'live.co.uk', 'msn.com', 'icloud.com', 'me.com', 'mac.com', 'aol.com', 'gmx.com', 'gmx.de', 'gmx.net',
    'web.de', 'mail.com', 'mail.ru', 'yandex.com', 'yandex.ru', 'proton.me', 'protonmail.com', 'pm.me', 'zoho.com',
    'qq.com', '163.com', '126.com', 'sina.com', 'naver.com', 'daum.net', 'libero.it', 'orange.fr', 'free.fr',
    'laposte.net', 'wanadoo.fr', 't-online.de', 'freenet.de', 'seznam.cz', 'wp.pl', 'o2.pl', 'interia.pl', 'rediffmail.com',
    'fastmail.com', 'hey.com', 'tutanota.com', 'tuta.io', 'rocketmail.com', 'att.net', 'comcast.net', 'verizon.net',
];

const DATA_NEEDED_OPTIONS = [
    'contacts'  => 'Contact details',
    'prices'    => 'Prices by booking site',
    'polygons'  => 'Building polygons',
    'location'  => 'Location & neighbourhood',
    'details'   => 'Hotel details',
    'changes'   => 'Monthly changes',
    'matching'  => 'List matching',
];
const FORMAT_OPTIONS    = ['csv' => 'CSV', 'excel' => 'Excel', 'sheets' => 'Google Sheets'];
const FREQUENCY_OPTIONS = ['one-off' => 'One-off', 'monthly' => 'Monthly'];

function app_secret(): string
{
    return (string) cfg('APP_SECRET', 'unset-secret');
}

function form_ts(): string
{
    $t = (string) time();
    return $t . '.' . substr(hash_hmac('sha256', 'ts|' . $t, app_secret()), 0, 32);
}

function form_csrf(string $ts, string $form): string
{
    return hash_hmac('sha256', 'csrf|' . $ts . '|' . $form, app_secret());
}

/** Hidden fields shared by every form. */
function form_hidden_fields(string $form, string $sourcePage): string
{
    $ts = form_ts();
    $utm = utm_values();
    $h  = '<input type="hidden" name="ts" value="' . e($ts) . '">'
        . '<input type="hidden" name="csrf" value="' . e(form_csrf($ts, $form)) . '">'
        . '<input type="hidden" name="source_page" value="' . e($sourcePage) . '">';
    foreach (['utm_source', 'utm_medium', 'utm_campaign', 'utm_term'] as $k) {
        $h .= '<input type="hidden" name="' . $k . '" value="' . e($utm[$k] ?? '') . '" data-persist="' . $k . '">';
    }
    $h .= '<input type="hidden" name="referrer" value="" data-persist="referrer">'
        . '<input type="hidden" name="landing_page" value="" data-persist="landing_page">';
    // Honeypot: visually hidden, not announced, skipped by keyboard.
    $h .= '<div class="hp" aria-hidden="true"><label for="website-' . e($form) . '">Leave this field empty</label>'
        . '<input type="text" id="website-' . e($form) . '" name="website" value="" tabindex="-1" autocomplete="off"></div>';
    if (turnstile_enabled()) {
        $h .= '<div class="cf-turnstile" data-sitekey="' . e(cfg('TURNSTILE_SITE_KEY')) . '"></div>';
    }
    return $h;
}

/** UTM values from the current URL, falling back to the session (server-side persistence). */
function utm_values(): array
{
    $out = [];
    foreach (['utm_source', 'utm_medium', 'utm_campaign', 'utm_term'] as $k) {
        if (isset($_GET[$k]) && is_string($_GET[$k])) {
            $out[$k] = mb_substr(trim($_GET[$k]), 0, 200);
        } elseif (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['utm'][$k])) {
            $out[$k] = (string) $_SESSION['utm'][$k];
        }
    }
    return $out;
}

/** Remember UTM values, landing page and external referrer in the session on the landing request. */
function remember_attribution(): void
{
    $hasUtm = false;
    foreach (['utm_source', 'utm_medium', 'utm_campaign', 'utm_term'] as $k) {
        if (!empty($_GET[$k])) {
            $hasUtm = true;
        }
    }
    $ref = (string) ($_SERVER['HTTP_REFERER'] ?? '');
    $external = $ref !== '' && parse_url($ref, PHP_URL_HOST) !== ($_SERVER['HTTP_HOST'] ?? '');
    if (!$hasUtm && !$external) {
        return;
    }
    start_session();
    if ($hasUtm) {
        $_SESSION['utm'] = utm_values();
    }
    $_SESSION['landing_page'] ??= mb_substr((string) ($_SERVER['REQUEST_URI'] ?? '/'), 0, 500);
    if ($external) {
        $_SESSION['referrer'] ??= mb_substr($ref, 0, 500);
    }
}

function turnstile_enabled(): bool
{
    return (string) cfg('TURNSTILE_SITE_KEY', '') !== '' && (string) cfg('TURNSTILE_SECRET', '') !== '';
}

function turnstile_verify(string $token): bool
{
    if ($token === '' || !function_exists('curl_init')) {
        return false;
    }
    $ch = curl_init('https://challenges.cloudflare.com/turnstile/v0/siteverify');
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => http_build_query(['secret' => cfg('TURNSTILE_SECRET'), 'response' => $token, 'remoteip' => client_ip()]),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 8,
    ]);
    $res = curl_exec($ch);
    curl_close($ch);
    $j = is_string($res) ? json_decode($res, true) : null;
    return is_array($j) && !empty($j['success']);
}

function post_str(string $key, int $max = 500): string
{
    $v = $_POST[$key] ?? '';
    if (!is_string($v)) {
        return '';
    }
    $v = trim(str_replace("\0", '', $v));
    return mb_substr($v, 0, $max);
}

/**
 * Anti-spam and CSRF checks common to all forms.
 * @return string|null an error message for the visitor, or null when the request passes
 */
function check_form_protection(string $form): ?string
{
    $generic = 'Your form could not be sent. Please wait a moment and submit it again.';

    // Same-origin check.
    $host = $_SERVER['HTTP_HOST'] ?? '';
    foreach (['HTTP_ORIGIN', 'HTTP_REFERER'] as $h) {
        if (!empty($_SERVER[$h])) {
            if (parse_url((string) $_SERVER[$h], PHP_URL_HOST) !== parse_url('//' . $host, PHP_URL_HOST)) {
                return $generic;
            }
            break;
        }
    }
    // Honeypot.
    if (post_str('website') !== '') {
        return $generic;
    }
    // Signed timestamp.
    $ts = post_str('ts', 100);
    if (!preg_match('/^(\d{9,11})\.([a-f0-9]{32})$/', $ts, $m)
        || !hash_equals(substr(hash_hmac('sha256', 'ts|' . $m[1], app_secret()), 0, 32), $m[2])) {
        return $generic;
    }
    $age = time() - (int) $m[1];
    if ($age < (int) cfg('MIN_SUBMIT_SECONDS', 3)) {
        return 'That was very quick. Please check your details and submit the form again.';
    }
    if ($age > 86400) {
        return 'This form has expired. Please submit it again.';
    }
    // CSRF token bound to the render.
    if (!hash_equals(form_csrf($ts, $form), post_str('csrf', 100))) {
        return $generic;
    }
    if (turnstile_enabled() && !turnstile_verify(post_str('cf-turnstile-response', 4096))) {
        return 'Please complete the verification and submit again.';
    }
    return null;
}

function rate_count(string $kind, string $key, int $seconds): int
{
    $stmt = db()->prepare('SELECT COUNT(*) FROM rate_events WHERE kind = ? AND key_hash = ? AND created_at > ?');
    $stmt->execute([$kind, $key, gmdate('Y-m-d H:i:s', time() - $seconds)]);
    return (int) $stmt->fetchColumn();
}

function rate_record(string $kind, string $key): void
{
    db()->prepare('INSERT INTO rate_events (kind, key_hash, created_at) VALUES (?, ?, ?)')->execute([$kind, $key, now_utc()]);
    if (random_int(1, 20) === 1) {
        purge_old_records();
    }
}

/** Retention (see /privacy/): submissions 24 months, rate-limit records 2 days. */
function purge_old_records(): void
{
    $pdo = db();
    $pdo->prepare('DELETE FROM rate_events WHERE created_at < ?')->execute([gmdate('Y-m-d H:i:s', time() - 2 * 86400)]);
    $cut = gmdate('Y-m-d H:i:s', strtotime('-24 months'));
    $pdo->prepare('DELETE FROM leads WHERE created_at < ?')->execute([$cut]);
    $pdo->prepare('DELETE FROM messages WHERE created_at < ?')->execute([$cut]);
}

function email_key(string $email): string
{
    return hash_hmac('sha256', strtolower($email), (string) cfg('IP_SALT', 'unset'));
}

/** @return string|null error for the visitor */
function check_rate_limits(string $email): ?string
{
    if (rate_count('ip', ip_hash(), 3600) >= (int) cfg('RATE_LIMIT_IP_HOUR', 5)) {
        return 'Too many requests from your connection. Please try again in an hour, or email ' . PUBLIC_EMAIL . '.';
    }
    if ($email !== '' && rate_count('email', email_key($email), 86400) >= (int) cfg('RATE_LIMIT_EMAIL_DAY', 3)) {
        return 'We have already received several requests for this email address today. Please check your inbox, or email ' . PUBLIC_EMAIL . '.';
    }
    return null;
}

function disposable_domains(): array
{
    static $list = null;
    if ($list === null) {
        $list = [];
        $file = ROOT . '/data/disposable_domains.txt';
        if (is_file($file)) {
            foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
                $line = strtolower(trim($line));
                if ($line !== '' && $line[0] !== '#') {
                    $list[$line] = true;
                }
            }
        }
    }
    return $list;
}

/** @return string|null error message for the email field */
function validate_email(string $email): ?string
{
    if ($email === '') {
        return 'Enter your email address.';
    }
    if (strlen($email) > 254 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return 'Enter a valid email address, like name@company.com.';
    }
    $domain = strtolower(substr(strrchr($email, '@') ?: '', 1));
    if (isset(disposable_domains()[$domain])) {
        return 'Please use a permanent email address; disposable addresses are not accepted.';
    }
    if (cfg('CHECK_DNS', true) && !checkdnsrr($domain, 'MX') && !checkdnsrr($domain, 'A')) {
        return 'We could not find mail servers for ' . $domain . '. Please check the address.';
    }
    return null;
}

function validate_name(string $name): ?string
{
    $len = mb_strlen($name);
    if ($len === 0) {
        return 'Enter your full name.';
    }
    if ($len < 2 || $len > 100) {
        return 'Your name should be between 2 and 100 characters.';
    }
    return null;
}

function attribution_from_post(): array
{
    $sess = session_status() === PHP_SESSION_ACTIVE ? $_SESSION : [];
    $a = [];
    foreach (['utm_source', 'utm_medium', 'utm_campaign', 'utm_term'] as $k) {
        $a[$k] = post_str($k, 200) ?: (string) ($sess['utm'][$k] ?? '');
    }
    $a['referrer']     = post_str('referrer', 500) ?: (string) ($sess['referrer'] ?? '');
    $a['landing_page'] = post_str('landing_page', 500) ?: (string) ($sess['landing_page'] ?? '');
    $a['source_page']  = post_str('source_page', 300);
    return $a;
}

function form_state(string $form): array
{
    return $GLOBALS['FORM_STATE'][$form] ?? ['errors' => [], 'values' => [], 'general' => null];
}

function field_error(array $state, string $field): string
{
    $err = $state['errors'][$field] ?? null;
    return $err ? '<p class="field-error" id="err-' . e($state['form'] ?? 'f') . '-' . e($field) . '">' . e($err) . '</p>' : '';
}

/** aria attributes tying a field to its error message. */
function field_aria(array $state, string $field): string
{
    return isset($state['errors'][$field])
        ? ' aria-invalid="true" aria-describedby="err-' . e($state['form'] ?? 'f') . '-' . e($field) . '"'
        : '';
}

function new_token(): string
{
    return bin2hex(random_bytes(32));
}

/* ------------------------------------------------------------------ */
/* Sample request (§8)                                                 */
/* ------------------------------------------------------------------ */

function handle_sample_request(): void
{
    start_session();
    $v = [
        'name'      => post_str('name', 100),
        'email'     => strtolower(post_str('email', 254)),
        'company'   => post_str('company', 200),
        'countries' => post_str('countries', 500),
        'opt_in'    => isset($_POST['opt_in']) ? 1 : 0,
    ];
    $attr = attribution_from_post();
    $errors = [];
    $general = check_form_protection('sample');
    if ($general === null) {
        if ($e = validate_name($v['name'])) {
            $errors['name'] = $e;
        }
        if ($e = validate_email($v['email'])) {
            $errors['email'] = $e;
        }
        if (!$errors) {
            $general = check_rate_limits($v['email']);
        }
    }
    if ($general !== null || $errors) {
        $GLOBALS['FORM_STATE']['sample'] = ['form' => 'sample', 'errors' => $errors, 'values' => $v, 'general' => $general ?? 'Please correct the fields marked below.'];
        $source = sample_form_page($attr['source_page']);
        render_route($source, $general !== null && !$errors ? 400 : 422);
        exit;
    }

    $domain = substr(strrchr($v['email'], '@') ?: '', 1);
    $isFree = in_array($domain, FREE_EMAIL_DOMAINS, true) ? 1 : 0;
    $pdo = db();

    // Reuse a still-valid token for the same email within 7 days.
    $stmt = $pdo->prepare('SELECT id, token FROM leads WHERE email = ? AND token_expires_at > ? AND download_count < 5 ORDER BY id DESC LIMIT 1');
    $stmt->execute([$v['email'], now_utc()]);
    $existing = $stmt->fetch();
    $repeat = (bool) $existing;
    if ($existing) {
        $token = $existing['token'];
    } else {
        $token = new_token();
        $pdo->prepare('INSERT INTO leads (created_at, name, email, email_domain, is_free_email, company, countries, marketing_opt_in,
                source_page, landing_page, referrer, utm_source, utm_medium, utm_campaign, utm_term, ip_hash, user_agent, token, token_expires_at)
                VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)')
            ->execute([
                now_utc(), $v['name'], $v['email'], $domain, $isFree, $v['company'] ?: null, $v['countries'] ?: null, $v['opt_in'],
                $attr['source_page'] ?: null, $attr['landing_page'] ?: null, $attr['referrer'] ?: null,
                $attr['utm_source'] ?: null, $attr['utm_medium'] ?: null, $attr['utm_campaign'] ?: null, $attr['utm_term'] ?: null,
                ip_hash(), mb_substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 500), $token, gmdate('Y-m-d H:i:s', time() + 7 * 86400),
            ]);
    }
    rate_record('ip', ip_hash());
    rate_record('email', email_key($v['email']));

    $ready = sample_available();
    $link  = abs_url('/download/' . $token . '/');
    send_mail($v['email'], 'Your Lodgingeo hotel data sample', sample_visitor_email($v['name'], $ready ? $link : null),
        text_to_html(sample_visitor_email($v['name'], $ready ? $link : null)));

    $tags = ($ready ? '' : ' [send sample manually]') . ($repeat ? ' [repeat request]' : '');
    $who  = $v['company'] !== '' ? $v['company'] : $domain;
    $body = "New sample request" . ($ready ? '' : " (no sample file on the server: send it manually)") . "\n\n"
        . "Name: {$v['name']}\nEmail: {$v['email']}\nFree-mail address: " . ($isFree ? 'yes' : 'no') . "\n"
        . "Company: {$v['company']}\nCountries: {$v['countries']}\nMonthly release emails: " . ($v['opt_in'] ? 'yes' : 'no') . "\n"
        . "Repeat request (token reused): " . ($repeat ? 'yes' : 'no') . "\n\n"
        . "Source page: {$attr['source_page']}\nLanding page: {$attr['landing_page']}\nReferrer: {$attr['referrer']}\n"
        . "utm_source: {$attr['utm_source']}\nutm_medium: {$attr['utm_medium']}\nutm_campaign: {$attr['utm_campaign']}\nutm_term: {$attr['utm_term']}\n\n"
        . "Download link: $link\n";
    send_mail((string) cfg('OWNER_NOTIFY_EMAIL', PUBLIC_EMAIL), "[Lodgingeo] Sample: {$v['name']} · {$who}" . $tags, $body, null, $v['email'], $v['name']);

    redirect('/hotel-database-sample/thank-you/?t=' . $token, 303);
}

function sample_visitor_email(string $name, ?string $link): string
{
    $hotels = n(stat_val('sheets.Hotels.rows'));
    $countries = n(stat_val('countries.count'));
    $first = $link !== null
        ? "Here is your link to the Lodgingeo hotel data sample (release " . SAMPLE_RELEASE . "):\n$link\n\nThe link works for 7 days and up to 5 downloads."
        : "Thank you for requesting the Lodgingeo hotel data sample. We're preparing the latest sample and will email it to you shortly.";
    return "Hello $name,\n\n$first\n\n"
        . "What's inside the Excel file:\n"
        . "- $hotels hotels in $countries countries, with location, contact details, ratings, rates by booking site and building polygons where available.\n"
        . "- A column guide that explains every field.\n"
        . "- Monthly change records since " . month_label(HISTORY_START) . " and counts of nearby places for each hotel.\n\n"
        . "Reply to this email with the countries and fields you need and I'll send counts and a quote.\n\n"
        . SIGNATURE_NAME . "\n" . CANONICAL_URL . "/\n";
}

/** Pages that carry the sample form; errors re-render there. */
function sample_form_page(string $source): string
{
    $routes = routes();
    if (isset($routes[$source]) && !empty($routes[$source]['sample_form'])) {
        return $source;
    }
    return '/hotel-database-sample/';
}

/* ------------------------------------------------------------------ */
/* Contact (§9)                                                        */
/* ------------------------------------------------------------------ */

function handle_contact_submit(): void
{
    start_session();
    $form = post_str('form', 10) === 'short' ? 'short' : 'full';
    $v = [
        'name'      => post_str('name', 100),
        'email'     => strtolower(post_str('email', 254)),
        'company'   => post_str('company', 200),
        'countries' => post_str('countries', 500),
        'message'   => post_str('message', 5000),
        'format'    => array_key_exists(post_str('format', 20), FORMAT_OPTIONS) ? post_str('format', 20) : '',
        'frequency' => array_key_exists(post_str('frequency', 20), FREQUENCY_OPTIONS) ? post_str('frequency', 20) : '',
        'data'      => array_values(array_intersect(array_keys(DATA_NEEDED_OPTIONS), is_array($_POST['data'] ?? null) ? $_POST['data'] : [])),
    ];
    $attr = attribution_from_post();
    $errors = [];
    $general = check_form_protection('contact-' . $form);
    if ($general === null) {
        if ($e = validate_name($v['name'])) {
            $errors['name'] = $e;
        }
        if ($e = validate_email($v['email'])) {
            $errors['email'] = $e;
        }
        if (mb_strlen($v['message']) < 2) {
            $errors['message'] = 'Enter a message.';
        }
        if (!$errors) {
            $general = check_rate_limits($v['email']);
        }
    }
    $source = $form === 'short' ? '/' : '/contact/';
    if ($general !== null || $errors) {
        $GLOBALS['FORM_STATE']['contact-' . $form] = ['form' => 'contact-' . $form, 'errors' => $errors, 'values' => $v, 'general' => $general ?? 'Please correct the fields marked below.'];
        render_route($source, $general !== null && !$errors ? 400 : 422);
        exit;
    }

    $dataLabels = array_map(fn($k) => DATA_NEEDED_OPTIONS[$k], $v['data']);
    db()->prepare('INSERT INTO messages (created_at, form, name, email, company, countries, data_needed, format, frequency, message,
            source_page, landing_page, referrer, utm_source, utm_medium, utm_campaign, utm_term, ip_hash, user_agent)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)')
        ->execute([
            now_utc(), $form, $v['name'], $v['email'], $v['company'] ?: null, $v['countries'] ?: null,
            $dataLabels ? implode(', ', $dataLabels) : null, $v['format'] ?: null, $v['frequency'] ?: null, $v['message'],
            $attr['source_page'] ?: null, $attr['landing_page'] ?: null, $attr['referrer'] ?: null,
            $attr['utm_source'] ?: null, $attr['utm_medium'] ?: null, $attr['utm_campaign'] ?: null, $attr['utm_term'] ?: null,
            ip_hash(), mb_substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 500),
        ]);
    rate_record('ip', ip_hash());
    rate_record('email', email_key($v['email']));

    $domain = substr(strrchr($v['email'], '@') ?: '', 1);
    $isFree = in_array($domain, FREE_EMAIL_DOMAINS, true);
    $details = "Name: {$v['name']}\nEmail: {$v['email']}\nCompany: {$v['company']}\nCountries: {$v['countries']}\n"
        . "Data needed: " . implode(', ', $dataLabels) . "\nFormat: " . (FORMAT_OPTIONS[$v['format']] ?? '') . "\n"
        . "One-off or monthly: " . (FREQUENCY_OPTIONS[$v['frequency']] ?? '') . "\n\nMessage:\n{$v['message']}\n";
    $owner = "New message from the " . ($form === 'short' ? 'home page' : 'contact page') . " form\n\n" . $details
        . "\nFree-mail address: " . ($isFree ? 'yes' : 'no') . "\nSource page: {$attr['source_page']}\nLanding page: {$attr['landing_page']}\n"
        . "Referrer: {$attr['referrer']}\nutm_source: {$attr['utm_source']}\nutm_medium: {$attr['utm_medium']}\n"
        . "utm_campaign: {$attr['utm_campaign']}\nutm_term: {$attr['utm_term']}\n";
    $who = $v['company'] !== '' ? $v['company'] : $domain;
    send_mail((string) cfg('OWNER_NOTIFY_EMAIL', PUBLIC_EMAIL), "[Lodgingeo] Contact: {$v['name']} · {$who}", $owner, null, $v['email'], $v['name']);

    $ack = "Hello {$v['name']},\n\nThank you for writing to Lodgingeo. " . REPLY_PROMISE . "\n\nA copy of your message:\n\n" . $details
        . "\n" . SIGNATURE_NAME . "\n" . CANONICAL_URL . "/\n";
    send_mail($v['email'], 'We received your message · Lodgingeo', $ack, text_to_html($ack));

    redirect($source . '?sent=1#' . ($form === 'short' ? 'contact' : 'contact-form'), 303);
}
