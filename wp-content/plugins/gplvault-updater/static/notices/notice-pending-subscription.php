<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$gplvault_link = '<a href="https://www.gplvault.com/my-account" target="_blank">GPL Vault</a>';
$plugin_name   = '<strong>GPLVault Update Manager</strong>';
?>
<div id="gv_subcription_message" class="notice notice-warning">
	<p><strong><?php esc_html_e( 'Your subscription is Pending Cancellation', 'gplvault' ); ?></strong>
		<?php /* translators: 1: title of the plugin 2: link for purchasing new license */ ?>
		<br><?php echo wp_kses_post( sprintf( __( 'Your subscription is awaiting cancellation. To continue using the %1$s, please reactivate your subscription at %2$s.', 'gplvault' ), $plugin_name, $gplvault_link ) ); ?></p>
</div>
<?php
