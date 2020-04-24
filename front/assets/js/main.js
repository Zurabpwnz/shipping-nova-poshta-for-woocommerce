jQuery(document).ready(function ($) {
    function init() {
        $('#shipping_nova_poshta_for_woocommerce_city').select2({
            minimumInputLength: 1,
            ajax: {
                url: shipping_nova_poshta_for_woocommerce.url,
                type: 'POST',
                data: function (params) {
                    return {
                        'nonce': shipping_nova_poshta_for_woocommerce.nonce,
                        'action': 'shipping_nova_poshta_for_woocommerce_city',
                        'search': params.term,
                    };
                },
                processResults: function (data) {
                    return {
                        results: data
                    };
                }
            }
        });
        $('#shipping_nova_poshta_for_woocommerce_city').on('select2:select', function (e) {
            $.ajax({
                url: shipping_nova_poshta_for_woocommerce.url,
                type: 'POST',
                data: {
                    'nonce': shipping_nova_poshta_for_woocommerce.nonce,
                    'action': 'shipping_nova_poshta_for_woocommerce_warehouse',
                    'city': $('#shipping_nova_poshta_for_woocommerce_city').val(),
                },
                success: function (data) {
                    let select = $('#shipping_nova_poshta_for_woocommerce_warehouse');
                    select.find('option').remove();
                    data.forEach(function (element) {
                        select.append(new Option(element.text, element.id, false, false));
                    });
                    select.trigger('change');
                }
            });
        });
        $('#shipping_nova_poshta_for_woocommerce_warehouse').select2();
    }

    if ( $('#shipping_nova_poshta_for_woocommerce_city, #shipping_nova_poshta_for_woocommerce_warehouse').length ) {
        init();
    }

    $(document).ajaxComplete(function (event, xhr, settings) {
        if ('/?wc-ajax=update_order_review' === settings.url) {
            init();
        }
    });
});
