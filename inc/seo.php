<?php
/** Head tags and JSON-LD builders (brief §11). */
declare(strict_types=1);

function jsonld_script(array $data): string
{
    return '<script type="application/ld+json">' . json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) . '</script>';
}

function org_id(): string
{
    return CANONICAL_URL . '/#organization';
}

function schema_organization(): array
{
    return [
        '@context' => 'https://schema.org',
        '@type'    => 'Organization',
        '@id'      => org_id(),
        'name'     => SITE_NAME,
        'legalName' => LEGAL_ENTITY,
        'url'      => CANONICAL_URL . '/',
        'email'    => PUBLIC_EMAIL,
        'logo'     => CANONICAL_URL . '/apple-touch-icon.png',
        'sameAs'   => [PARENT_ORG_URL],
        'parentOrganization' => ['@type' => 'Organization', 'name' => PARENT_ORG_NAME, 'url' => PARENT_ORG_URL],
    ];
}

function schema_website(): array
{
    return [
        '@context' => 'https://schema.org',
        '@type'    => 'WebSite',
        '@id'      => CANONICAL_URL . '/#website',
        'name'     => SITE_NAME,
        'url'      => CANONICAL_URL . '/',
        'description' => 'Global hotels database: ' . PLACES_TO_STAY_TOTAL . ' places to stay, including ' . HOTELS_TOTAL . ' hotels.',
        'publisher' => ['@id' => org_id()],
        'inLanguage' => 'en',
    ];
}

function schema_breadcrumbs(string $path): array
{
    $items = [];
    $i = 1;
    foreach (breadcrumb_trail($path) as $p => $label) {
        $items[] = ['@type' => 'ListItem', 'position' => $i++, 'name' => $label, 'item' => abs_url($p)];
    }
    return ['@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => $items];
}

function schema_faq(array $faqs): array
{
    return [
        '@context' => 'https://schema.org',
        '@type'    => 'FAQPage',
        'mainEntity' => array_map(fn($f) => [
            '@type' => 'Question',
            'name'  => $f['q'],
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => strip_tags($f['a'])],
        ], $faqs),
    ];
}

function schema_article(string $path, array $route): array
{
    return [
        '@context' => 'https://schema.org',
        '@type'    => 'Article',
        'headline' => $route['h1'],
        'description' => $route['desc'],
        'mainEntityOfPage' => abs_url($path),
        'url'      => abs_url($path),
        'datePublished' => GUIDES_PUBLISHED,
        'dateModified'  => GUIDES_UPDATED,
        'author'   => ['@type' => 'Person', 'name' => AUTHOR_NAME, 'worksFor' => ['@id' => org_id()]],
        'publisher' => ['@id' => org_id(), '@type' => 'Organization', 'name' => SITE_NAME, 'logo' => ['@type' => 'ImageObject', 'url' => CANONICAL_URL . '/apple-touch-icon.png']],
        'image'    => CANONICAL_URL . '/og-image.png',
        'inLanguage' => 'en',
    ];
}

/** Dataset markup for Google Dataset Search. */
function schema_dataset(bool $coverageOnly = false): array
{
    $vars = [];
    foreach (['latitude', 'longitude', 'name', 'formatted_address', 'country_code', 'phone', 'website', 'hotel_class', 'rating',
              'review_count', 'amenities', 'hotel_rates_by_site', 'lowest_offer_latest', 'direct_vs_lowest_ota_pct',
              'building_polygon_wkt', 'building_area_m2', 'building_match_grade', 'first_seen_month', 'closed_month'] as $c) {
        $vars[] = ['@type' => 'PropertyValue', 'name' => $c];
    }
    $d = [
        '@context' => 'https://schema.org',
        '@type'    => 'Dataset',
        '@id'      => CANONICAL_URL . '/#dataset',
        'name'     => 'Lodgingeo Global Hotels Database',
        'description' => 'Global database of ' . PLACES_TO_STAY_TOTAL . ' places to stay, including ' . HOTELS_TOTAL . ' hotels, in '
            . COUNTRIES_TOTAL . ': coordinates and addresses, publicly listed contact details, nightly rates across booking sites, '
            . 'building polygons, neighbourhood context and monthly change history since January 2025. Delivered as CSV, Excel or Google Sheets.',
        'url'      => CANONICAL_URL . '/',
        'creator'  => ['@type' => 'Organization', '@id' => org_id(), 'name' => SITE_NAME, 'url' => CANONICAL_URL . '/'],
        'publisher' => ['@type' => 'Organization', '@id' => org_id(), 'name' => SITE_NAME],
        'license'  => CANONICAL_URL . '/terms/#data-licence',
        'isAccessibleForFree' => false,
        'keywords' => ['hotel database', 'hotels data', 'hotel list', 'hotel contact data', 'hotel rates by booking site',
                       'hotel building polygons', 'places to stay', 'points of interest'],
        'spatialCoverage' => ['@type' => 'Place', 'name' => 'Worldwide'],
        'temporalCoverage' => HISTORY_START . '/' . LATEST_RELEASE,
        'dateModified' => LATEST_RELEASE,
        'variableMeasured' => $vars,
        'distribution' => [
            ['@type' => 'DataDownload', 'encodingFormat' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
             'contentUrl' => CANONICAL_URL . '/hotel-database-sample/', 'name' => 'Free sample (.xlsx)'],
            ['@type' => 'DataDownload', 'encodingFormat' => 'text/csv', 'contentUrl' => CANONICAL_URL . '/hotel-database-sample/', 'name' => 'Full data (CSV)'],
        ],
    ];
    if ($coverageOnly) {
        unset($d['variableMeasured']);
    }
    return $d;
}

/** All head tags for a page. */
function render_head(string $path, array $page): string
{
    $title = $page['title'];
    $desc  = $page['desc'];
    $canon = abs_url($page['canonical'] ?? $path);
    $og    = abs_url('/og-image.png');
    $h  = '<meta charset="utf-8">' . "\n";
    $h .= '<meta name="viewport" content="width=device-width, initial-scale=1">' . "\n";
    $h .= '<title>' . e($title) . '</title>' . "\n";
    $h .= '<meta name="description" content="' . e($desc) . '">' . "\n";
    if (!empty($page['noindex'])) {
        $h .= '<meta name="robots" content="noindex, follow">' . "\n";
    }
    if (empty($page['noindex'])) {
        $h .= '<link rel="canonical" href="' . e($canon) . '">' . "\n";
    }
    $h .= '<meta property="og:title" content="' . e($title) . '">' . "\n";
    $h .= '<meta property="og:description" content="' . e($desc) . '">' . "\n";
    $h .= '<meta property="og:url" content="' . e($canon) . '">' . "\n";
    $h .= '<meta property="og:type" content="' . e(!empty($page['guide']) ? 'article' : 'website') . '">' . "\n";
    $h .= '<meta property="og:site_name" content="' . SITE_NAME . '">' . "\n";
    $h .= '<meta property="og:image" content="' . e($og) . '">' . "\n";
    $h .= '<meta property="og:image:width" content="1200"><meta property="og:image:height" content="630">' . "\n";
    $h .= '<meta name="twitter:card" content="summary_large_image">' . "\n";
    $h .= '<meta name="twitter:title" content="' . e($title) . '">' . "\n";
    $h .= '<meta name="twitter:description" content="' . e($desc) . '">' . "\n";
    $h .= '<meta name="twitter:image" content="' . e($og) . '">' . "\n";
    $h .= '<meta name="theme-color" content="#ffffff">' . "\n";
    $h .= '<link rel="icon" href="/favicon.svg" type="image/svg+xml">' . "\n";
    $h .= '<link rel="apple-touch-icon" href="/apple-touch-icon.png">' . "\n";
    if (GSC_VERIFICATION !== '') {
        $h .= '<meta name="google-site-verification" content="' . e(GSC_VERIFICATION) . '">' . "\n";
    }
    if (BING_VERIFICATION !== '') {
        $h .= '<meta name="msvalidate.01" content="' . e(BING_VERIFICATION) . '">' . "\n";
    }
    $h .= '<link rel="stylesheet" href="' . e(asset('assets/site.css')) . '">' . "\n";
    $h .= '<script src="' . e(asset('assets/site.js')) . '" defer></script>' . "\n";
    if (turnstile_enabled() && !empty($page['has_form'])) {
        $h .= '<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>' . "\n";
    }

    // Structured data.
    $blocks = [];
    if ($path === '/') {
        $blocks[] = schema_organization();
        $blocks[] = schema_website();
    } else {
        $blocks[] = schema_breadcrumbs($path);
    }
    foreach ($page['jsonld'] ?? [] as $type) {
        $blocks[] = match ($type) {
            'dataset'          => schema_dataset(),
            'dataset_coverage' => schema_dataset(true),
            'faq_home'         => schema_faq(faq_items(true)),
            'faq_all'          => schema_faq(faq_items(false)),
        };
    }
    if (!empty($page['guide'])) {
        $blocks[] = schema_article($path, $page);
    }
    foreach ($blocks as $b) {
        $h .= jsonld_script($b) . "\n";
    }
    return $h;
}

function faq_items(bool $homeOnly): array
{
    $all = data_json('faq')['items'] ?? [];
    return $homeOnly ? array_values(array_filter($all, fn($f) => !empty($f['home']))) : $all;
}
