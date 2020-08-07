import 'babel-polyfill';

/* global shipping_nova_poshta_for_woocommerce */

/**
 * Class Admin.
 */
class Admin {
	constructor() {
		this.CITY_ID = 'shipping_nova_poshta_for_woocommerce_city';
		this.WAREHOUSE_ID = 'shipping_nova_poshta_for_woocommerce_warehouse';
		this.tips();
		this.initAdminShipping();
		this.initCitySearch();
		this.initWarehouseSearch();
	}

	initCitySearch() {
		const $city = $( this.CITY_ID );
		if ( ! $city.length ) {
			return;
		}
		$city.np_select2( {
			language: shipping_nova_poshta_for_woocommerce.language,
			minimumInputLength: 1,
			ajax: {
				url: shipping_nova_poshta_for_woocommerce.url,
				type: 'POST',
				data: function( params ) {
					return {
						'nonce': shipping_nova_poshta_for_woocommerce.nonce,
						'action': 'shipping_nova_poshta_for_woocommerce_city',
						'search': params.term,
					};
				},
				processResults: function( data ) {
					return {
						results: data,
					};
				},
			},
		} ).on( 'select2:select', function() {
			this.updateWarehouses();
		} );
	}

	initWarehouseSearch() {
		const $warehouse = $( this.WAREHOUSE_ID );
		if ( ! $warehouse.length ) {
			return;
		}
		$warehouse.np_select2( {
			language: shipping_nova_poshta_for_woocommerce.language,
		} );
		$( document ).ajaxComplete( function( event, xhr, settings ) {
			if ( 'woocommerce_save_order_items' === app.getQueryParams( 'action', settings.data ) ) {
				app.updateShippingMethodClasses();
			}
		} );
	}

	updateWarehouses( active = null ) {
		const $city = $( '#shipping_nova_poshta_for_woocommerce_city' ),
			$warehouse = $( '#shipping_nova_poshta_for_woocommerce_warehouse' );
		if ( ! $city.length || ! $warehouse ) {
			return;
		}
		$.ajax( {
			url: shipping_nova_poshta_for_woocommerce.url,
			type: 'POST',
			data: {
				'nonce': shipping_nova_poshta_for_woocommerce.nonce,
				'action': 'shipping_nova_poshta_for_woocommerce_warehouse',
				'city': $city.val(),
			},
			beforeSend: function() {
				$warehouse.addClass( 'inactive' );
			},
			success: function( data ) {
				$warehouse.find( 'option' ).remove();
				data.forEach( function( element ) {
					$warehouse.append( new Option( element.text, element.id, false, active === element.id ) );
				} );
				$warehouse.trigger( 'change' );
			},
			complete: function() {
				$warehouse.removeClass( 'inactive' );
			},
		} );
	}

	getQueryParams( params, href ) {
		const reg = new RegExp( '[?&]' + params + '=([^&#]*)', 'i' );
		const queryString = reg.exec( href );
		return queryString ? queryString[ 1 ] : '';
	}

	initAdminShipping() {
		const warehouse = $( '#shipping_nova_poshta_for_woocommerce_warehouse' ).val(),
			$orderItems = $( '#woocommerce-order-items' );
		$orderItems.on( 'click', '#order_shipping_line_items .edit-order-item', function() {
			app.replaceInputToSelect( 'city_id', 'shipping_nova_poshta_for_woocommerce_city' );
			app.replaceInputToSelect( 'warehouse_id', 'shipping_nova_poshta_for_woocommerce_warehouse' );
			app.init();
			app.updateWarehouses( warehouse );
		} );
		$orderItems.on( 'click', '#order_shipping_line_items .shipping_method', function() {
			app.updateShippingMethodClasses();
		} );
	}

	updateShippingMethodClasses() {
		const $methodBlock = $( '#order_shipping_line_items' ),
			$methodField = $methodBlock.find( '.shipping_method' );
		if ( $methodField.length && 'shipping_nova_poshta_for_woocommerce' === $methodField.val() ) {
			$methodBlock.addClass( 'shipping-nova-poshta-for-woocommerce' );
		} else {
			$methodBlock.removeClass( 'shipping-nova-poshta-for-woocommerce' );
		}
	}

	replaceInputToSelect( key, id ) {
		let $el = $( '#order_shipping_line_items input[value=' + key + ']' ),
			$field = $el.next( 'textarea' ),
			index = $field.closest( 'tr' ).index(),
			value = $( '#order_shipping_line_items .display_meta tr:eq(' + index + ') td' ).text(),
			option = new Option( value, $field.val() );
		$el.attr( 'type', 'hidden' );
		$field.replaceWith(
			'<select name="' + $field.attr( 'name' ) + '" id="' + id + '"></select>',
		);
		$( '#' + id ).append( option );
	}

	tips() {
		$( '.help-tip' ).tipTip(
			{
				'attribute': 'data-tip',
				'fadeIn': 50,
				'fadeOut': 50,
				'delay': 50,
			} );
	}
}

export default Admin;
