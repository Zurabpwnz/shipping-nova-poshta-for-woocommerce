<?php
/**
 * Page options
 *
 * @package Nova_Posta\Admin\Partials
 */

use Nova_Poshta\Core\Main;

$url        = get_admin_url( null, 'admin.php?page=' . Main::PLUGIN_SLUG );
$active_tab = filter_input( INPUT_GET, 'tab', FILTER_SANITIZE_STRING );
?>
<div class="wrap">
	<div class="nav-tab-wrapper" method="GET">
		<a
				href="<?php echo esc_url( $url ); ?>"
				class="nav-tab<?php echo ! $active_tab ? ' nav-tab-active' : ''; ?>">
			<?php esc_attr_e( 'General', 'woo-nova-poshta' ); ?>
		</a>
		<a
				href="<?php echo esc_url( $url . '&tab=internet_document' ); ?>"
				class="nav-tab<?php echo 'internet_document' === $active_tab ? ' nav-tab-active' : ''; ?>">
			<?php esc_attr_e( 'Create invoice', 'woo-nova-poshta' ); ?>
		</a>
	</div>
	<?php
	if ( empty( $active_tab ) ) {
		require plugin_dir_path( __FILE__ ) . 'general.php';
	} elseif ( 'internet_document' === $active_tab ) {
		require plugin_dir_path( __FILE__ ) . 'internet-document.php';
	}
	?>
</div>
