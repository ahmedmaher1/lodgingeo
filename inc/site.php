<?php
/**
 * Public site settings: the single source of truth for every template (brief §2).
 * Secrets never go here; they live in the server-only lodgingeo-config.php (§12).
 */
declare(strict_types=1);

const SITE_NAME            = 'Lodgingeo';
const CANONICAL_URL        = 'https://lodgingeo.com';
const CANONICAL_HOST       = 'lodgingeo.com';
const TAGLINE              = 'Global hotels database';
const PUBLIC_EMAIL         = 'maher@lodgingeo.com';
const SIGNATURE_NAME       = 'Ahmed Maher, Lodgingeo';
const AUTHOR_NAME          = 'Ahmed Maher';
const LEGAL_ENTITY         = 'Lodgingeo, LLC';
const PARENT_ORG_NAME      = 'Location Drive';
const PARENT_ORG_URL       = 'https://locationdrive.com';

const PLACES_TO_STAY_TOTAL = '5.8 million';
const HOTELS_TOTAL         = '2.6 million';
const COUNTRIES_TOTAL      = '250 countries and territories';
const HISTORY_START        = '2025-01';
const LATEST_RELEASE       = '2026-10';
const UPDATE_FREQUENCY     = 'Monthly';
const FORMATS              = 'CSV, Excel (.xlsx) or a shared Google Sheet. Building polygons come as a WKT text column';
const FORMATS_SHORT        = 'CSV, Excel (.xlsx), Google Sheets';
const DELIVERY             = 'A download link for the CSV or Excel files, or a Google Sheet shared with the buyer';
const REPLY_PROMISE        = 'We reply within 24 hours.';

// Sample file: neutral name, stored outside the web-reachable paths (storage/ is denied).
const SAMPLE_FILENAME      = 'lodgingeo-hotel-data-sample-2026-10.xlsx';
const SAMPLE_RELEASE       = '2026-10';

// Optional search-engine verification meta tags. Leave empty until provided.
const GSC_VERIFICATION     = '';
const BING_VERIFICATION    = '';

// IndexNow: the key is public by design (served at /{key}.txt).
const INDEXNOW_KEY         = '17e061ef5e97f83a0b1dcb4ede841b25';

// Content dates.
const GUIDES_PUBLISHED     = '2026-11-15';
const GUIDES_UPDATED       = '2026-11-15';

// Rates caveat, required wherever rates appear (§7 copy rules).
const RATES_CAVEAT         = 'Rates are indicative nightly offers captured for the search dates at the time of capture, not contracted rates.';
const TRADEMARK_LINE       = 'Booking-site names are trademarks of their owners. Lodgingeo is not affiliated with the booking sites listed.';
