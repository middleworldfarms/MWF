<?php
defined( 'ABSPATH' ) || exit;

/** @var GPLVault_Settings_Manager $settings_manager */
$api_settings         = $settings_manager->get_api_settings();
$blocked_plugins      = $settings_manager->blocked_plugins();
$blocked_themes       = $settings_manager->blocked_themes();
$is_license_activated = $settings_manager->license_is_activated();
$gv_license_key       = $api_settings[ GPLVault_Settings_Manager::API_KEY ] ?? '';
$gv_product_id        = $api_settings[ GPLVault_Settings_Manager::PRODUCT_KEY ] ?? '';
$license_status_class = $is_license_activated ? 'gv-status__success' : 'gv-status__error';

$license_summary = $is_license_activated ? $settings_manager->license_status() : array();
?>
<style>
/* Material Design inspired styles for settings page */
:root {
	--gv-material-primary: #1e88e5;
	--gv-material-primary-dark: #1565c0;
	--gv-material-shadow: 0 2px 5px 0 rgba(0,0,0,0.16), 0 2px 10px 0 rgba(0,0,0,0.12);
	--gv-material-radius: 8px;
}

.gv-material-card {
	background: #fff;
	border-radius: var(--gv-material-radius);
	box-shadow: var(--gv-material-shadow);
	padding: 32px;
	margin-bottom: 32px;
	transition: box-shadow 0.3s ease;
	border-top: 4px solid var(--gv-material-primary);
}

.gv-material-card:hover {
	box-shadow: 0 5px 11px 0 rgba(0,0,0,0.18), 0 4px 15px 0 rgba(0,0,0,0.15);
}

.gv-material-header {
	margin-bottom: 32px;
}

.gv-material-title {
	font-size: 24px;
	font-weight: 400;
	color: #333;
	margin: 0 0 12px 0;
}

.gv-material-description {
	font-size: 16px;
	color: #666;
	line-height: 1.6;
}

/* Notification container */
#gv_popups {
	position: fixed;
	top: 32px;
	right: 20px;
	z-index: 9999;
}

/* Material Design styles for license status */
.gv-material-status-section {
	background: #f5f5f5;
	border-radius: var(--gv-material-radius);
	padding: 24px;
	margin-bottom: 32px;
}

/* Progress bar styles */
.gv-material-progress-container {
	background: #fff;
	border-radius: 8px;
	padding: 20px;
	margin-bottom: 20px;
	box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.gv-material-progress-info {
	display: flex;
	justify-content: space-between;
	align-items: center;
	margin-bottom: 12px;
}

.gv-material-progress-text {
	font-size: 14px;
	color: #666;
}

.gv-material-progress-percentage {
	font-size: 14px;
	font-weight: 600;
	color: #333;
}

.gv-material-progress-bar {
	width: 100%;
	height: 8px;
	background: #e0e0e0;
	border-radius: 4px;
	overflow: hidden;
	position: relative;
}

.gv-material-progress-fill {
	height: 100%;
	background: #4caf50;
	border-radius: 4px;
	transition: width 0.3s ease;
}

.gv-material-status-grid {
	display: grid;
	grid-template-columns: repeat(3, 1fr);
	gap: 20px;
}

.gv-material-status-item {
	background: #fff;
	border-radius: 8px;
	padding: 24px 20px;
	text-align: center;
	box-shadow: 0 1px 3px rgba(0,0,0,0.1);
	display: flex;
	flex-direction: column;
	justify-content: center;
	min-height: 120px;
}

/* Color coding for different metrics */
.gv-material-status-item.total {
	border-top: 3px solid #2196f3;
}

.gv-material-status-item.used {
	border-top: 3px solid #ff9800;
}

.gv-material-status-item.remaining {
	border-top: 3px solid #4caf50;
}

.gv-material-status-label {
	font-size: 13px;
	color: #666;
	text-transform: uppercase;
	letter-spacing: 0.5px;
	margin-bottom: 12px;
	font-weight: 500;
}

.gv-material-status-value {
	font-size: 36px;
	font-weight: 600;
	color: #333;
	line-height: 1;
}

.gv-material-status-value.status-active {
	color: #2e7d32;
}

.gv-material-status-value.status-inactive {
	color: #c62828;
}

.gv-material-status-icon {
	display: inline-block;
	width: 48px;
	height: 48px;
	border-radius: 50%;
	margin-bottom: 12px;
	display: flex;
	align-items: center;
	justify-content: center;
}

.gv-material-status-icon.active {
	background: #e8f5e9;
	color: #2e7d32;
}

.gv-material-status-icon.inactive {
	background: #ffebee;
	color: #c62828;
}

.gv-material-status-icon .dashicons {
	font-size: 24px;
	width: 24px;
	height: 24px;
}

/* Responsive adjustments */
@media (max-width: 1024px) {
	.gv-material-status-grid {
		grid-template-columns: repeat(3, 1fr);
		gap: 16px;
	}
}

@media (max-width: 782px) {
	.gv-material-status-grid {
		grid-template-columns: 1fr;
		gap: 16px;
	}
	
	.gv-material-status-item {
		padding: 20px;
		min-height: auto;
	}
	
	.gv-material-status-value {
		font-size: 32px;
	}
}
</style>

<div class="wrap gv-wrapper gv-wrapper-settings" id="gv_settings_wrapper" style="max-width:1400px;margin: 0 auto 40px auto;">
	<!-- Notification container -->
	<div id="gv_popups"></div>
	
	<div class="gv-material-card">
		<div class="gv-material-header">
			<h1 class="gv-material-title">
				<span class="dashicons dashicons-admin-generic" style="margin-right: 12px; color: var(--gv-material-primary);"></span>
				<?php esc_html_e( 'GPLVault Settings', 'gplvault' ); ?>
			</h1>
			<p class="gv-material-description">
				<?php esc_html_e( 'Activate your GPLVault license to receive automatic updates for premium WordPress plugins and themes. Monitor your activation quota and manage your license settings.', 'gplvault' ); ?>
			</p>
		</div>

		<div id="gv_license_status_wrapper">
			<?php if ( $is_license_activated && ! empty( $license_summary ) ) : ?>
				<?php GPLVault_Admin::load_partial( 'settings/status', compact( 'license_summary' ) ); ?>
			<?php endif; ?>
		</div>
		
		<?php
			GPLVault_Admin::load_partial(
				'settings/license',
				compact( 'is_license_activated', 'license_status_class', 'gv_license_key', 'gv_product_id' )
			);
			?>
	</div>
</div> <!-- .wrap -->
