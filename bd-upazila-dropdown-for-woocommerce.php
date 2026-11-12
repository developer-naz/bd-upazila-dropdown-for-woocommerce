<?php
/**
 * Plugin Name:          BD Upazila Dropdown for WooCommerce
 * Plugin URI:           https://github.com/developer-naz/bd-upazila-dropdown-for-woocommerce
 * Description:          Turns the City field into a searchable Upazila / Thana dropdown that follows the selected District when the country is Bangladesh. Covers all 64 districts, 495 upazilas and the Dhaka & Chattogram metropolitan thanas, with Bangla names.
 * Version:              1.0.0
 * Author:               Md Nazmul Islam (developer-naz)
 * Author URI:           https://www.linkedin.com/in/developernaz/
 * License:              GPL-2.0-or-later
 * License URI:          https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:          bdud
 * Requires at least:    6.0
 * Requires PHP:         7.4
 * Requires Plugins:     woocommerce
 * WC requires at least: 7.0
 *
 * @package BDUD
 */

defined( 'ABSPATH' ) || exit;

define( 'BDUD_VERSION', '1.0.0' );
define( 'BDUD_FILE', __FILE__ );
define( 'BDUD_PATH', plugin_dir_path( __FILE__ ) );
define( 'BDUD_URL', plugin_dir_url( __FILE__ ) );

/**
 * Declare compatibility: HPOS yes, Checkout block no (classic checkout only).
 */
add_action( 'before_woocommerce_init', function () {
	if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', BDUD_FILE, true );
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', BDUD_FILE, false );
	}
} );

/* -------------------------------------------------------------------------
 * Data helpers
 * ---------------------------------------------------------------------- */

/**
 * All districts and their areas, keyed by WooCommerce state code (BD-01 ... BD-64).
 *
 * Filter: bdud_locations — add, rename or remove areas.
 *
 * @return array<string, array{bn: string, upazilas: array<string, string>}>
 */
function bdud_locations() {
	static $locations = null;

	if ( null === $locations ) {
		$locations = (array) apply_filters( 'bdud_locations', require BDUD_PATH . 'data/bd-locations.php' );
	}

	return $locations;
}

/**
 * Areas (English => Bangla) for one district code, or an empty array.
 *
 * @param string $state_code WooCommerce state code, e.g. BD-13.
 * @return array<string, string>
 */
function bdud_get_areas( $state_code ) {
	$locations = bdud_locations();
	return isset( $locations[ $state_code ]['upazilas'] ) ? (array) $locations[ $state_code ]['upazilas'] : array();
}

/**
 * Dropdown label language: 'en', 'bn' or 'both' (default).
 * The saved value is always the English name, so couriers can read it.
 *
 * @return string
 */
function bdud_label_language() {
	$mode = apply_filters( 'bdud_label_language', 'both' );
	return in_array( $mode, array( 'en', 'bn', 'both' ), true ) ? $mode : 'both';
}

/**
 * Whether customers may type an area that is not in the list ("Other").
 *
 * @return bool
 */
function bdud_allow_other() {
	return (bool) apply_filters( 'bdud_allow_other', true );
}

/**
 * Build the visible label for an area.
 *
 * @param string $en   English name.
 * @param string $bn   Bangla name.
 * @param string $mode en | bn | both.
 * @return string
 */
function bdud_format_label( $en, $bn, $mode ) {
	if ( 'bn' === $mode && '' !== $bn ) {
		return $bn;
	}
	if ( 'both' === $mode && '' !== $bn ) {
		return sprintf( '%s (%s)', $en, $bn );
	}
	return $en;
}

/* -------------------------------------------------------------------------
 * Field labels for Bangladesh
 * ---------------------------------------------------------------------- */

add_filter( 'woocommerce_get_country_locale', function ( $locale ) {
	$locale['BD']['state']['label']       = __( 'District', 'bdud' );
	$locale['BD']['state']['required']    = true;
	$locale['BD']['city']['label']        = __( 'Upazila / Thana', 'bdud' );
	$locale['BD']['postcode']['required'] = false;

	return $locale;
} );

/**
 * Optional: show Bangla names next to districts, e.g. "Dhaka (ঢাকা)".
 * Filter: bdud_show_bangla_district_names (default false).
 */
add_filter( 'woocommerce_states', function ( $states ) {
	if ( empty( $states['BD'] ) || ! apply_filters( 'bdud_show_bangla_district_names', false ) ) {
		return $states;
	}

	foreach ( bdud_locations() as $code => $district ) {
		if ( isset( $states['BD'][ $code ] ) && ! empty( $district['bn'] ) ) {
			$states['BD'][ $code ] = sprintf( '%s (%s)', trim( $states['BD'][ $code ] ), $district['bn'] );
		}
	}

	return $states;
} );

/**
 * Optional: make Bangladesh the default checkout country for new customers.
 * Filter: bdud_default_country_bangladesh (default false).
 */
add_filter( 'default_checkout_billing_country', function ( $country ) {
	if ( empty( $country ) && apply_filters( 'bdud_default_country_bangladesh', false ) ) {
		return 'BD';
	}
	return $country;
} );

/* -------------------------------------------------------------------------
 * Front-end assets
 * ---------------------------------------------------------------------- */

/**
 * Whether the dropdown should load on the current page.
 *
 * @return bool
 */
function bdud_should_load() {
	$load = false;

	if ( function_exists( 'is_checkout' ) && is_checkout() && ! is_order_received_page() ) {
		$load = true;
	} elseif ( function_exists( 'is_account_page' ) && is_account_page() && is_wc_endpoint_url( 'edit-address' ) ) {
		$load = true;
	}

	return (bool) apply_filters( 'bdud_load_assets', $load );
}

/**
 * Data passed to the dropdown script.
 *
 * @return array
 */
function bdud_script_data() {
	$mode  = bdud_label_language();
	$areas = array();

	foreach ( bdud_locations() as $code => $district ) {
		foreach ( (array) $district['upazilas'] as $en => $bn ) {
			$areas[ $code ][] = array( (string) $en, bdud_format_label( (string) $en, (string) $bn, $mode ) );
		}
	}

	return array(
		'areas'      => $areas,
		'allowOther' => bdud_allow_other(),
		'i18n'       => array(
			'placeholder' => __( 'Select Upazila / Thana', 'bdud' ),
			'other'       => __( 'Other (type it in)', 'bdud' ),
		),
	);
}

add_action( 'wp_enqueue_scripts', function () {
	if ( ! bdud_should_load() ) {
		return;
	}

	$deps = array( 'jquery' );
	if ( wp_script_is( 'selectWoo', 'registered' ) ) {
		$deps[] = 'selectWoo';
	}

	wp_enqueue_script( 'bdud-dropdown', BDUD_URL . 'assets/js/bd-upazila-dropdown.js', $deps, BDUD_VERSION, true );
	wp_add_inline_script( 'bdud-dropdown', 'window.bdudData = ' . wp_json_encode( bdud_script_data() ) . ';', 'before' );

	wp_register_style( 'bdud-dropdown', false, array(), BDUD_VERSION );
	wp_enqueue_style( 'bdud-dropdown' );
	wp_add_inline_style(
		'bdud-dropdown',
		'.bdud-city-select{width:100%;}.bdud-other-input{margin-top:8px;}'
	);
}, 20 );

/* -------------------------------------------------------------------------
 * Server-side validation (only when "Other" is turned off)
 * ---------------------------------------------------------------------- */

add_action( 'woocommerce_after_checkout_validation', function ( $data, $errors ) {
	if ( bdud_allow_other() ) {
		return;
	}

	foreach ( array( 'billing', 'shipping' ) as $type ) {
		if ( 'shipping' === $type && empty( $data['ship_to_different_address'] ) ) {
			continue;
		}

		$country = isset( $data[ $type . '_country' ] ) ? $data[ $type . '_country' ] : '';
		$state   = isset( $data[ $type . '_state' ] ) ? $data[ $type . '_state' ] : '';
		$city    = isset( $data[ $type . '_city' ] ) ? trim( (string) $data[ $type . '_city' ] ) : '';
		$areas   = bdud_get_areas( $state );

		if ( 'BD' !== $country || '' === $city || empty( $areas ) ) {
			continue;
		}

		if ( ! isset( $areas[ $city ] ) ) {
			$errors->add(
				'validation',
				'shipping' === $type
					? __( 'Please select a valid shipping Upazila / Thana from the list.', 'bdud' )
					: __( 'Please select a valid Upazila / Thana from the list.', 'bdud' )
			);
		}
	}
}, 10, 2 );
