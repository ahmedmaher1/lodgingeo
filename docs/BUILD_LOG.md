# Build log

## 2026-11-15 — Phase A

### Discovery
- See `docs/DISCOVERY.md`. Key findings: the Hostinger site was a Node.js app (from the earlier placeholder), GitHub auto-deploy on `main` already active, MX points to Google, no Hostinger mail order.
- Tag `pre-rebuild-2026-11` created locally; pushing tags is refused by this session's Git proxy (HTTP 403), so the same commit (10b459c) is on branch `archive/pre-rebuild-2026-11`.
- No legacy store existed, so no `legacy/store-2026` branch.
- The build container's network allowlist did not include lodgingeo.com; a personal `lodgingeo` environment was created (lodgingeo.com, www.lodgingeo.com, api.indexnow.org, package registries) for live verification and IndexNow.

### Build decisions not covered by the brief
- **CSRF without sessions.** CSRF tokens are HMACs bound to a signed render timestamp, plus a same-origin check on every POST. This keeps HTML cacheable for 10 minutes (§11) while forms still carry a CSRF token and a signed timestamp (§8). Sessions start only when a visitor lands with UTM tags or an external referrer (server-side attribution fallback).
- **Config outside the web root.** The Hostinger upload API writes only inside `public_html`, so `inc/bootstrap.php` moves an uploaded `public_html/.lodgingeo-config.incoming.php` to `../lodgingeo-config.php` on the next request. The incoming file is HTTP-denied (dotfile rule) and outputs nothing if executed.
- **Mail transport.** No Hostinger mailbox can be created (no mail order; MX at Google). The server config starts with `MAIL_TRANSPORT = mail` (PHP `mail()` on Hostinger) so notifications go out now; switching to SMTP is a config change once credentials exist.
- **City column.** Public pages use `ld_city` (our matched city), never the client-input `city`, to respect the privacy rule in §10.
- **Interim meanings.** `columns.json` carries one-line meanings written from the brief for Phase A; Phase B replaces them with the Column guide text. Change-type meanings on the history page (`field_added`, `reopened`, …) and the descriptions of `neighbourhood_momentum_12m` and `stable_recommendations_*` are my reading of the names; check them against the Column guide.
- **Sample coverage.** Only the top 10 of the 32 sample countries are known before Phase B, so `/coverage/` lists those ten; the full list is generated from the workbook. Kinds of places to stay are listed only once the workbook's `category` / `categories` values exist.
- **Release log.** Per-month change counts are not in the brief, so `/releases/` lists the 22 months without counts until Phase B (`releases.json` then carries counts by `cycle_month`).
- **Sample file size.** Left out until Phase B reads it from the final file.
- **Redirects.** Git history only ever served `/`. The pre-repository site's URLs are unknown, so `data/redirects.csv` maps common legacy patterns of a hotel-database site (e.g. `/sample`, `/pricing`, `/contact-us`, `/hotel-email-list`) to the nearest topical page, never all to `/`. Search Console's "Not found" report will reveal the real old URLs; add them to the CSV.
- **Link underline.** In-text links have a light underline (darker on hover) because colour-only links fail WCAG 1.4.1 (Lighthouse `link-in-text-block`). Navigation and buttons underline on hover only.
- **Retention.** A purge runs on roughly 1 in 20 form submissions: leads and messages older than 24 months, rate-limit records older than 2 days (as stated on `/privacy/`).
- **Sample licence.** `/terms/` states that the free sample is covered by the data licence.
- **Free-mail list.** Gmail, Outlook, Yahoo and ~60 other consumer domains are flagged (not rejected) in owner notifications.

### Tests
- `php tests/smoke_test.php`: 941 checks passed (26 sitemap pages, every internal link and anchor, redirects, denied paths, budgets, both forms, pending and download paths, tokens, WhatsApp).

### Lighthouse (mobile, local `php -S`, Lighthouse 12)

| Page | Performance | Accessibility | Best practices | SEO |
|---|---|---|---|---|
| `/` | 100 | 100 | 100 | 100 |
| `/hotel-database-sample/` | 100 | 100 | 100 | 100 |
| `/data/hotel-prices-by-booking-site/` | 100 | 100 | 100 | 100 |
| `/data-dictionary/` | 100 | 100 | 100 | 100 |
| `/contact/` | 100 | 100 | 100 | 100 |

First run scored Accessibility 96 (`link-in-text-block`); fixed as noted above.

### Deploy (2026-11-15)
- PR #2 (CI green on PHP 8.2) squash-merged. The Node.js auto-deploy built `main` and failed (no package.json); the old placeholder stayed live.
- `hosting_deploy` with `type: static` refused: the website is registered as Node.js. Deployed `main` as an archive (`hosting_websites_deploy-static-site-archive`). The site returned the intended fail-closed 500 until the config was uploaded and moved out of `public_html` on the next request.
- Database `u742794872_lodgingeo` created; the schema self-migrated on first use.
- PHP switched 8.3 → 8.5; cache cleared; Node.js Git auto-deployment removed.
- PR #3 added `tests/live_check.php` and the maintenance hook; redeployed by archive.
- Live forms tested with `maher+test-*@lodgingeo.com`, rows verified and then erased; error log empty. IndexNow ping: HTTP 202.
- Hostinger upload credentials expire within a few hours; generate new ones for each upload.

### Website recreated as PHP (2026-11-15)
- Backed up leads and messages with the new maintenance `export` job (both empty), deleted the Node.js website entry, recreated `lodgingeo.com` as a regular website on order 68992684. The database, SSL and CDN settings survived.
- `hosting_deploy` (git, `main`, type static/PHP) with auto-deploy; PHP 8.5; config re-uploaded; live check 111/111.
- SPF widened to include Google Workspace and the server's IPv4/IPv6 addresses.
