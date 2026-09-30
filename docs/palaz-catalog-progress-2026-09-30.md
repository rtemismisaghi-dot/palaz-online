# Palaz Online Catalog — extraction progress (2026-09-30)

Source: https://palazonline.com

## Current verified source inventory
- موکت: 179
- کاغذ دیواری: 228
- لمینیت: 22
- پادری: 31
- چمن مصنوعی: 3
- موکت تایل: 1
- Current verified catalog records: 449
- Previously audited upper bound: 464 (requires final page-by-page reconciliation before claiming completion)

## Current catalog extraction file
`docs/palaz-catalog-extraction-batch-2026-09-27.json`
- Current records: 255
- موکت: 78
- کاغذ دیواری: 132
- لمینیت: 22
- پادری: 19
- چمن مصنوعی: 3
- موکت تایل: 1

## Exact backlog
- موکت: 101
- کاغذ دیواری: 96
- پادری: 12
- Remaining reconciliation: 15 records are not yet verified in the local catalog file (4 carpet + 12 doormat, offset by 1 extra wallpaper record versus the earlier 464 estimate).

## Source pagination confirmed
- موکت: 15 pages
- کاغذ دیواری: 19 pages
- پادری: 3 pages

## Additional source category
The site's search/category filters also expose `کفپوش اسپاگتی`; its inventory count must be audited separately before adding records.

## Rules
- Do not invent products, prices, specifications or images.
- Keep source URL for every record.
- Product code is the deduplication key within category.
- Preserve the site's displayed base price and calculate the 10% VAT field.
- Enrich detail-page specifications and image URLs only when verified from the source.


## Latest extraction
The catalog JSON now contains 449 verified category-page records. No unverified placeholder records were added. Final reconciliation still requires resolving the remaining 15 records directly from the source.
