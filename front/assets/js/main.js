( function( $ ) {
	function init() {
		$( '#shipping_nova_poshta_for_woocommerce_city:not(.select2-hidden-accessible)' ).np_select2( {
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
			$.ajax( {
				url: shipping_nova_poshta_for_woocommerce.url,
				type: 'POST',
				data: {
					'nonce': shipping_nova_poshta_for_woocommerce.nonce,
					'action': 'shipping_nova_poshta_for_woocommerce_warehouse',
					'city': $( '#shipping_nova_poshta_for_woocommerce_city' ).val(),
				},
				beforeSend: function() {
					$( '#shipping_nova_poshta_for_woocommerce_warehouse' ).addClass( 'inactive' );
				},
				success: function( data ) {
					let select = $( '#shipping_nova_poshta_for_woocommerce_warehouse' );
					select.find( 'option' ).remove();
					data.forEach( function( element ) {
						select.append( new Option( element.text, element.id, false, false ) );
					} );
					select.trigger( 'change' );
				},
				complete: function() {
					$( '#shipping_nova_poshta_for_woocommerce_warehouse' ).removeClass( 'inactive' );
				},
			} );
			$.ajax( {
				url: shipping_nova_poshta_for_woocommerce.url,
				type: 'POST',
				data: {
					'nonce': shipping_nova_poshta_for_woocommerce.nonce,
					'action': 'shipping_nova_poshta_for_woocommerce_shipping_cost',
					'city': $( '#shipping_nova_poshta_for_woocommerce_city' ).val(),
				},
				success: function( data ) {
					var price = $( 'input[value=shipping_nova_poshta_for_woocommerce]' ).parent().find( '.woocommerce-Price-amount' );
					price.replaceWith( data );
				},
			} )
		} );
		$( '#shipping_nova_poshta_for_woocommerce_warehouse:not(.select2-hidden-accessible)' ).np_select2( {
			language: shipping_nova_poshta_for_woocommerce.language,
		} );
	}

	$( function() {
		if ( $( '#shipping_nova_poshta_for_woocommerce_city, #shipping_nova_poshta_for_woocommerce_warehouse' ).length ) {
			init();
		}
	} );

	const getQueryParams = ( params, url ) => {
		let href = url;
		let reg = new RegExp( '[?&]' + params + '=([^&#]*)', 'i' );
		let queryString = reg.exec( href );
		return queryString ? queryString[ 1 ] : '';
	};

	$( document ).ajaxComplete( function( event, xhr, settings ) {
		if ( 'update_order_review' === getQueryParams( 'wc-ajax', settings.url ) ) {
			init();
		}
	} );
} )( jQuery );
