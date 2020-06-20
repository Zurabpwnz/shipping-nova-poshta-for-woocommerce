<?php
/**
 * Notice view
 *
 * @package Nova_Posta\Admin\Partials
 * @var $type
 * @var $message
 * @var $btn_label
 * @var $btn_url
 */

if ( $btn_label && $btn_url ) {
	$message = sprintf(
		'<p>' . $message . '<a href="%s" target="_blank" class="button button-primary">%s</a></p>',
		esc_url( $btn_url ),
		esc_html( $btn_label )
	);
} else {
	$message = sprintf( '<p>%s</p>', esc_html( $message ) );
}
?>
<div class="shipping-nova-poshta-for-woocommerce-notice notice notice-<?php echo esc_attr( $type ); ?> is-dismissible">
	<?php
	echo wp_kses(
		$message,
		[
			'p'      => [],
			'a'      => [
				'href'   => true,
				'class'  => true,
				'target' => true,
			],
			'strong' => [],
			'span'   => [
				'class' => true,
			],
		]
	);
	?>
</div>
