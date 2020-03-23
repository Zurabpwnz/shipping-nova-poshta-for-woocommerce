<?php

namespace Nova_Poshta\Core;

use WC_Order;

class Thank_You {

	private $api;

	public function __construct( API $api ) {
		$this->api = $api;
	}

	public function shipping( array $total_rows, WC_Order $order ) {
		$shipping_methods = $order->get_shipping_methods();
		if ( ! empty( $shipping_methods ) ) {
			$shipping_method = array_shift( $shipping_methods );
			if ( 'woo_nova_poshta' === $shipping_method->get_method_id() ) {
				$city      = $shipping_method->get_meta( 'city' );
				$warehouse = $shipping_method->get_meta( 'warehouse' );
				if ( ! $city || ! $warehouse ) {
					return $total_rows;
				}
				$city      = $this->api->city( $city );
				$warehouse = $this->api->warehouse( $warehouse );

				$total_rows['shipping']['value'] .= '<br>';
				$total_rows['shipping']['value'] .= $city . '<br>';
				$total_rows['shipping']['value'] .= $warehouse;

				$internet_document = $shipping_method->get_meta( 'internet_document' );
				if ( ! $internet_document ) {
					return $total_rows;
				}
				$total_rows['shipping']['value'] .= '<br>' . $internet_document;
			}
		}

		return $total_rows;
	}

}
