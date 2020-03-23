jQuery(document).ready(function ($) {
    function init() {
        $('#woo_nova_poshta_city').select2({
            minimumInputLength: 1,
            ajax: {
                url: woo_nova_poshta.url,
                type: 'POST',
                data: function (params) {
                    return {
                        'nonce': woo_nova_poshta.nonce,
                        'action': 'woo_nova_poshta_city',
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
        $('#woo_nova_poshta_city').on('select2:select', function (e) {
            $.ajax({
                url: woo_nova_poshta.url,
                type: 'POST',
                data: {
                    'nonce': woo_nova_poshta.nonce,
                    'action': 'woo_nova_poshta_warehouse',
                    'city': $('#woo_nova_poshta_city').val(),
                },
                success: function (data) {
                    let select = $('#woo_nova_poshta_warehouse');
                    select.find('option').remove();
                    data.forEach(function (element) {
                        select.append(new Option(element.text, element.id, false, false));
                    });
                    select.trigger('change');
                }
            });
        });
        $('#woo_nova_poshta_warehouse').select2();
    }

    if ( $('#woo_nova_poshta_city, #woo_nova_poshta_warehouse').length ) {
        init();
    }

    $(document).ajaxComplete(function (event, xhr, settings) {
        if ('/?wc-ajax=update_order_review' === settings.url) {
            init();
        }
    });
});
