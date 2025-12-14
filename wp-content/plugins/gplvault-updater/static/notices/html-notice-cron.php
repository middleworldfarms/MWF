<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div id="gv_cron_message" class="notice notice-error">
	<p><strong><?php esc_html_e( 'WP CRON Deactivated!', 'gplvault' ); ?></strong>
		<?php /* translators: 1: constant definition to disable cron 2: value to activate cron */ ?>
		&#8211; <?php echo wp_kses_post( sprintf( __( 'GPLVault Updater depends on WP Cron to sync data. Please remove %1$s or set it to %2$s.', 'gplvault' ), "<code>define('DISABLE_WP_CRON', true)</code>", '<code>false</code>' ) ); ?></p>
</div>
<?php
