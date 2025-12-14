<?php
defined( 'ABSPATH' ) || exit;
/**
 * @var string $settings_url
 */
?>
<div class="gv-material-activation-card">
	<div class="gv-material-activation-icon">
		<span class="dashicons dashicons-lock"></span>
	</div>
	<h3 class="gv-material-activation-title"><?php esc_html_e( 'Please activate your license to continue.', 'gplvault' ); ?></h3>
	<a class="gv-material-button gv-material-button-primary" href="<?php echo esc_url( $settings_url ); ?>">
		<span class="dashicons dashicons-admin-generic"></span>
		<?php esc_html_e( 'Go to Settings', 'gplvault' ); ?>
	</a>
</div>
