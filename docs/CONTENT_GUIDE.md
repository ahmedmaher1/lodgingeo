# Content guide

Copy rules for lodgingeo.com (brief §7). Apply them to every page, guide, email and FAQ answer.

## Voice

- Plain, factual and specific, like documentation from a data provider.
- Short paragraphs; prefer tables to prose.
- No hype words: revolutionary, cutting-edge, unlock, leverage, seamless, world-class, game-changing.
- Use "hotel database", "hotels database", "hotel data" and "hotel list" naturally. Never stuff keywords.
- One person writes to another: emails are signed "Ahmed Maher, Lodgingeo" and contain no marketing template.

## Every page has

- A unique `<title>` of 60 characters or fewer and a unique meta description of 140–160 characters (`inc/routes.php`; the smoke test enforces both).
- Exactly one H1, breadcrumbs (except the home page), a canonical URL, OG/Twitter tags.
- A closing CTA block (Download sample + Contact) and at least three internal links in the main content.

## Numbers

- Every number comes from `inc/site.php` (settings) or from `data/*.json` (generated from the sample). Never type a figure into a template.
- If a number is missing, leave the sentence out instead of guessing. Blocks that need sample rows render only when their JSON file exists.
- Never show a price, price range, plan or "from" figure for the data. Quotes go by email only.
- Never mention an API, endpoints or query parameters (the smoke test fails on "API" or "endpoint").

## Required caveats

- Wherever rates appear: "Rates are indicative nightly offers captured for the search dates at the time of capture, not contracted rates." (`RATES_CAVEAT`)
- Once on the prices page and once in the terms: "Lodgingeo is not affiliated with the booking sites listed." (`TRADEMARK_LINE`)
- Never claim a field is in the sample unless it is in the sample's header row (`data/columns.json`). Other fields are labelled "full dataset" (`sample_badge()`).

## Privacy on public pages

- Never display `product_id`, the client-input columns (`hotel_name`, `city`, `country` from the input) or the match candidates. Use our own fields (`name`, `ld_city`, `country_name`).
- Never print the WhatsApp number anywhere: link to `/whatsapp/`, which redirects to wa.me with the number from the server config.

## Attribution

- Footer: "Contains information from OpenStreetMap contributors (ODbL) and Overture Maps Foundation", linking to `/methodology/#attribution`.
- Do not name any specific map provider as a source.
