# Lodgingeo rebuild: plan

Brief: build, test, merge and deploy lodgingeo.com as a plain, documentation-style hotel data site (PHP 8.2+, no build step), then switch on sample-row blocks in Phase B.

## Phase A (now)

1. Discovery: repo history, Hostinger account, DNS/mail, live site → `docs/DISCOVERY.md`.
2. Preserve main (`pre-rebuild-2026-11`), work on `rebuild/site-v1`.
3. Core: `index.php` front controller, `.htaccess`, `inc/` (settings, bootstrap, db, mail, forms, seo, layout, pages).
4. Content: every page in §7 from `inc/routes.php`; copy rules in `docs/CONTENT_GUIDE.md`.
5. Data: hand-seeded `data/*.json` (interim) from Appendices A, B, D, E; `tools/build_data.py` regenerates them in Phase B.
6. Forms: sample request (lead + token + download), contact form, anti-spam, PRG, emails (PHPMailer).
7. SEO: titles/meta/canonical/OG, JSON-LD, sitemap, robots, llms.txt, IndexNow, redirects.
8. Tests: `tests/smoke_test.php` (php -S + SQLite + log mail), CI workflow, Lighthouse.
9. Deploy: PR → CI → squash-merge → Hostinger (switch site from Node.js to PHP via Git deploy), DB, server config, cache purge.
10. Verify live (§15), write `docs/LAUNCH_REPORT.md`.

## Phase B (when the final workbook arrives)

Run §17: save workbook under the neutral name, `python tools/build_data.py`, diff against Appendix A, enable sample-row blocks, redeploy, run the full download test.
