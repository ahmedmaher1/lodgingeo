# lodgingeo.com

Website of Lodgingeo, the hotel-data line of Location Drive: a global hotels database sold as CSV, Excel or Google Sheets. The site explains the data, offers a free Excel sample (name and email required) and collects quote requests. It shows no prices and has no checkout.

## Stack

PHP 8.2+ with server-rendered pages and no build step, one front controller (`index.php`) behind `.htaccess` rewrites (LiteSpeed), MySQL through PDO (SQLite in tests), PHPMailer vendored in `vendor/`, one CSS file and one small JS file. Python is used locally only, in `tools/`.

## Layout

| Path | Purpose |
|---|---|
| `index.php`, `.htaccess` | Router, redirects, deny rules, caching |
| `inc/site.php` | Public settings: the single source of truth for every number and name on the site |
| `inc/routes.php` | Page registry: titles, descriptions, H1s, breadcrumbs |
| `inc/pages/`, `inc/layout/` | Page templates and partials |
| `inc/forms.php`, `inc/mail.php`, `inc/db.php` | Sample and contact forms, email, database with self-migration |
| `data/` | JSON generated from the sample workbook, FAQ, redirects |
| `storage/samples/` | The sample workbook (served only through `/download/{token}/`) |
| `tools/` | `build_data.py`, `seed_interim.py`, `make_og_image.py` |
| `tests/smoke_test.php` | End-to-end smoke test |
| `docs/` | Plan, discovery, build log, content guide, launch report |

All of `inc/`, `data/`, `storage/`, `vendor/`, `tools/`, `tests/`, `docs/` and dotfiles are denied over HTTP.

## Server config

Secrets live only on the server in `lodgingeo-config.php`, one level above `public_html`. Without it the site returns a generic 500. Keys:

```php
<?php return [
    'ENV' => 'production',
    'DB_DSN' => 'mysql:host=localhost;dbname=…;charset=utf8mb4', 'DB_USER' => '…', 'DB_PASS' => '…',
    'MAIL_TRANSPORT' => 'smtp',            // smtp | mail | log
    'SMTP_HOST' => 'smtp.hostinger.com', 'SMTP_PORT' => 465, 'SMTP_SECURE' => 'ssl', 'SMTP_USER' => '…', 'SMTP_PASS' => '…',
    'OWNER_NOTIFY_EMAIL' => '…', 'WHATSAPP_NUMBER' => '…',
    'IP_SALT' => '…', 'APP_SECRET' => '…',
    'HSTS' => true, 'ERROR_LOG' => '/home/…/lodgingeo-error.log',
    'TURNSTILE_SITE_KEY' => '', 'TURNSTILE_SECRET' => '', 'GA4_ID' => '',
];
```

Uploads through the Hostinger file API can only land inside `public_html`, so a new config is uploaded as `public_html/.lodgingeo-config.incoming.php` (denied over HTTP) and `inc/bootstrap.php` moves it one level up on the next request.

## Local development and tests

```bash
php tests/smoke_test.php
```

The smoke test starts `php -S 127.0.0.1:8080 index.php` with SQLite and `MAIL_TRANSPORT=log` and checks every page, link, redirect, denied path, both forms, downloads and tokens.

## Data (Phase B)

```bash
python tools/build_data.py          # regenerate data/*.json from storage/samples/lodgingeo-hotel-data-sample-2026-10.xlsx
python tools/build_data.py --check  # CI: fail if committed data/*.json are stale
```

## Deploy

Branch → pull request → CI → merge to `main`. Hostinger's Git auto-deploy publishes `main` to `public_html` on every push. Clear the Hostinger cache after each deploy and run the checks in `docs/LAUNCH_REPORT.md`.
