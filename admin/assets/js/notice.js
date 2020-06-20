jQuery(document).ready(function ($) {
    $('.shipping-nova-poshta-for-woocommerce-notice').show();
    $('.shipping-nova-poshta-for-woocommerce-notice .notice-dismiss').on('click', function () {
        $.ajax({
            url: shipping_nova_poshta_for_woocommerce.url,
            type: 'POST',
            data: {
                'nonce': shipping_nova_poshta_for_woocommerce.nonce,
                'action': 'shipping_nova_poshta_for_woocommerce_notice'
            }
        })
    });
});
