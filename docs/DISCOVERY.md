# Discovery (2026-11-15)

## Repository

- History: 3 commits. `main` (10b459c) holds only a Node.js placeholder (`package.json`, `server.js`, `.gitignore`, `README.md`) added on 2026-11-13.
- No earlier application (no store, Supabase, Stripe or S3), so no `legacy/store-2026` branch was needed.
- Tag `pre-rebuild-2026-11` was created locally on 10b459c, but this session's Git proxy refuses tag pushes (HTTP 403). The same commit is preserved on branch `archive/pre-rebuild-2026-11`. **Action for the owner:** create the tag on GitHub from that branch if you want the tag itself.
- URL paths ever served from Git: only `/` (the placeholder answered every path with the same page). No HTML files, routes or sitemaps exist in history. The site Google knew "for years" predates this repository, so its old URLs are not in Git; `data/redirects.csv` covers common legacy patterns for a hotel-database site (see that file), each mapped to the nearest topical page.

## Hostinger

| Item | Finding | Decision |
|---|---|---|
| Plan | `hostinger_business` (order 68992684), active | Business plan supports PHP, MySQL, Git deploy |
| Website | `lodgingeo.com`, username `u742794872`, addon vhost, root `/home/u742794872/domains/lodgingeo.com/public_html` | Deploy target |
| Website type | `nodejs` (created 2026-11-13 from the placeholder `package.json`) | Re-deploy as PHP (`hosting_deploy` type `static`, which covers PHP) and remove `package.json` |
| PHP | 8.3.36 active; supported up to 8.5 | Switch to 8.5 (newest offered) |
| Git | GitHub installation `01a124ee-…` active; auto-deploy enabled for `ahmedmaher1/lodgingeo` `main`, directory "" | Keep auto-deploy on `main` |
| Files | `public_html` contains only `.htaccess` (463 B, Node.js proxy rules) | Replaced by the site's `.htaccess` |
| Uploads | `hosting_files_generate-upload-url` writes only inside `public_html` | Config is uploaded inside `public_html` under a denied name and moved one level up by `bootstrap.php` on first request |
| Databases | `u742794872_localizelarge` (localizelarge.com), `u742794872_hiaLb` (unassigned) | Not ours; untouched. Create `lodgingeo` |

## DNS and email

| Record | Value |
|---|---|
| A @ | 46.202.183.201 |
| AAAA @ | 2a02:4780:2b:1949:0:2c46:2678:11 |
| CNAME www | lodgingeo.com |
| **MX @** | **1 smtp.google.com** (Google Workspace receives mail) |
| TXT @ SPF | `v=spf1 include:_spf.mail.hostinger.com ~all` |
| DKIM | CNAMEs `hostingermail-a/b/c._domainkey` → Hostinger |
| TXT _dmarc | `v=DMARC1; p=none` (exists, so not changed) |

- Hostinger mail orders: **none**. A Hostinger mailbox for `maher@lodgingeo.com` cannot be created through the API (no mail order), and MX points to Google, so `maher@lodgingeo.com` is almost certainly a Google Workspace mailbox.
- Decision: MX is not touched. SMTP credentials are a stop point (§0, case 2). Until they exist, the server config uses `MAIL_TRANSPORT = mail` (PHP `mail()` on Hostinger, which is enabled on this plan); switching to SMTP is a config-only change.
- Note: the SPF record does not include Google (`include:_spf.google.com`). If you send mail from Gmail as @lodgingeo.com, add it.

## Live site (before rebuild)

- `https://lodgingeo.com/` and `https://www.lodgingeo.com/`: 200, LiteSpeed, placeholder page. `www` is not redirected to the apex.
- `/robots.txt`, `/sitemap.xml`, `/index.php`: 200 with the placeholder HTML (the Node app answers every path).
- Plain-HTTP checks cannot run from this build container (its proxy only tunnels HTTPS); HTTP → HTTPS is verified via the `.htaccess` rules and headers after deploy.
