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
	<?php require plugin_dir_path( __FILE__ ) . 'general.php'; ?>
</div>
