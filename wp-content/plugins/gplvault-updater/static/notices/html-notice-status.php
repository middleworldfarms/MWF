<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div id="gv_activation_message" class="notice notice-error">
	<p><strong><?php esc_html_e( 'Attention!', 'gplvault' ); ?></strong>
		&#8211; <?php echo wp_kses_post( __( 'Your license appears to be inactive or could not be verified by our server. Please check your <strong><a href="https://gplvault.com/my-account" target="_blank" title="GPLVault Account">Account page</a></strong> or contact an administrator for assistance.', 'gplvault' ) ); ?></p>
</div>
