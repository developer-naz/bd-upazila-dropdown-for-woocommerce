/**
 * BD Upazila Dropdown for WooCommerce
 *
 * Turns the City field into a searchable Upazila / Thana dropdown that follows
 * the selected District (state) when the country is Bangladesh.
 *
 * The original City input stays in the form (hidden) and always holds the
 * saved value, so WooCommerce, couriers and other plugins keep working.
 *
 * @package BDUD
 */
( function ( $ ) {
	'use strict';

	if ( typeof window.bdudData === 'undefined' ) {
		return;
	}

	var data      = window.bdudData;
	var OTHER     = '__bdud_other__';
	var lastState = {};

	function hasSelectWoo() {
		return typeof $.fn.selectWoo === 'function';
	}

	function getSelect( prefix ) {
		return $( '#' + prefix + '_city_bdud' );
	}

	function removeSelect( prefix ) {
		var $select = getSelect( prefix );
		var $input  = $( '#' + prefix + '_city' );

		if ( $select.length ) {
			if ( hasSelectWoo() && $select.data( 'select2' ) ) {
				$select.selectWoo( 'destroy' );
			}
			$select.remove();
		}

		$input.removeClass( 'bdud-other-input' ).show();
	}

	function buildSelect( prefix, areas, isChange ) {
		var $input  = $( '#' + prefix + '_city' );
		var current = $.trim( $input.val() || '' );
		var match   = '';

		removeSelect( prefix );

		var $select = $( '<select></select>', {
			id: prefix + '_city_bdud',
			'class': 'bdud-city-select',
			'aria-label': data.i18n.placeholder
		} );

		$select.append( $( '<option></option>', { value: '', text: data.i18n.placeholder } ) );

		$.each( areas, function ( i, area ) {
			// After a district change, never carry over the old value
			// (names like "Mirpur" exist in more than one district).
			if ( ! isChange && current && area[0].toLowerCase() === current.toLowerCase() ) {
				match = area[0];
			}
			$select.append( $( '<option></option>', { value: area[0], text: area[1] } ) );
		} );

		if ( data.allowOther ) {
			$select.append( $( '<option></option>', { value: OTHER, text: data.i18n.other } ) );
		}

		$input.before( $select );

		if ( match ) {
			// Saved value is in the list: select it.
			$select.val( match );
			$input.val( match ).hide();
		} else if ( current && ! isChange && data.allowOther ) {
			// Saved value is a custom area: keep it visible as "Other".
			$select.val( OTHER );
			$input.addClass( 'bdud-other-input' ).show();
		} else {
			// District changed (or custom values not allowed): start fresh.
			$select.val( '' );
			$input.val( '' ).hide();
		}

		if ( hasSelectWoo() ) {
			$select.selectWoo( { width: '100%' } );
		}
	}

	function apply( prefix ) {
		var $input = $( '#' + prefix + '_city' );

		if ( ! $input.length ) {
			return;
		}

		var country = $( '#' + prefix + '_country' ).val() || '';
		var state   = $( '#' + prefix + '_state' ).val() || '';
		var key     = country + '|' + state;

		// Only a real change of country or district clears the field.
		var isChange = Object.prototype.hasOwnProperty.call( lastState, prefix ) && lastState[ prefix ] !== key;
		lastState[ prefix ] = key;

		var areas = ( 'BD' === country && state && data.areas[ state ] ) ? data.areas[ state ] : null;

		if ( ! areas ) {
			removeSelect( prefix );
			return;
		}

		buildSelect( prefix, areas, isChange );
	}

	// Copy the chosen area into the real City input.
	$( document.body ).on( 'change', '.bdud-city-select', function () {
		var prefix = this.id.replace( '_city_bdud', '' );
		var $input = $( '#' + prefix + '_city' );
		var value  = $( this ).val();

		if ( OTHER === value ) {
			$input.val( '' ).addClass( 'bdud-other-input' ).show().trigger( 'focus' );
		} else {
			$input.val( value ).removeClass( 'bdud-other-input' ).hide();
		}

		$input.trigger( 'change' );
	} );

	// District changed.
	$( document.body ).on( 'change', '#billing_state, #shipping_state', function () {
		apply( 0 === this.id.indexOf( 'shipping' ) ? 'shipping' : 'billing' );
	} );

	// Country changed (WooCommerce rebuilds the District field first).
	$( document.body ).on( 'country_to_state_changed', function ( event, country, $wrapper ) {
		if ( $wrapper && $wrapper.length ) {
			if ( $wrapper.find( '#billing_city' ).length ) {
				apply( 'billing' );
			}
			if ( $wrapper.find( '#shipping_city' ).length ) {
				apply( 'shipping' );
			}
			return;
		}
		apply( 'billing' );
		apply( 'shipping' );
	} );

	$( function () {
		apply( 'billing' );
		apply( 'shipping' );
	} );
}( jQuery ) );
