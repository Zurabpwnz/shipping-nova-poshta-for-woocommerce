/* global shipping_nova_poshta_for_woocommerce */

'use strict';

const NovaPoshtaNotices =
	window.NovaPoshtaNotices ||
	( function( document, window, $ ) {
		const app = {
			init() {
				$( '.shipping-nova-poshta-for-woocommerce-notice' ).show();
				$( document ).on( 'click', '.shipping-nova-poshta-for-woocommerce-notice .notice-dismiss', function() {
					app.close();
				} );
			},
			close() {
				$.ajax( {
					url: shipping_nova_poshta_for_woocommerce.url,
					type: 'POST',
					data: {
						nonce: shipping_nova_poshta_for_woocommerce.nonce,
						action: 'shipping_nova_poshta_for_woocommerce_notice',
					},
				} );
			},
		};

		return app;
	}( document, window, jQuery ) );

NovaPoshtaNotices.init();
