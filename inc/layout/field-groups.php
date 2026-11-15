<?php
/** The 8 field groups (Appendix B) with example columns and their pages. */
$groups = [
    ['Location & address', 'Coordinates in WGS84, full address, neighbourhood and time zone.', ['latitude', 'longitude', 'formatted_address', 'timezone'], '/data/hotel-location-data/', 'Location data'],
    ['Contacts & identity', 'Phone, website and domain, chain and brand, plus email, WhatsApp and social profiles where publicly listed.', ['phone', 'website', 'domain', 'brand'], '/data/hotel-contact-data/', 'Contact details'],
    ['Hotel details & amenities', 'Star class, guest rating and reviews, check-in times, amenities and languages.', ['hotel_class', 'rating', 'review_count', 'amenities'], '/data/hotel-details-and-amenities/', 'Details and amenities'],
    ['Prices & booking sites', 'Nightly offers per booking site, lowest and direct offers, and monthly rate history.', ['hotel_rates_by_site', 'lowest_offer_latest', 'direct_vs_lowest_ota_pct', 'rate_history_by_month'], '/data/hotel-prices-by-booking-site/', 'Prices by booking site'],
    ['Building polygon', 'The hotel\'s building footprint as WKT, with area, source and match grade.', ['building_polygon_wkt', 'building_area_m2', 'building_match_grade', 'polygon_source'], '/data/hotel-building-polygons/', 'Building polygons'],
    ['Neighbourhood (within 3 km)', 'Nearest services, counts of places by group and radius, nearby lodging and trends.', ['nearest_<service>', 'count_<group>_3km', 'places_within_1km', 'nearby_lodging_*'], '/data/hotel-location-data/#neighbourhood', 'Neighbourhood data'],
    ['History & changes', 'First and last seen months, closures, name and amenity changes, rating trends.', ['first_seen_month', 'closed_month', 'name_history', 'rating_trend_12m'], '/data/hotel-history-and-changes/', 'History and changes'],
    ['Match & IDs', 'Our stable place ID and, when you send a list, match scores and a match tier per row.', ['ld_id', 'name_score', 'confidence', 'match_tier'], '/hotel-matching/', 'List matching'],
];
?>
<div class="table-wrap">
<table>
  <caption>The eight field groups in the hotel data</caption>
  <thead><tr><th scope="col">Field group</th><th scope="col">What it covers</th><th scope="col">Example columns</th><th scope="col">Details</th></tr></thead>
  <tbody>
<?php foreach ($groups as [$g, $desc, $cols, $href, $label]): ?>
    <tr>
      <th scope="row"><?= e($g) ?></th>
      <td><?= e($desc) ?></td>
      <td><?= implode(' ', array_map(fn($c) => '<code>' . e($c) . '</code>', $cols)) ?></td>
      <td><a href="<?= e($href) ?>"><?= e($label) ?></a></td>
    </tr>
<?php endforeach; ?>
  </tbody>
</table>
</div>
