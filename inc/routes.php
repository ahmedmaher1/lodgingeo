<?php
/**
 * Page registry. Every indexable page is listed here; the router, sitemap,
 * breadcrumbs and smoke test all read it. URLs never change once live.
 *
 * keys: tpl, title (<=60), desc (140-160), h1, crumb, parent, data (lastmod = release),
 *       sample_form (page carries a sample form), jsonld (extra schema types)
 */
declare(strict_types=1);

function routes(): array
{
    static $r = null;
    if ($r !== null) {
        return $r;
    }
    $r = [
        '/' => [
            'tpl' => 'home', 'crumb' => 'Home', 'parent' => null, 'data' => true, 'sample_form' => true,
            'title' => 'Global Hotels Database — Lodgingeo',
            'desc'  => 'Global hotels database: 5.8 million places to stay in 250 countries, with contacts, rates by booking site, building polygons and monthly history.',
            'h1'    => 'Global Hotels Database',
            'jsonld' => ['dataset', 'faq_home'],
        ],
        '/hotel-database-sample/' => [
            'tpl' => 'sample', 'crumb' => 'Free sample', 'parent' => '/', 'data' => true, 'sample_form' => true,
            'title' => 'Free Hotel Data Sample (Excel) — Lodgingeo',
            'desc'  => 'Download a free hotel database sample in Excel: 250 hotels in 32 countries with contacts, rates by booking site, building polygons and change history.',
            'h1'    => 'Free Hotel Data Sample (Excel)',
            'jsonld' => ['dataset'],
        ],
        '/data/' => [
            'tpl' => 'data', 'crumb' => 'Data', 'parent' => '/', 'data' => true, 'sample_form' => true,
            'title' => 'Hotel Data: Fields and Field Groups — Lodgingeo',
            'desc'  => 'What the Lodgingeo hotel data contains: location, contact details, rates by booking site, building polygons, amenities, neighbourhood and monthly changes.',
            'h1'    => 'Hotel Data',
        ],
        '/data/hotel-contact-data/' => [
            'tpl' => 'data-contacts', 'crumb' => 'Contact details', 'parent' => '/data/', 'data' => true, 'sample_form' => true,
            'title' => 'Hotel Contact Database: Phone, Email, Website — Lodgingeo',
            'desc'  => 'Hotel contact database with phone numbers, websites, emails, WhatsApp and social profiles as published by each property, for B2B prospecting by country.',
            'h1'    => 'Hotel Contact Details: Phone, Email, Website, WhatsApp',
        ],
        '/data/hotel-prices-by-booking-site/' => [
            'tpl' => 'data-prices', 'crumb' => 'Prices by booking site', 'parent' => '/data/', 'data' => true, 'sample_form' => true,
            'title' => 'Hotel Prices by Booking Site (OTA Rate Data) — Lodgingeo',
            'desc'  => 'Hotel rate data across booking sites: nightly offers per OTA and metasearch site, lowest and direct offers, rate parity gaps and monthly rate history.',
            'h1'    => 'Hotel Prices Across Booking Sites',
        ],
        '/data/hotel-building-polygons/' => [
            'tpl' => 'data-polygons', 'crumb' => 'Building polygons', 'parent' => '/data/', 'data' => true, 'sample_form' => true,
            'title' => 'Hotel Building Polygons (WKT Footprints) — Lodgingeo',
            'desc'  => 'Hotel building footprints as WKT polygons in WGS84, matched from OpenStreetMap and Overture with a grade A–E, area in m² and loading code for GIS tools.',
            'h1'    => 'Hotel Building Polygons (Footprints)',
        ],
        '/data/hotel-location-data/' => [
            'tpl' => 'data-location', 'crumb' => 'Location & neighbourhood', 'parent' => '/data/', 'data' => true, 'sample_form' => true,
            'title' => 'Hotel Coordinates and Address Database — Lodgingeo',
            'desc'  => 'Hotel latitude and longitude, full address, timezone and neighbourhood context within 3 km: nearest services, nearby places, lodging and trending spots.',
            'h1'    => 'Hotel Location and Neighbourhood Data',
        ],
        '/data/hotel-details-and-amenities/' => [
            'tpl' => 'data-details', 'crumb' => 'Details & amenities', 'parent' => '/data/', 'data' => true, 'sample_form' => true,
            'title' => 'Hotel Amenities and Star Rating Data — Lodgingeo',
            'desc'  => 'Hotel class, guest rating, review count, rating distribution, check-in and check-out times, amenities, attributes and languages for each hotel in the data.',
            'h1'    => 'Hotel Details and Amenities',
        ],
        '/data/hotel-history-and-changes/' => [
            'tpl' => 'data-history', 'crumb' => 'History & changes', 'parent' => '/data/', 'data' => true, 'sample_form' => true,
            'title' => 'Hotel Openings, Closures and Change History — Lodgingeo',
            'desc'  => 'Monthly hotel change history since January 2025: first seen and closed months, name and amenity changes, rating trends and openings in the neighbourhood.',
            'h1'    => 'Hotel History and Changes',
        ],
        '/hotel-matching/' => [
            'tpl' => 'matching', 'crumb' => 'List matching', 'parent' => '/data/', 'data' => true, 'sample_form' => true,
            'title' => 'Hotel List Matching and Mapping Service — Lodgingeo',
            'desc'  => 'Send your hotel list with your IDs, names and cities. We return our ID, coordinates and every fact, with name and city scores and a match tier for review.',
            'h1'    => 'Hotel List Matching',
        ],
        '/data-dictionary/' => [
            'tpl' => 'dictionary', 'crumb' => 'Data dictionary', 'parent' => '/', 'data' => true,
            'title' => 'Hotel Data Dictionary: Fields and Schema — Lodgingeo',
            'desc'  => 'Every field in the Lodgingeo hotel data: column name, field group, meaning and whether it is in the free sample, plus the full dataset field reference.',
            'h1'    => 'Hotel Data Dictionary',
            'jsonld' => ['dataset'],
        ],
        '/coverage/' => [
            'tpl' => 'coverage', 'crumb' => 'Coverage', 'parent' => '/', 'data' => true,
            'title' => 'Worldwide Hotel Database Coverage — Lodgingeo',
            'desc'  => 'Coverage of the Lodgingeo global hotel database: 5.8 million places to stay and 2.6 million hotels in 250 countries and territories, updated monthly.',
            'h1'    => 'Worldwide Coverage',
            'jsonld' => ['dataset_coverage'],
        ],
        '/use-cases/' => [
            'tpl' => 'use-cases', 'crumb' => 'Use cases', 'parent' => '/',
            'title' => 'Hotel Data Use Cases — Lodgingeo',
            'desc'  => 'How teams use hotel data: supplier prospecting, content mapping for OTAs, rate intelligence, real estate research, GIS geofencing and AI travel assistants.',
            'h1'    => 'Use Cases',
        ],
        '/formats-and-delivery/' => [
            'tpl' => 'formats', 'crumb' => 'Formats & delivery', 'parent' => '/',
            'title' => 'Hotel Data Formats and Delivery — Lodgingeo',
            'desc'  => 'Hotel data delivered as CSV, Excel or a shared Google Sheet, per country or worldwide, with monthly updates and change files. What to send us for a quote.',
            'h1'    => 'Formats and Delivery',
        ],
        '/methodology/' => [
            'tpl' => 'methodology', 'crumb' => 'Methodology', 'parent' => '/',
            'title' => 'Hotel Data Methodology and Sources — Lodgingeo',
            'desc'  => 'How the Lodgingeo hotel data is built: public listings and booking sites, building footprints from OpenStreetMap and Overture, match tiers and quality rules.',
            'h1'    => 'Methodology',
        ],
        '/releases/' => [
            'tpl' => 'releases', 'crumb' => 'Releases', 'parent' => '/', 'data' => true,
            'title' => 'Monthly Hotel Data Releases — Lodgingeo',
            'desc'  => 'Release log of the Lodgingeo hotel database: monthly releases since January 2025, newest first, with the latest release and how each month is compared.',
            'h1'    => 'Release Log',
        ],
        '/guides/' => [
            'tpl' => 'guides', 'crumb' => 'Guides', 'parent' => '/',
            'title' => 'Hotel Data Guides — Lodgingeo',
            'desc'  => 'Practical guides to the Lodgingeo hotel data: loading WKT polygons, reading direct vs OTA prices, building prospect lists and tracking openings and closures.',
            'h1'    => 'Guides',
        ],
        '/guides/load-hotel-polygons-wkt/' => [
            'tpl' => 'guide-polygons', 'crumb' => 'Load hotel polygons (WKT)', 'parent' => '/guides/', 'guide' => true,
            'title' => 'Load Hotel Polygons (WKT) in GeoPandas, PostGIS, QGIS',
            'desc'  => 'Step-by-step guide to loading the hotel building polygons from WKT into GeoPandas, PostGIS and QGIS, checking match grades and converting them to GeoJSON.',
            'h1'    => 'Load Hotel Building Polygons (WKT) in GeoPandas, PostGIS and QGIS',
        ],
        '/guides/hotel-direct-vs-ota-prices/' => [
            'tpl' => 'guide-direct-ota', 'crumb' => 'Direct vs OTA prices', 'parent' => '/guides/', 'guide' => true,
            'title' => 'Hotel Direct vs OTA Prices: How to Read the Data',
            'desc'  => 'How to read direct_vs_lowest_ota_pct: what the gap between a hotel website price and the lowest OTA offer means, what the sample shows and its caveats.',
            'h1'    => 'Hotel Direct vs OTA Prices: How to Read the Data',
        ],
        '/guides/build-a-hotel-prospect-list/' => [
            'tpl' => 'guide-prospects', 'crumb' => 'Build a prospect list', 'parent' => '/guides/', 'guide' => true,
            'title' => 'How to Build a Hotel Prospect List in Excel',
            'desc'  => 'Build a hotel prospect list in Excel: filter the hotel data by country, class, rating, chain and available contacts, and choose the columns that matter.',
            'h1'    => 'How to Build a Hotel Prospect List in Excel',
        ],
        '/guides/track-hotel-openings-and-closures/' => [
            'tpl' => 'guide-openings', 'crumb' => 'Track openings and closures', 'parent' => '/guides/', 'guide' => true,
            'title' => 'Track Hotel Openings and Closures Month by Month',
            'desc'  => 'Use the lifecycle columns, the monthly change log and neighbourhood momentum in the hotel data to track hotel openings, closures and reopenings over time.',
            'h1'    => 'Track Hotel Openings and Closures Month by Month',
        ],
        '/faq/' => [
            'tpl' => 'faq', 'crumb' => 'FAQ', 'parent' => '/',
            'title' => 'Hotel Database FAQ — Lodgingeo',
            'desc'  => 'Answers about the Lodgingeo hotel database: counts and countries, update frequency, formats and delivery, sources, contacts, polygons, licensing and GDPR.',
            'h1'    => 'Frequently Asked Questions',
            'jsonld' => ['faq_all'],
        ],
        '/about/' => [
            'tpl' => 'about', 'crumb' => 'About', 'parent' => '/',
            'title' => 'About Lodgingeo — Hotel Data by Location Drive',
            'desc'  => 'Lodgingeo is the hotel data service of Location Drive, which has compiled global place data since 2010. Run by Ahmed Maher, who answers each message personally.',
            'h1'    => 'About Lodgingeo',
        ],
        '/contact/' => [
            'tpl' => 'contact', 'crumb' => 'Contact', 'parent' => '/',
            'title' => 'Contact Lodgingeo — Hotel Data Quotes',
            'desc'  => 'Ask for counts and a quote for hotel data by country or worldwide. Tell us the fields, format and frequency you need. We reply within 24 hours by email.',
            'h1'    => 'Contact Us',
        ],
        '/privacy/' => [
            'tpl' => 'privacy', 'crumb' => 'Privacy', 'parent' => '/',
            'title' => 'Privacy Policy — Lodgingeo',
            'desc'  => 'What the Lodgingeo website forms collect, why we collect it, how long we keep it (24 months) and how to ask us to delete your details at any time.',
            'h1'    => 'Privacy Policy',
        ],
        '/terms/' => [
            'tpl' => 'terms', 'crumb' => 'Terms', 'parent' => '/',
            'title' => 'Terms of Use and Data Licence — Lodgingeo',
            'desc'  => 'Website terms of use and the Lodgingeo data licence: who may use purchased hotel data, what is allowed, term, third-party sources and order terms.',
            'h1'    => 'Terms of Use and Data Licence',
        ],
    ];
    return $r;
}

/** Breadcrumb trail [path => label] from home to $path. */
function breadcrumb_trail(string $path): array
{
    $routes = routes();
    $trail = [];
    $p = $path;
    while ($p !== null && isset($routes[$p])) {
        $trail = [$p => $routes[$p]['crumb']] + $trail;
        $p = $routes[$p]['parent'];
    }
    return $trail;
}
