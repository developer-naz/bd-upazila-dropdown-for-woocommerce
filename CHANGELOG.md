# Changelog

All notable changes to this project are documented in this file.
The format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/) and the project uses [Semantic Versioning](https://semver.org/).

## [1.0.0] - 2026-11-12

### Added
- Searchable Upazila / Thana dropdown that follows the selected District for Bangladesh, on checkout and My Account → Addresses (billing and shipping).
- Data for all 64 districts and 495 upazilas, plus 50 Dhaka and 15 Chattogram metropolitan thanas (560 areas) with Bangla names.
- "District" and "Upazila / Thana" field labels for Bangladesh; postcode optional.
- "Other" option for areas that aren't listed, and server-side validation when it is turned off.
- Filters: `bdud_label_language`, `bdud_allow_other`, `bdud_show_bangla_district_names`, `bdud_default_country_bangladesh`, `bdud_locations`, `bdud_load_assets`.
- WooCommerce HPOS compatibility declaration.
