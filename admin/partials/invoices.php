<?php

global $wpdb;

$orders = $wpdb->get_results(
	"SELECT p.ID, p.post_title, pm.meta_value, woi.order_item_name
FROM wp_posts as p
         LEFT JOIN wp_postmeta as pm ON p.ID = pm.post_id
         LEFT JOIN wp_woocommerce_order_items as woi ON p.ID = woi.order_id
WHERE p.post_type = 'shop_order'
  AND p.post_status = 'wc-on-hold'
  AND pm.meta_key = 'woo_nova_poshta_internet_document'
  AND woi.order_item_type = 'line_item'"
);

if ( $orders ) {
	$krya = [];
	foreach ( $orders as $key => $order ) {
		$krya[ $order->ID ] = [
			'name'    => $order->post_title,
			'invoice' => $order->meta_value,
		];
		$krya[ $order->ID ]['items'][]    = $order->order_item_name;
	}
	$orders = $krya;
	?>
	<table>
		<thead>
		<tr>
			<th>Заказ</th>
			<th>Товары</th>
			<th>Накладная</th>
		</tr>
		</thead>
		<tbody>
		<?php foreach ( $orders as $order_id => $order ) { ?>
			<tr>
				<td>
					<a href="<?php echo esc_url( get_edit_post_link( $order_id ) ); ?>">
						<?php echo esc_attr( $order['name'] ); ?>
					</a>
				</td>
				<td>
					<?php foreach ( $order['items'] as $item ) {
						echo $item . '<br>';
					} ?>
				</td>
				<td>
					<?php echo esc_attr( $order['invoice'] ); ?>
				</td>
			</tr>
		<?php } ?>
		</tbody>
	</table>
	<?php
}

//      new WC_Order( ID )->update_status( '')