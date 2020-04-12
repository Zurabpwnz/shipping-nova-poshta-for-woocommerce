jQuery(document).ready(function ($) {
    function update_warehouses(active = null) {
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
                    select.append(new Option(element.text, element.id, false, active === element.id));
                });
                select.trigger('change');
            }
        });
    }

    function init() {
        if (!$('#woo_nova_poshta_city, #woo_nova_poshta_warehouse').length) {
            return;
        }
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
            update_warehouses();
        });
        $('#woo_nova_poshta_warehouse').select2();
    }


    const getQueryParams = (params, url) => {
        let href = url;
        let reg = new RegExp('[?&]' + params + '=([^&#]*)', 'i');
        let queryString = reg.exec(href);
        return queryString ? queryString[1] : null;
    };

    $(document).ajaxComplete(function (event, xhr, settings) {
        if ('woocommerce_save_order_items' === getQueryParams('action', settings.data)) {
            update_method_classes();
        }
    });

    update_method_classes();
    init();

    $('#woocommerce-order-items').on('click', '#order_shipping_line_items .shipping_method', function () {
        update_method_classes();
    });

    function update_method_classes() {
        let method_field = $('#order_shipping_line_items .shipping_method');
        if (method_field.length && 'woo_nova_poshta' === method_field.val()) {
            $('#order_shipping_line_items').addClass('woo-nova-poshta');
        } else {
            $('#order_shipping_line_items').removeClass('woo-nova-poshta');
        }

    }

    $('#woocommerce-order-items').on('click', '#order_shipping_line_items .edit-order-item', function () {
        replace_input_on_select('city_id', 'woo_nova_poshta_city');
        replace_input_on_select('warehouse_id', 'woo_nova_poshta_warehouse');
        init();
        update_warehouses($('#woo_nova_poshta_warehouse').val());

        function replace_input_on_select(key, id) {
            let id_key = $('#order_shipping_line_items input[value=' + key + ']');
            id_key.attr('type', 'hidden');
            let id_field = id_key.next('textarea');
            let index = id_field.closest('tr').index();
            let value = $('#order_shipping_line_items .display_meta tr:eq(' + index + ') td').text();
            let option = new Option(value, id_field.val());
            id_field.replaceWith(
                '<select name="' + id_field.attr('name') + '" id="' + id + '"></select>'
            );
            $('#' + id).append(option);
        }
    })
});