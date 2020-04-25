<?php
/**
 * Notice view
 *
 * @package Nova_Posta\Admin\Partials
 * @var $type
 * @var $message
 */

?>
<div class="notice notice-<?php echo esc_attr( $type ); ?>">
	<p>
		<?php
		echo wp_kses(
			$message,
			[
				'a'      => [ 'href' => true ],
				'strong' => [],
			]
		);
		?>
	</p>
</div>
