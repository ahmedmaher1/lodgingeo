# Launch report: lodgingeo.com, Phase A (2026-11-15)

**Live:** https://lodgingeo.com/ (PHP 8.5 on Hostinger Business, MySQL, LiteSpeed, Hostinger CDN, SSL active)

## Live URLs (26, all in /sitemap.xml)

| Page | URL |
|---|---|
| Home | https://lodgingeo.com/ |
| Free sample | https://lodgingeo.com/hotel-database-sample/ |
| Data overview | https://lodgingeo.com/data/ |
| Contact details | https://lodgingeo.com/data/hotel-contact-data/ |
| Prices by booking site | https://lodgingeo.com/data/hotel-prices-by-booking-site/ |
| Building polygons | https://lodgingeo.com/data/hotel-building-polygons/ |
| Location & neighbourhood | https://lodgingeo.com/data/hotel-location-data/ |
| Details & amenities | https://lodgingeo.com/data/hotel-details-and-amenities/ |
| History & changes | https://lodgingeo.com/data/hotel-history-and-changes/ |
| List matching | https://lodgingeo.com/hotel-matching/ |
| Data dictionary | https://lodgingeo.com/data-dictionary/ |
| Coverage | https://lodgingeo.com/coverage/ |
| Use cases | https://lodgingeo.com/use-cases/ |
| Formats & delivery | https://lodgingeo.com/formats-and-delivery/ |
| Methodology | https://lodgingeo.com/methodology/ |
| Release log | https://lodgingeo.com/releases/ |
| Guides | https://lodgingeo.com/guides/ |
| Guide: load polygons (WKT) | https://lodgingeo.com/guides/load-hotel-polygons-wkt/ |
| Guide: direct vs OTA prices | https://lodgingeo.com/guides/hotel-direct-vs-ota-prices/ |
| Guide: prospect list | https://lodgingeo.com/guides/build-a-hotel-prospect-list/ |
| Guide: openings and closures | https://lodgingeo.com/guides/track-hotel-openings-and-closures/ |
| FAQ | https://lodgingeo.com/faq/ |
| About | https://lodgingeo.com/about/ |
| Contact | https://lodgingeo.com/contact/ |
| Privacy | https://lodgingeo.com/privacy/ |
| Terms and data licence | https://lodgingeo.com/terms/ |

## What was verified on production (§15)

`php tests/live_check.php`: 111 checks passed, 0 failed.

- Every sitemap URL returns 200 over HTTPS and shows its real H1.
- `https://www.lodgingeo.com/` → 301 `https://lodgingeo.com/`; `/index.php` → 301 `/`; `/data` → 301 `/data/`.
- **Not checkable from the build container:** plain-HTTP requests (its proxy only tunnels HTTPS). HTTP → HTTPS is covered twice: the `.htaccess` rule and Hostinger's own HTTPS redirect (`is_https_redirect_enabled: true`). Please open http://lodgingeo.com once in a browser to confirm.
- `/robots.txt` and `/sitemap.xml` valid; JSON-LD on `/` and `/hotel-database-sample/` parses.
- 403/404 for `/.git/config`, `/inc/site.php`, `/data/columns.json`, `/storage/samples/…xlsx`, `/docs/PLAN.md`, the config file paths, `/vendor/…`, `/tests/…`, `/.htaccess`, `/.hostinger/site.json`, `/data/redirects.csv`.
- Headers: CSP, nosniff, Referrer-Policy, Permissions-Policy, X-Frame-Options, HSTS (`max-age=31536000`). Hostinger adds a second CSP header, `upgrade-insecure-requests`; both apply.
- `/whatsapp/` → 302 to wa.me; no page contains the WhatsApp number.
- **Sample form, Phase A pending path:** a submission from `maher+test-sample@lodgingeo.com` created a lead row and showed the "We're preparing the latest sample…" state (noindex). The owner notification was tagged `[send sample manually]`.
- **Contact forms:** the full form (`maher+test-contact@…`) and the home short form (`maher+test-short@…`) each stored a message and showed the success message.
- **Email:** all six emails (owner notification and visitor email for the sample request; owner notification and sender acknowledgement for each of the two contact forms; all addressed to maher@lodgingeo.com or its plus-addresses) were accepted by the mail transport with no error in the error log. Delivery to the inbox could not be checked from here: **please confirm the test emails arrived at maher@lodgingeo.com** (check spam too, see "Email" below).
- Test rows deleted afterwards (1 lead, 2 messages, 3 rate-limit records) with the maintenance hook.
- IndexNow ping sent for all 26 URLs (HTTP 202 accepted).
- The full download test runs in Phase B, once the workbook is on the server.

## Lighthouse (mobile)

| Page | Performance | Accessibility | Best practices | SEO |
|---|---|---|---|---|
| `/` | 100 | 100 | 100 | 100 |
| `/hotel-database-sample/` | 100 | 100 | 100 | 100 |
| `/data/hotel-prices-by-booking-site/` | 100 | 100 | 100 | 100 |
| `/data-dictionary/` | 100 | 100 | 100 | 100 |
| `/contact/` | 100 | 100 | 100 | 100 |

Run locally against `php -S` with Lighthouse 12 before the first merge.

## Redirects applied (301, `data/redirects.csv`)

Git history only ever served `/`, so these map common legacy paths of a hotel-database site to the nearest page:

`/index.html` `/index.htm` `/home` `/hotel-database` `/hotels-database` `/hotel-list` `/hotels-list` → `/` · `/global-hotel-database` `/countries` → `/coverage/` · `/sample` `/free-sample` `/download` `/downloads` `/hotel-database-sample.html` → `/hotel-database-sample/` · `/hotel-email-list` `/hotel-email-database` `/hotel-contact-database` → `/data/hotel-contact-data/` · `/hotel-prices` `/hotel-rates` → `/data/hotel-prices-by-booking-site/` · `/hotel-polygons` → `/data/hotel-building-polygons/` · `/fields` `/data-fields` `/documentation` → `/data-dictionary/` · `/pricing` `/prices` `/plans` → `/formats-and-delivery/` · `/buy` `/order` `/quote` `/contact-us` `/contact.html` → `/contact/` · `/about-us` `/about.html` → `/about/` · `/faq.html` `/faqs` → `/faq/` · `/privacy-policy` → `/privacy/` · `/terms-and-conditions` `/terms-of-service` → `/terms/` · `/blog` → `/guides/`.

Plus: http → https, www → apex, `/index.php` → `/`, trailing slash on every page URL.

## Decisions I made where the brief was silent or did not match reality

1. **Hostinger site type.** The site had been created as a Node.js app, and Hostinger's API cannot switch a Node.js site to PHP Git auto-deploy (`hosting_deploy` refuses `type: static` on it). I deployed `main` as an archive into `public_html` instead, which replaced the Node proxy `.htaccess` so LiteSpeed serves PHP. I removed the Node.js Git auto-deployment, because every push was starting a failing Node build. **Deploys are by archive until the site type is changed** (see "What I need from you").
2. **Tag push refused.** This session's Git proxy refuses tag pushes, so the pre-rebuild commit is on branch `archive/pre-rebuild-2026-11` rather than tag `pre-rebuild-2026-11`.
3. **Config outside the web root.** Uploads only land inside `public_html`, so the config was uploaded as a denied dotfile and moved one level up by the app on its first request. It now sits at `/home/u742794872/domains/lodgingeo.com/lodgingeo-config.php`.
4. **Email transport.** No Hostinger mail order exists and the MX points to Google, so no Hostinger mailbox could be created. Mail goes out through PHP `mail()` on the hosting server for now (stop point 2 below).
5. **CSRF without sessions**, so pages can be cached for 10 minutes (the Hostinger CDN is on). Details are in `docs/BUILD_LOG.md`.
6. **Owner maintenance hook** for erasure requests and test cleanup (`README.md`, "Maintenance jobs").
7. **In-text links are lightly underlined** (WCAG 1.4.1); navigation and buttons underline on hover only.
8. **Interim wording to check:** the one-line column meanings in Phase A, the meanings of the change types (`field_added`, `reopened` …), and the descriptions of `neighbourhood_momentum_12m` and `stable_recommendations_*`. Phase B replaces the column meanings with the Column guide text.
9. **Coverage page** lists the top 10 of the 32 sample countries until Phase B generates the full list. The release log lists the months without per-month counts until Phase B.
10. **Terms:** I stated that the free sample is covered by the data licence.
11. **PHP 8.5** (newest offered); CI tests on PHP 8.2.

## What I need from you

1. **Email (stop point 2).** Choose one:
   - Google Workspace (your MX is Google): create an app password for maher@lodgingeo.com, then I set `MAIL_TRANSPORT = smtp`, `SMTP_HOST = smtp.gmail.com`, port 465. Also add `include:_spf.google.com` to the SPF record.
   - Or buy or enable Hostinger email for lodgingeo.com and give me the mailbox password. MX would then have to move to Hostinger, so only do this if you are leaving Google.

   Until then, `mail()` messages from the web server may land in spam. Please check the spam folder for the six launch-test emails.
2. **Switch the site to PHP Git auto-deploy.** In hPanel → Websites → lodgingeo.com, change the website from Node.js to a regular PHP site, or tell me to delete and recreate the website entry (the database and config are kept separately). Then I connect Git auto-deploy for `main`.
3. **Search Console.** Add a Domain property for `lodgingeo.com` and send me the TXT value; I will add it through Hostinger DNS. Then submit `https://lodgingeo.com/sitemap.xml` and request indexing for `/`, `/hotel-database-sample/` and `/data/`. Check the "Not found (404)" report after a few weeks and send me any old URLs, so I can add them to `data/redirects.csv`.
4. **Bing Webmaster Tools:** import from Search Console.
5. **Phase B:** attach the final sample workbook and ask me to run §17.
6. **Optional:** create the `pre-rebuild-2026-11` tag on GitHub from branch `archive/pre-rebuild-2026-11`.
7. Review this report and reply with any corrections.
