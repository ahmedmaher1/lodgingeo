#!/usr/bin/env python3
"""Write the Phase A (interim) data/*.json files from the brief's appendices.

Every figure below is copied from Appendix A (current sample facts), Appendix B
(column groups), Appendix C (booking sites) and Appendix E (four real polygons).
Files are marked "interim": true; tools/build_data.py overwrites them in Phase B.
sample_preview.json and rates_example.json are deliberately NOT written: the
blocks that need sample rows stay hidden until the workbook arrives.
"""
from __future__ import annotations

import json
import sys
from pathlib import Path

sys.path.insert(0, str(Path(__file__).resolve().parent))
from build_data import GROUPS, PRIVATE, polygon_svg_path  # noqa: E402

DATA = Path(__file__).resolve().parent.parent / "data"

# Interim one-line meanings (Phase B replaces them with the Column guide text).
MEANING = {
    "ld_id": "Lodgingeo place ID; stable across monthly releases.",
    "ld_name": "Name of the matched Lodgingeo place.",
    "ld_name_alt": "Alternative name of the matched place.",
    "ld_city": "City of the matched place.",
    "ld_address": "Address of the matched place.",
    "ld_category": "Category of the matched place.",
    "country_code": "ISO 3166-1 alpha-2 country code.",
    "name_score": "Name similarity between your record and the match, 0–100.",
    "city_score": "City similarity between your record and the match, 0–100.",
    "confidence": "Match confidence: 70% name score, 30% city score, plus a small bonus for places typed as hotels.",
    "match_tier": "high, medium, low or no_match, derived from confidence.",
    "flags": "Notes on the match, such as a close second candidate.",
    "hotel_ld_id": "Lodgingeo ID of the hotel used for all fact columns.",
    "latitude": "Latitude, WGS84 decimal degrees.",
    "longitude": "Longitude, WGS84 decimal degrees.",
    "name": "Property name.",
    "name_alt": "Alternative or local-language name.",
    "parent_name": "Parent place or property group, where listed.",
    "street_address": "Street and number.",
    "neighborhood": "Neighbourhood or district.",
    "state": "State, province or region.",
    "postal_code": "Postal code.",
    "country_name": "Country name.",
    "formatted_address": "Full address on one line.",
    "timezone": "IANA time zone, e.g. Europe/Madrid.",
    "phone": "Phone number in international format.",
    "website": "Website URL as published by the property.",
    "domain": "Website domain without protocol or path.",
    "chain_from_website": "Chain or group detected from the website.",
    "brand": "Hotel brand, where known.",
    "wikipedia_url": "Wikipedia article, where one exists.",
    "category": "Primary category of the place.",
    "categories": "All categories of the place.",
    "status": "Operating status in the latest release.",
    "hotel_class": "Star class (1–5), where listed.",
    "rating": "Average guest rating.",
    "review_count": "Number of guest reviews.",
    "rating_distribution": "Count of reviews per star level.",
    "price_level": "Relative price level, where listed.",
    "check_in_time": "Check-in time.",
    "check_out_time": "Check-out time.",
    "amenities": "Amenities listed as available.",
    "amenities_unavailable": "Amenities listed as not available.",
    "attributes": "Other listed attributes.",
    "languages": "Languages spoken, where listed.",
    "floor_level": "Floor level of the place inside its building, where known.",
    "area_type": "Type of area around the place, where known.",
    "hotel_rate_latest": "Headline nightly rate in the latest release.",
    "hotel_rates_by_site": "Nightly offer per booking site in the latest release.",
    "booking_sites_latest": "Booking sites offering the hotel in the latest release.",
    "booking_otas_latest": "Online travel agencies among those sites.",
    "booking_metasearch_latest": "Metasearch sites among those sites.",
    "booking_other_sites_latest": "Other booking sites among those sites.",
    "lowest_offer_latest": "Lowest nightly offer across sites in the latest release.",
    "direct_offer_latest": "Offer on the hotel's own website, where shown.",
    "direct_vs_lowest_ota_pct": "Direct offer compared with the lowest OTA offer, in percent (positive: direct is higher).",
    "booking_sites_months_seen": "Months in which each booking site listed the hotel.",
    "rate_history_by_month": "Headline nightly rate per monthly release.",
    "hotel_avg_rate_2025": "Average of the monthly rates captured in 2025.",
    "hotel_avg_rate_2026": "Average of the monthly rates captured in 2026.",
    "places_inside_building": "Number of other places located inside the same building.",
    "contained_places_categories": "Categories of the places inside the building.",
    "building_id": "Building identifier from the source dataset.",
    "building_name": "Building name, where the source has one.",
    "polygon_source": "How the footprint was matched: overture_contains, osm_name or overture_nearest.",
    "building_area_m2": "Footprint area in square metres.",
    "building_polygon_wkt": "Building footprint as a WKT MULTIPOLYGON in WGS84 (EPSG:4326).",
    "building_match_grade": "Match confidence grade, A (highest) to E (lowest).",
    "building_match_score": "Numeric match score behind the grade.",
    "building_dist_m": "Distance from the hotel point to the building, in metres (0 when inside).",
    "building_osm_id": "OpenStreetMap ID of the building, where matched by name.",
    "building_osm_name": "OpenStreetMap name of the building.",
    "oldest_seen_date": "Earliest date the place is known to have been listed.",
    "first_seen_month": "First monthly release containing the hotel.",
    "last_seen_month": "Latest monthly release containing the hotel.",
    "last_confirmed_month": "Latest release in which the hotel was confirmed operating.",
    "closed_month": "Release in which the hotel was first marked closed.",
    "continuity_months": "Number of monthly releases containing the hotel.",
    "lifecycle_stage": "Lifecycle stage derived from the monthly history.",
    "rating_first": "Rating in the first release.",
    "rating_latest": "Rating in the latest release.",
    "rating_trend_12m": "Change in rating over the last 12 months.",
    "reviews_gained_last_month": "Reviews added since the previous release.",
    "reviews_gained_all_time": "Reviews added since the hotel was first seen.",
    "review_velocity_3m": "Average reviews gained per month over the last 3 months.",
    "status_history": "Operating status by month.",
    "name_history": "Previous names with the month they changed.",
    "facts_change_timeline": "Month-by-month list of changed facts.",
    "facts_changes_total": "Total number of fact changes since the first release.",
    "hotel_rating_change_2025": "Rating change during 2025.",
    "hotel_reviews_gained_2025": "Reviews gained during 2025.",
    "hotel_facts_changes_2025": "Fact changes during 2025.",
    "hotel_rating_change_2026": "Rating change during 2026.",
    "hotel_reviews_gained_2026": "Reviews gained during 2026.",
    "hotel_facts_changes_2026": "Fact changes during 2026.",
}
# Column patterns described on the data pages (Appendix B, §7).
PATTERNS = [
    ("amenities_added", "History & changes", "Amenities added since the previous release (also amenities_removed, attributes_added/removed, languages_added/removed)."),
    ("nearest_<service>", "Neighbourhood (within 3 km)", "Name of the nearest place of a service type, e.g. nearest_pharmacy; nearest_<service>_m gives the distance in metres."),
    ("count_<group>_3km", "Neighbourhood (within 3 km)", "Number of open places of a category group within 3 km."),
    ("places_within_500m", "Neighbourhood (within 3 km)", "Open places within 500 m (also places_within_1km and places_within_3km)."),
    ("nearby_lodging_*", "Neighbourhood (within 3 km)", "Other places to stay nearby: the competitive set."),
    ("nearby_openings_*", "Neighbourhood (within 3 km)", "Places that opened nearby, by period."),
    ("nearby_closures_*", "Neighbourhood (within 3 km)", "Places that closed nearby, by period."),
    ("neighbourhood_momentum_12m", "Neighbourhood (within 3 km)", "Balance of openings and closures nearby over 12 months."),
    ("possible_replacements", "Neighbourhood (within 3 km)", "Places that may have replaced a closed place at the same spot."),
    ("stable_recommendations_*", "Neighbourhood (within 3 km)", "Nearby places that stay highly rated across releases."),
]
SKIP_UNKNOWN = {"amenities_added … languages_removed"}

FILL = {"phone": 220, "website": 217, "rating": 227, "rate_history_by_month": 229, "building_polygon_wkt": 215, "amenities": 210,
        "hotel_rates_by_site": 209, "lowest_offer_latest": 209, "hotel_class": 193, "check_in_time": 159, "direct_offer_latest": 135,
        "brand": 30}

POLYGONS = [
    ("Hotel Eden Nord Soller", "Spain", 949.6, "osm_name",
     "MULTIPOLYGON (((2.6967423 39.7994039, 2.6968025 39.7992372, 2.6970276 39.7992449, 2.6970236 39.7993252, 2.6971609 39.7993453, 2.6971311 39.799438, 2.6971048 39.7994342, 2.6971057 39.7994244, 2.6969995 39.7994209, 2.6969874 39.7996061, 2.6967543 39.7995984, 2.6967637 39.799406, 2.6967423 39.7994039)))"),
    ("Viva Maya by Wyndham", "Mexico", 1538.6, "overture_contains",
     "MULTIPOLYGON (((-87.089957 20.6065914, -87.0899333 20.6064867, -87.0897814 20.6065169, -87.089829 20.6067265, -87.0896928 20.6069567, -87.0899069 20.6069619, -87.0899652 20.6069072, -87.0901903 20.6068736, -87.0901536 20.6066581, -87.0900471 20.6066739, -87.0900363 20.6066104, -87.089957 20.6065914)))"),
    ("Holiday Inn New York City - Times Square by IHG", "United States", 548.3, "overture_contains",
     "MULTIPOLYGON (((-73.9918413 40.7552031, -73.991692 40.7551405, -73.991647 40.7552021, -73.9916025 40.755263, -73.9917845 40.7553394, -73.9917001 40.7554548, -73.9917413 40.7554721, -73.9917824 40.7554893, -73.9919743 40.7552267, -73.9918916 40.7551921, -73.9918593 40.7551785, -73.9918413 40.7552031)))"),
    ("Paradise Beach Hotel", "Greece", 678.2, "osm_name",
     "MULTIPOLYGON (((20.9305216 37.76193, 20.9306591 37.7618932, 20.9306742 37.7619286, 20.9307533 37.7619075, 20.9307279 37.7618483, 20.9309436 37.7617906, 20.9309858 37.761889, 20.9309331 37.7619031, 20.9309513 37.7619458, 20.9308302 37.7619782, 20.930849 37.762022, 20.9305905 37.7620911, 20.9305216 37.76193)))"),
]

OTAS = ["Booking.com", "Hotels.com", "Expedia", "Super.com", "CheapTickets", "Priceline", "Orbitz", "Travelocity", "eDreams", "Reserving",
        "Agoda", "Etrip", "Trip.com", "muv", "TravelUp", "Stayforlong", "Clicktrip", "Evendo", "Tripening", "Traveluro", "ZenHotels",
        "Catchit", "ClosestHotel", "Traveloka", "Klook"]
META = ["Bluepillow", "Vio.com", "goseek", "Wego", "KAYAK", "DealBase", "HotelsCombined", "Tripadvisor", "trivago", "momondo", "Kiwi.com", "HomeToGo"]
OTHER = ["Amimir.com", "Qantas Hotels", "Billabook.com", "BusinessHotels.com", "Hotel prive", "Luxury Escapes", "lastminute.com",
         "Long Stay Discounts", "EaseMyTrip.com", "CHECK24.de"]


def main() -> None:
    hotel_cols = []
    for group, cols in GROUPS.items():
        for c in cols:
            # Columns that only arrive with the final sample (Appendix D) are not claimed as in the sample.
            if c in {"email", "whatsapp", "facebook_url", "instagram_url", "linkedin_url", "x_url", "tiktok_url", "youtube_url",
                     "place_type", "description", "is_claimed", "parent_id", "photo_cover_url", "photo_urls", "logo_url", "plus_code",
                     "h3", "review_sentiments", "reviews", "similar_places", "booking_url", "hotel_search_url"}:
                continue
            hotel_cols.append({"name": c, "group": group, "meaning": MEANING.get(c), "example": None, "fill": FILL.get(c),
                               "private": c in PRIVATE})
    for name, group, meaning in PATTERNS:
        hotel_cols.append({"name": name, "group": group, "meaning": meaning, "example": None, "fill": None, "private": False, "pattern": True})

    columns = {"interim": True, "note": "Seeded from the field-group reference; Phase B lists every column of every sheet.", "sheets": [
        {"name": "Hotels", "rows": 250, "columns_total": 207, "columns": hotel_cols,
         "description": "One row per hotel: match columns, then every fact column."},
        {"name": "Column guide", "rows": 76, "columns_total": 3, "columns": [], "description": "Definitions: file, column, meaning."},
        {"name": "Hotel changes", "rows": 1915, "columns_total": 12, "columns": [], "description": "Monthly changes 2025-02 to 2026-10."},
        {"name": "Category counts", "rows": 201158, "columns_total": 6, "columns": [],
         "description": "Open places within 500 m, 1 km and 3 km of each hotel, per category."},
        {"name": "Buildings", "rows": 215, "columns_total": 14, "columns": [],
         "description": "Building footprint per hotel: ID, name, source, area, grade, score, distance, OSM ID, polygon WKT."},
    ]}

    stats = {
        "interim": True, "file_size_bytes": None, "format": "xlsx", "release": "2026-10",
        "sheets": {"Hotels": {"rows": 250, "columns": 207}, "Column guide": {"rows": 76, "columns": 3},
                   "Hotel changes": {"rows": 1915, "columns": 12}, "Category counts": {"rows": 201158, "columns": 6},
                   "Buildings": {"rows": 215, "columns": 14}},
        "countries": {"count": 32, "all": None, "top": [["Spain", 41], ["Greece", 32], ["Germany", 23], ["Austria", 16], ["Italy", 16],
                                                        ["Egypt", 15], ["Mexico", 12], ["United States", 12], ["Portugal", 10],
                                                        ["Dominican Republic", 10]]},
        "cities": {"count": 190},
        "fill": {"total": 250, "fields": FILL},
        "booking_sites": {"distinct": 105, "median_per_hotel": 22, "max_per_hotel": 42, "by_type": {
            "ota": {"count": 29, "sites": [[s, None] for s in OTAS], "partial": True},
            "metasearch": {"count": 12, "sites": [[s, None] for s in META]},
            "other": {"count": None, "sites": [[s, None] for s in OTHER], "partial": True}}},
        "direct_vs_ota": {"hotels_with_direct": 135, "above_lowest_ota_pct": 63, "median_gap_pct": 5},
        "polygon_grades": {"A": 154, "B": 24, "C": 14, "D": 17, "E": 6, "none": 35},
        "polygon_sources": {"overture_contains": 102, "osm_name": 76, "overture_nearest": 37},
        "polygon_median_area_m2": 946,
        "hotel_class": {"4": 91, "3": 49, "5": 45, "2": 7, "1": 1, "none": 57},
        "match_tiers": {"high": 193, "medium": 36, "low": 16, "no_match": 5},
        "changes": {"total": 1915, "types": {"field_changed": 1628, "field_added": 273, "first_listed": 10, "reopened": 2, "closed": 2},
                    "top_fields": [["amenities", 623], ["website", 337], ["rating", 253]], "per_month": {}},
        "category_counts": {"rows": 201158, "distinct_categories": 14230},
        "column_guide": {"definitions": 76},
    }

    polys = {"interim": True, "polygons": [
        {"name": n, "country": c, "area_m2": a, "grade": "A", "source": s, "wkt": w, "svg_path": polygon_svg_path(w)}
        for n, c, a, s, w in POLYGONS]}

    months, y, m = [], 2025, 1
    while f"{y:04d}-{m:02d}" <= "2026-10":
        months.append(f"{y:04d}-{m:02d}")
        y, m = (y + 1, 1) if m == 12 else (y, m + 1)
    releases = {"interim": True, "releases": [{"month": mo, "sample_changes": None} for mo in reversed(months)]}

    for name, obj in (("columns", columns), ("sample_stats", stats), ("polygon_preview", polys), ("releases", releases)):
        (DATA / f"{name}.json").write_text(json.dumps(obj, ensure_ascii=False, indent=1) + "\n", encoding="utf-8")
        print(f"wrote data/{name}.json")


if __name__ == "__main__":
    main()
