(function ($) {
	function init() {
		$('#shipping_nova_poshta_for_woocommerce_city').select2({
			language: shipping_nova_poshta_for_woocommerce.language,
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
				beforeSend: function () {
					$('#shipping_nova_poshta_for_woocommerce_warehouse').addClass('inactive');
				},
				success: function (data) {
					let select = $('#shipping_nova_poshta_for_woocommerce_warehouse');
					select.find('option').remove();
					data.forEach(function (element) {
						select.append(new Option(element.text, element.id, false, false));
					});
					select.trigger('change');
				},
				complete: function () {
					$('#shipping_nova_poshta_for_woocommerce_warehouse').removeClass('inactive');
				}
			});
		});
		$('#shipping_nova_poshta_for_woocommerce_warehouse').select2({
			language: shipping_nova_poshta_for_woocommerce.language
		});
	}

	$(function () {
		if ($('#shipping_nova_poshta_for_woocommerce_city, #shipping_nova_poshta_for_woocommerce_warehouse').length) {
			init();
		}
	});
	$(document).ajaxComplete(function (event, xhr, settings) {
		if (settings.url.indexOf('?wc-ajax=update_order_review') + 1) {
			init();
		}
		if (settings.url.indexOf('?wc-ajax=update_shipping_method') + 1) {
			init();
		}
	});
	$(window).load(function () {
		init();
	});
})(jQuery);