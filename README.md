<div align="center">

# 🇧🇩 BD Upazila Dropdown for WooCommerce

**Turn the City field into a searchable Upazila / Thana dropdown that follows the selected District, with Bangla names.**

![WordPress](https://img.shields.io/badge/WordPress-6.0%2B-21759B?style=flat&logo=wordpress&logoColor=white)
![WooCommerce](https://img.shields.io/badge/WooCommerce-7.0%2B-96588A?style=flat&logo=woocommerce&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-7.4%2B-777BB4?style=flat&logo=php&logoColor=white)
![HPOS](https://img.shields.io/badge/HPOS-compatible-success?style=flat)
![Areas](https://img.shields.io/badge/areas-560-006A4E?style=flat)
![License](https://img.shields.io/badge/license-GPL--2.0-orange?style=flat)

</div>

---

## 😩 The problem

WooCommerce already lists Bangladesh's 64 districts, but the City field is a free-text box. Customers type "Mirpur 10", "mirpur", "Dhaka", "Savar EPZ" or nothing useful at all. Courier booking becomes slow, addresses are wrong, and parcels come back.

## ✅ The solution

When the customer picks **Bangladesh** and a **District**, the City field becomes a searchable dropdown with only that district's **Upazilas / Thanas**:

~~~
Country:          Bangladesh
District:         Dhaka            ▼
Upazila / Thana:  Gulshan (গুলশান)  ▼   ← searchable, filtered by district
~~~

The saved value is always the clean English name (e.g. `Gulshan`), so it works with couriers, invoices, CSV exports and other plugins.

---

## ✨ Features

- 🗺️ **All 64 districts, 495 upazilas** plus **50 Dhaka** and **15 Chattogram** metropolitan thanas: **560 areas** in total
- 🔤 **Bangla names**: show English, Bangla, or both, e.g. "Gulshan (গুলশান)"
- 🔍 **Searchable** dropdown using WooCommerce's own selectWoo (no extra library)
- 🔁 **Smart reset**: changing the district clears the old area, even when names repeat (Mirpur is in both Dhaka and Kushtia)
- ✍️ **"Other" option**: customers can still type an area that isn't listed (can be turned off)
- 🛡️ **Server-side validation** when "Other" is turned off
- 🏷️ Field labels become **District** and **Upazila / Thana** for Bangladesh; postcode becomes optional
- 🚚 Works for **billing and shipping** addresses, on **Checkout** and **My Account → Addresses**
- 🗄️ **HPOS compatible**, no database tables, no settings to configure; a 4 KB script plus ~9 KB (gzipped) of area data, loaded only on checkout and address pages

---

## 🚀 Installation

1. Click **Code → Download ZIP** on this page.
2. In WordPress go to **Plugins → Add New → Upload Plugin** and upload the ZIP.
3. Activate **BD Upazila Dropdown for WooCommerce**. That's it.

### ⚠️ Classic checkout required

The dropdown works with the **classic checkout** (`[woocommerce_checkout]` shortcode, Elementor's Checkout widget and most page builders).
New stores on WooCommerce 8.3+ use the **Checkout block** by default. To switch: **Pages → Checkout → Edit**, replace the Checkout block with a Shortcode block containing `[woocommerce_checkout]`, then update.

---

## ⚙️ Options (filters)

Add these to your child theme's `functions.php` or a Code Snippets snippet.

~~~php
// Dropdown labels: 'both' (default) = "Gulshan (গুলশান)", 'bn' = "গুলশান", 'en' = "Gulshan".
add_filter( 'bdud_label_language', fn() => 'bn' );

// Only allow areas from the list (removes the "Other" option and validates on the server).
add_filter( 'bdud_allow_other', '__return_false' );

// Show Bangla names next to districts: "Dhaka (ঢাকা)".
add_filter( 'bdud_show_bangla_district_names', '__return_true' );

// Make Bangladesh the default checkout country for new customers.
add_filter( 'bdud_default_country_bangladesh', '__return_true' );

// Add your own delivery areas (e.g. Savar sub-areas) to a district.
add_filter( 'bdud_locations', function ( $locations ) {
	$locations['BD-13']['upazilas']['Ashulia'] = 'আশুলিয়া';
	ksort( $locations['BD-13']['upazilas'] );
	return $locations;
} );

// Load the dropdown on a custom page that isn't the WooCommerce checkout.
add_filter( 'bdud_load_assets', function ( $load ) {
	return $load || is_page( 'order-now' );
} );
~~~

| Filter | Default | Description |
|---|---|---|
| `bdud_label_language` | `both` | `en`, `bn` or `both` |
| `bdud_allow_other` | `true` | Allow typing an area that isn't listed |
| `bdud_show_bangla_district_names` | `false` | Bangla names in the District dropdown |
| `bdud_default_country_bangladesh` | `false` | Pre-select Bangladesh for new customers |
| `bdud_locations` | bundled data | Add, rename or remove areas |
| `bdud_load_assets` | checkout & edit-address pages | Where the script loads |

---

## 📁 Project structure

~~~
bd-upazila-dropdown-for-woocommerce/
├── bd-upazila-dropdown-for-woocommerce.php   # Labels, assets, validation, filters
├── assets/js/bd-upazila-dropdown.js           # Dependent dropdown logic
├── data/bd-locations.php                      # 64 districts → 560 areas (English + Bangla)
├── CHANGELOG.md
└── LICENSE
~~~

---

## 🗂️ Data sources

- **Upazilas (495)** with Bangla names: [nuhil/bangladesh-geocode](https://github.com/nuhil/bangladesh-geocode)
- **District codes**: WooCommerce's built-in Bangladesh states (`BD-01` … `BD-64`)
- **Metropolitan thanas**: Dhaka Metropolitan Police (50) and Chattogram Metropolitan Police (15)

Found a wrong or missing area? Please [open an issue](../../issues) or a pull request.

---

## 🛠️ Troubleshooting

- **City is still a text box:** your checkout uses the Checkout block. See *Classic checkout required* above.
- **Dropdown doesn't appear:** make sure Country is Bangladesh and a District is selected, then clear your cache plugin's cache.
- **Custom checkout page (funnel/landing page):** use the `bdud_load_assets` filter.
- Always test on a staging site before changing a live store.

---

## 🗺️ Roadmap

- [ ] Checkout block (Store API) support
- [ ] Union / area level for Dhaka city
- [ ] Shipping cost by district (inside / outside Dhaka)
- [ ] Courier-ready area names (Steadfast, Pathao, RedX)

---

## 📄 License

[GPL-2.0-or-later](LICENSE), the same license as WordPress and WooCommerce.

---

<div align="center">

### 👨‍💻 Built by Md Nazmul Islam (Developer Naz)

Web Developer | WordPress & Shopify Expert | SEO Specialist

[![LinkedIn](https://img.shields.io/badge/LinkedIn-developernaz-0A66C2?style=flat&logo=linkedin&logoColor=white)](https://www.linkedin.com/in/developernaz/)
[![GitHub](https://img.shields.io/badge/GitHub-developer--naz-181717?style=flat&logo=github&logoColor=white)](https://github.com/developer-naz)

⭐ If this plugin helps your store, please give it a star!

</div>
