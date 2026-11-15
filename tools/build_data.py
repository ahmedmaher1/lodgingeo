#!/usr/bin/env python3
"""Generate the site's data/*.json files from the sample workbook (brief §10).

Usage:
    python tools/build_data.py            # regenerate data/*.json from the sample
    python tools/build_data.py --check    # fail if committed data/*.json are stale

The workbook is read with openpyxl in read-only mode, and every column is looked
up by its header name, never by position. Without a workbook (Phase A) the
script prints a notice and exits 0.
"""
from __future__ import annotations

import argparse
import json
import math
import re
import statistics
import sys
import tempfile
from collections import Counter, OrderedDict
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent
SAMPLE = ROOT / "storage" / "samples" / "lodgingeo-hotel-data-sample-2026-10.xlsx"
DATA = ROOT / "data"
HISTORY_START = "2025-01"
LATEST_RELEASE = "2026-10"
OUTPUTS = ["columns", "sample_stats", "sample_preview", "polygon_preview", "rates_example", "releases"]

# ---------------------------------------------------------------- groups (Appendix B)
GROUPS: "OrderedDict[str, list[str]]" = OrderedDict([
    ("Match & IDs", ["product_id", "hotel_name", "city", "country", "country_code", "ld_id", "ld_name", "ld_name_alt", "ld_city",
                     "ld_address", "ld_category", "name_score", "city_score", "confidence", "match_tier", "second_candidate_name",
                     "second_candidate_confidence", "closest_candidate_name", "closest_candidate_confidence", "flags", "hotel_ld_id"]),
    ("Location & address", ["latitude", "longitude", "name", "name_alt", "parent_name", "street_address", "neighborhood", "state",
                            "postal_code", "country_name", "formatted_address", "timezone", "plus_code", "h3"]),
    ("Contacts & identity", ["phone", "website", "domain", "chain_from_website", "brand", "wikipedia_url", "email", "whatsapp",
                             "facebook_url", "instagram_url", "linkedin_url", "x_url", "tiktok_url", "youtube_url",
                             "place_type", "description", "is_claimed", "parent_id", "photo_cover_url", "photo_urls", "logo_url"]),
    ("Hotel details & amenities", ["category", "categories", "status", "hotel_class", "rating", "review_count", "rating_distribution",
                                   "price_level", "check_in_time", "check_out_time", "amenities", "amenities_unavailable", "attributes",
                                   "languages", "floor_level", "area_type", "review_sentiments", "reviews", "similar_places"]),
    ("Prices & booking sites", ["hotel_rate_latest", "hotel_rates_by_site", "booking_sites_latest", "booking_otas_latest",
                                "booking_metasearch_latest", "booking_other_sites_latest", "lowest_offer_latest", "direct_offer_latest",
                                "direct_vs_lowest_ota_pct", "booking_sites_months_seen", "rate_history_by_month", "hotel_avg_rate_2025",
                                "hotel_avg_rate_2026", "booking_url", "hotel_search_url"]),
    ("Building polygon", ["places_inside_building", "contained_places_categories", "building_id", "building_name", "polygon_source",
                          "building_area_m2", "building_polygon_wkt", "building_match_grade", "building_match_score", "building_dist_m",
                          "building_osm_id", "building_osm_name"]),
    ("History & changes", ["oldest_seen_date", "first_seen_month", "last_seen_month", "last_confirmed_month", "closed_month",
                           "continuity_months", "lifecycle_stage", "rating_first", "rating_latest", "rating_trend_12m",
                           "reviews_gained_last_month", "reviews_gained_all_time", "review_velocity_3m", "status_history", "name_history",
                           "facts_change_timeline", "facts_changes_total", "hotel_rating_change_2025", "hotel_reviews_gained_2025",
                           "hotel_facts_changes_2025", "hotel_rating_change_2026", "hotel_reviews_gained_2026", "hotel_facts_changes_2026"]),
    ("Neighbourhood (within 3 km)", []),
])
PATTERN_GROUPS = [
    (r"^(amenities|attributes|languages)_(added|removed)$", "History & changes"),
    (r"_(added|removed)$", "History & changes"),
    (r"^(nearest_|count_|places_within_|nearby_|neighbourhood_|neighborhood_|stable_recommendations|trending|seasonal|possible_replacements|new_places_|closed_places_|changed_places_|ev_|fuel_|openings_|closures_)", "Neighbourhood (within 3 km)"),
]
SHEET_GROUP = {"Hotel changes": "Hotel changes", "Category counts": "Category counts", "Buildings": "Buildings"}
# Columns never shown on public pages (client input and match candidates).
PRIVATE = {"product_id", "hotel_name", "city", "country", "second_candidate_name", "second_candidate_confidence",
           "closest_candidate_name", "closest_candidate_confidence"}


def group_for(col: str, sheet: str, in_neighbourhood: bool) -> str:
    if sheet != "Hotels":
        return SHEET_GROUP.get(sheet, sheet)
    for g, cols in GROUPS.items():
        if col in cols:
            return g
    for pat, g in PATTERN_GROUPS:
        if re.search(pat, col):
            return g
    if in_neighbourhood:
        return "Neighbourhood (within 3 km)"
    return "Hotel details & amenities"


# ---------------------------------------------------------------- polygons → SVG
def wkt_outer_rings(wkt: str) -> list[list[tuple[float, float]]]:
    """Outer ring of each polygon in a (MULTI)POLYGON WKT string."""
    rings = []
    for body in re.findall(r"\(\(([^()]+)\)", wkt or ""):
        pts = []
        for pair in body.split(","):
            xy = pair.split()
            if len(xy) >= 2:
                pts.append((float(xy[0]), float(xy[1])))
        if len(pts) >= 3:
            rings.append(pts)
    return rings


def polygon_svg_path(wkt: str, width: int = 300, height: int = 200, pad: int = 14) -> str:
    """Project lon/lat around the centroid (lon scaled by cos(lat)) and fit into the viewBox."""
    rings = wkt_outer_rings(wkt)
    pts = [p for r in rings for p in r]
    if not pts:
        return ""
    lon0 = (min(p[0] for p in pts) + max(p[0] for p in pts)) / 2
    lat0 = (min(p[1] for p in pts) + max(p[1] for p in pts)) / 2
    k = math.cos(math.radians(lat0))
    proj = [[((x - lon0) * k, y - lat0) for x, y in r] for r in rings]
    xs = [p[0] for r in proj for p in r]
    ys = [p[1] for r in proj for p in r]
    w, h = (max(xs) - min(xs)) or 1e-12, (max(ys) - min(ys)) or 1e-12
    s = min((width - 2 * pad) / w, (height - 2 * pad) / h)
    ox = (width - w * s) / 2 - min(xs) * s
    oy = (height - h * s) / 2 + max(ys) * s
    out = []
    for r in proj:
        seg = [f"{ox + x * s:.1f},{oy - y * s:.1f}" for x, y in r]
        out.append("M" + " L".join(seg) + " Z")
    return " ".join(out)


# ---------------------------------------------------------------- value parsing
def is_empty(v) -> bool:
    return v is None or (isinstance(v, str) and v.strip() in ("", "[]", "{}", "null", "None", "nan"))


def parse_list(v) -> list[str]:
    if is_empty(v):
        return []
    if isinstance(v, (int, float)):
        return []
    s = str(v).strip()
    try:
        j = json.loads(s)
        if isinstance(j, list):
            return [str(x.get("site") or x.get("name") if isinstance(x, dict) else x).strip() for x in j if x]
        if isinstance(j, dict):
            return [str(k).strip() for k in j]
    except (ValueError, TypeError):
        pass
    for sep in (";", "|", ","):
        if sep in s:
            return [p.strip() for p in s.split(sep) if p.strip()]
    return [s]


def to_num(v):
    if v is None:
        return None
    if isinstance(v, (int, float)):
        return float(v)
    m = re.search(r"-?\d+(?:[.,]\d+)?", str(v).replace("−", "-"))
    return float(m.group(0).replace(",", ".")) if m else None


def parse_pairs(v) -> list[list]:
    """'{"Booking.com": 120}' / '[{"site":..,"price":..}]' / 'Booking.com: 120; Expedia: 125' → [[site, price], ...]."""
    if is_empty(v):
        return []
    s = str(v).strip()
    try:
        j = json.loads(s)
        if isinstance(j, dict):
            return [[str(k), to_num(x)] for k, x in j.items() if to_num(x) is not None]
        if isinstance(j, list):
            out = []
            for x in j:
                if isinstance(x, dict):
                    k = x.get("site") or x.get("name") or x.get("month") or x.get("source")
                    p = x.get("price") or x.get("rate") or x.get("value")
                    if k is not None and to_num(p) is not None:
                        out.append([str(k), to_num(p)])
                elif isinstance(x, (list, tuple)) and len(x) >= 2 and to_num(x[1]) is not None:
                    out.append([str(x[0]), to_num(x[1])])
            return out
    except (ValueError, TypeError):
        pass
    out = []
    for part in re.split(r"[;|\n]", s):
        m = re.match(r"\s*(.+?)\s*[:=]\s*(.+)$", part)
        if m and to_num(m.group(2)) is not None:
            out.append([m.group(1), to_num(m.group(2))])
    return out


def trunc(v, n: int = 80) -> str:
    s = str(v)
    return s if len(s) <= n else s[: n - 1] + "…"


# ---------------------------------------------------------------- workbook
def find_sheet(wb, name: str):
    for ws in wb.worksheets:
        if ws.title.strip().lower() == name.lower():
            return ws
    return None


def read_sheet(ws) -> tuple[list[str], list[dict]]:
    rows = ws.iter_rows(values_only=True)
    header = [str(h).strip() if h is not None else "" for h in next(rows, [])]
    data = []
    for r in rows:
        if r is None or all(c is None for c in r):
            continue
        data.append({header[i]: r[i] for i in range(min(len(header), len(r))) if header[i]})
    return header, data


def pick(header: list[str], *names: str):
    low = {h.lower(): h for h in header}
    for n in names:
        if n.lower() in low:
            return low[n.lower()]
    return None


def guide_lookup(guide: list[dict], gh: list[str]):
    fcol = pick(gh, "file", "sheet")
    ccol = pick(gh, "column", "field")
    mcol = pick(gh, "meaning", "description", "definition")
    exact, patterns = {}, []
    for g in guide:
        col = str(g.get(ccol) or "").strip()
        if not col:
            continue
        meaning = str(g.get(mcol) or "").strip()
        sheet = str(g.get(fcol) or "").strip()
        for part in re.split(r"\s*/\s*|\s*,\s*", col):
            part = part.strip()
            if "*" in part or "<" in part:
                rx = "^" + re.sub(r"<[^>]+>|\\\*", ".+", re.escape(part)) + "$"
                patterns.append((rx, meaning, sheet))
            elif part:
                exact[(sheet.lower(), part)] = meaning
                exact.setdefault(("", part), meaning)

    def meaning_for(sheet: str, column: str) -> str | None:
        for key in ((sheet.lower(), column), ("", column)):
            if key in exact:
                return exact[key]
        for rx, m, _ in patterns:
            if re.match(rx, column):
                return m
        return None
    return meaning_for


def build(sample: Path) -> dict[str, object]:
    from openpyxl import load_workbook

    wb = load_workbook(sample, read_only=True, data_only=True)
    sheets = {}
    for name in ["Hotels", "Column guide", "Hotel changes", "Category counts", "Buildings"]:
        ws = find_sheet(wb, name)
        if ws is not None:
            sheets[name] = read_sheet(ws)
    for ws in wb.worksheets:
        if ws.title not in sheets:
            sheets[ws.title] = read_sheet(ws)

    hh, hotels = sheets["Hotels"]
    gh, guide = sheets.get("Column guide", ([], []))
    meaning_for = guide_lookup(guide, gh)

    # columns.json
    cols_out = {"generated_from": sample.name, "sheets": []}
    for sname, (header, rows) in sheets.items():
        in_nb = False
        entries = []
        for c in header:
            if not c:
                continue
            if sname == "Hotels" and c == "nearby_places_now":
                in_nb = True
            g = group_for(c, sname, in_nb)
            if sname == "Hotels" and c == "new_places_2026":
                in_nb = False
            fill = sum(1 for r in rows if not is_empty(r.get(c)))
            example = None
            if c not in PRIVATE:
                example = next((trunc(r[c]) for r in rows if not is_empty(r.get(c))), None)
            entries.append({"name": c, "group": g, "meaning": meaning_for(sname, c), "example": example,
                            "fill": fill, "private": c in PRIVATE})
        cols_out["sheets"].append({"name": sname, "rows": len(rows), "columns_total": len([h for h in header if h]),
                                   "columns": entries})

    # helpers over Hotels
    def col(*names):
        return pick(hh, *names)

    def filled(c):
        return sum(1 for r in hotels if c and not is_empty(r.get(c)))

    c_country = col("country_name")
    c_city = col("ld_city")
    stats: dict = {"generated_from": sample.name, "file_size_bytes": sample.stat().st_size, "format": "xlsx",
                   "release": LATEST_RELEASE, "sheets": {}}
    for sname, (header, rows) in sheets.items():
        stats["sheets"][sname] = {"rows": len(rows), "columns": len([h for h in header if h])}
    cc = Counter(str(r[c_country]).strip() for r in hotels if c_country and not is_empty(r.get(c_country)))
    stats["countries"] = {"count": len(cc), "top": [[k, v] for k, v in cc.most_common(10)], "all": sorted(cc)}
    cities = {(str(r.get(c_city)).strip(), str(r.get(c_country)).strip()) for r in hotels if c_city and not is_empty(r.get(c_city))}
    stats["cities"] = {"count": len(cities)}

    fill_fields = ["phone", "email", "whatsapp", "website", "facebook_url", "instagram_url", "linkedin_url", "x_url", "tiktok_url",
                   "youtube_url", "rating", "rate_history_by_month", "building_polygon_wkt", "amenities", "hotel_rates_by_site",
                   "lowest_offer_latest", "hotel_class", "check_in_time", "direct_offer_latest", "brand"]
    stats["fill"] = {"total": len(hotels), "fields": {f: filled(col(f)) for f in fill_fields if col(f)}}

    # booking sites
    by_type = {}
    for key, cname in (("ota", "booking_otas_latest"), ("metasearch", "booking_metasearch_latest"), ("other", "booking_other_sites_latest")):
        c = col(cname)
        cnt = Counter(s for r in hotels if c for s in parse_list(r.get(c)))
        by_type[key] = {"count": len(cnt), "sites": [[k, v] for k, v in cnt.most_common()]}
    c_sites = col("booking_sites_latest")
    per_hotel = []
    all_sites = set()
    for r in hotels:
        v = r.get(c_sites) if c_sites else None
        if is_empty(v):
            continue
        lst = parse_list(v)
        if lst:
            per_hotel.append(len(lst))
            all_sites.update(lst)
        elif to_num(v) is not None:
            per_hotel.append(int(to_num(v)))
    if not all_sites:
        for t in by_type.values():
            all_sites.update(s for s, _ in t["sites"])
    stats["booking_sites"] = {"distinct": len(all_sites), "median_per_hotel": statistics.median(per_hotel) if per_hotel else None,
                              "max_per_hotel": max(per_hotel) if per_hotel else None, "by_type": by_type}
    c_dir = col("direct_vs_lowest_ota_pct")
    gaps = [to_num(r.get(c_dir)) for r in hotels if c_dir and not is_empty(r.get(c_dir))]
    gaps = [g for g in gaps if g is not None]
    n_direct = filled(col("direct_offer_latest"))
    stats["direct_vs_ota"] = {"hotels_with_direct": n_direct, "with_gap": len(gaps),
                              "above_lowest_ota": sum(1 for g in gaps if g > 0),
                              "above_lowest_ota_pct": round(sum(1 for g in gaps if g > 0) / len(gaps) * 100) if gaps else None,
                              "median_gap_pct": round(statistics.median(gaps)) if gaps else None}

    def dist(cname, order=None):
        c = col(cname)
        cnt = Counter(("none" if is_empty(r.get(c)) else str(r.get(c)).strip()) for r in hotels) if c else Counter()
        return dict(sorted(cnt.items(), key=lambda kv: (order.index(kv[0]) if order and kv[0] in order else 99, -kv[1])))

    stats["polygon_grades"] = dist("building_match_grade", ["A", "B", "C", "D", "E", "none"])
    stats["polygon_sources"] = {k: v for k, v in dist("polygon_source").items() if k != "none"}
    areas = [to_num(r.get(col("building_area_m2"))) for r in hotels if col("building_area_m2") and not is_empty(r.get(col("building_area_m2")))]
    stats["polygon_median_area_m2"] = round(statistics.median(areas)) if areas else None
    stats["hotel_class"] = dist("hotel_class")
    stats["match_tiers"] = dist("match_tier", ["high", "medium", "low", "no_match"])
    for cname in ("category", "categories"):
        c = col(cname)
        if c:
            stats.setdefault("categories", Counter())
            for r in hotels:
                for v in parse_list(r.get(c)):
                    stats["categories"][v] += 1
    if "categories" in stats:
        stats["categories"] = [[k, v] for k, v in stats["categories"].most_common()]

    # changes
    ch_h, changes = sheets.get("Hotel changes", ([], []))
    c_type = pick(ch_h, "change_type", "type", "change")
    c_field = pick(ch_h, "field", "field_name", "column")
    c_month = pick(ch_h, "cycle_month", "month")
    stats["changes"] = {"total": len(changes),
                        "types": dict(Counter(str(r.get(c_type)) for r in changes if c_type).most_common()),
                        "top_fields": [[k, v] for k, v in Counter(str(r.get(c_field)) for r in changes if c_field and not is_empty(r.get(c_field))).most_common(10)],
                        "per_month": dict(sorted(Counter(str(r.get(c_month))[:7] for r in changes if c_month and not is_empty(r.get(c_month))).items()))}
    cat_h, cats = sheets.get("Category counts", ([], []))
    c_cat = pick(cat_h, "category")
    stats["category_counts"] = {"rows": len(cats), "distinct_categories": len({r.get(c_cat) for r in cats}) if c_cat else None}
    stats["column_guide"] = {"definitions": len(guide)}

    # preview rows (our own fields only)
    def row_view(r):
        sites = parse_list(r.get(c_sites)) if c_sites else []
        return {"hotel": r.get(col("name")), "city": r.get(c_city), "country": r.get(c_country),
                "lat": round(to_num(r.get(col("latitude"))) or 0, 5), "lon": round(to_num(r.get(col("longitude"))) or 0, 5),
                "class": r.get(col("hotel_class")), "rating": r.get(col("rating")),
                "booking_sites": len(sites) if sites else (to_num(r.get(c_sites)) if c_sites else None),
                "lowest_offer": r.get(col("lowest_offer_latest")), "building_m2": to_num(r.get(col("building_area_m2"))),
                "grade": r.get(col("building_match_grade"))}
    need = ["name", "latitude", "longitude", "hotel_class", "rating", "lowest_offer_latest", "building_area_m2", "building_match_grade"]
    preview, seen = [], set()
    for r in hotels:
        ctry = r.get(c_country)
        if ctry in seen or any(is_empty(r.get(col(f))) for f in need if col(f)):
            continue
        preview.append(row_view(r))
        seen.add(ctry)
        if len(preview) == 5:
            break
    contact_rows, seen = [], set()
    for r in hotels:
        if any(is_empty(r.get(col(f))) for f in ("name", "phone", "domain") if col(f)) or r.get(c_country) in seen:
            continue
        contact_rows.append({"hotel": r.get(col("name")), "city": r.get(c_city), "country": r.get(c_country),
                             "phone": r.get(col("phone")), "domain": r.get(col("domain"))})
        seen.add(r.get(c_country))
        if len(contact_rows) == 3:
            break
    nearest = None
    nb_cols = [h for h in hh if re.match(r"^nearest_[a-z0-9_]+$", h) and not h.endswith("_m")]
    for r in hotels:
        items = []
        for c in nb_cols:
            if not is_empty(r.get(c)) and not is_empty(r.get(c + "_m")):
                items.append({"service": c[len("nearest_"):].replace("_", " "), "column": c, "name": trunc(r.get(c), 60),
                              "metres": round(to_num(r.get(c + "_m")) or 0)})
        if len(items) >= 6:
            nearest = {"hotel": r.get(col("name")), "city": r.get(c_city), "country": r.get(c_country), "items": items}
            break
    sample_preview = {"rows": preview, "contact_rows": contact_rows, "nearest_example": nearest}

    # polygon_preview: 4 grade-A hotels from different countries
    polys, seen = [], set()
    c_wkt, c_grade = col("building_polygon_wkt"), col("building_match_grade")
    for r in hotels:
        if c_wkt is None or is_empty(r.get(c_wkt)) or str(r.get(c_grade)).strip() != "A" or r.get(c_country) in seen:
            continue
        wkt = str(r.get(c_wkt))
        polys.append({"name": r.get(col("name")), "country": r.get(c_country), "area_m2": round(to_num(r.get(col("building_area_m2"))) or 0, 1),
                      "grade": "A", "source": r.get(col("polygon_source")), "wkt": wkt, "svg_path": polygon_svg_path(wkt)})
        seen.add(r.get(c_country))
        if len(polys) == 4:
            break

    # rates_example: the hotel with the most parsed site rates
    best = None
    c_rates, c_hist = col("hotel_rates_by_site"), col("rate_history_by_month")
    for r in hotels:
        pairs = parse_pairs(r.get(c_rates)) if c_rates else []
        hist = parse_pairs(r.get(c_hist)) if c_hist else []
        if pairs and hist and (best is None or len(pairs) > len(best["rates"])):
            best = {"hotel": r.get(col("name")), "city": r.get(c_city), "country": r.get(c_country),
                    "rates": sorted(pairs, key=lambda p: p[1]), "history": sorted(hist)}
    rates_example = best

    months, y, m = [], 2025, 1
    while f"{y:04d}-{m:02d}" <= LATEST_RELEASE:
        months.append(f"{y:04d}-{m:02d}")
        y, m = (y + 1, 1) if m == 12 else (y, m + 1)
    per_month = stats["changes"]["per_month"]
    releases = {"releases": [{"month": mo, "sample_changes": per_month.get(mo, 0 if mo != HISTORY_START else None)} for mo in reversed(months)]}

    out = {"columns": cols_out, "sample_stats": stats, "sample_preview": sample_preview, "polygon_preview": {"polygons": polys},
           "releases": releases}
    if rates_example:
        out["rates_example"] = rates_example
    return out


def write_outputs(outputs: dict, target: Path) -> None:
    target.mkdir(parents=True, exist_ok=True)
    for name, obj in outputs.items():
        (target / f"{name}.json").write_text(json.dumps(obj, ensure_ascii=False, indent=1, default=str) + "\n", encoding="utf-8")


def main() -> int:
    ap = argparse.ArgumentParser(description=__doc__.splitlines()[0])
    ap.add_argument("--sample", type=Path, default=SAMPLE)
    ap.add_argument("--check", action="store_true", help="fail when committed data/*.json differ from the sample")
    args = ap.parse_args()
    if not args.sample.is_file():
        print(f"notice: no sample workbook at {args.sample.relative_to(ROOT) if args.sample.is_relative_to(ROOT) else args.sample}; "
              "skipping (Phase A uses the interim data files).")
        return 0
    outputs = build(args.sample)
    if args.check:
        with tempfile.TemporaryDirectory() as tmp:
            write_outputs(outputs, Path(tmp))
            stale = [n for n in outputs if not (DATA / f"{n}.json").is_file()
                     or json.loads((DATA / f"{n}.json").read_text()) != json.loads((Path(tmp) / f"{n}.json").read_text())]
        if stale:
            print("stale data files: " + ", ".join(f"data/{n}.json" for n in stale) + " (run python tools/build_data.py)")
            return 1
        print("data files are up to date")
        return 0
    write_outputs(outputs, DATA)
    print("wrote " + ", ".join(f"data/{n}.json" for n in outputs))
    return 0


if __name__ == "__main__":
    sys.exit(main())
