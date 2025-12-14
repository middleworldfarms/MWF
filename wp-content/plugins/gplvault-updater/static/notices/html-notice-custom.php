<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! empty( $notice ) && ! empty( $notice_html ) ) {
	?>
	<div id="<?php echo esc_attr( $notice ); ?>" class="updated gv-notice-custom">
		<a class="gv-notice-close notice-dismiss"
			href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'gv-hide-notice', $notice ), 'gv_hide_notices_nonce', '_gv_notice_nonce' ) ); ?>"><?php esc_html_e( 'Dismiss', 'gplvault' ); ?></a>
		<?php echo wp_kses_post( wpautop( $notice_html ) ); ?>
	</div>
	<?php
}
