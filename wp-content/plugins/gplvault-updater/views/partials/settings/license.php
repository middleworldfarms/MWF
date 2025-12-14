<?php
// phpcs:ignoreFile
defined( 'ABSPATH' ) || exit;
/**
 * @var string $license_status_class
 * @var bool $is_license_activated
 * @var string $gv_license_key
 * @var int $gv_product_id
 */
?>
<style>
/* Material Design styles for license section */
.gv-material-grid {
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
	gap: 24px;
	margin-top: 24px;
}

.gv-material-column-card {
	background: #fff;
	border: 1px solid #e0e0e0;
	border-radius: var(--gv-material-radius);
	padding: 24px;
	transition: all 0.3s ease;
}

.gv-material-column-card:hover {
	box-shadow: 0 4px 8px rgba(0,0,0,0.1);
	transform: translateY(-2px);
}

.gv-material-column-header {
	display: flex;
	align-items: center;
	justify-content: space-between;
	margin-bottom: 20px;
	padding-bottom: 16px;
	border-bottom: 1px solid #e0e0e0;
}

.gv-material-column-title {
	font-size: 18px;
	font-weight: 500;
	color: #333;
	margin: 0;
	display: flex;
	align-items: center;
}

.gv-material-column-title .dashicons {
	margin-right: 8px;
	color: var(--gv-material-primary);
}

.gv-material-status-badge {
	display: inline-flex;
	align-items: center;
	padding: 4px 12px;
	border-radius: 16px;
	font-size: 13px;
	font-weight: 500;
}

.gv-material-status-badge.active {
	background: #e8f5e9;
	color: #2e7d32;
}

.gv-material-status-badge.inactive {
	background: #ffebee;
	color: #c62828;
}

.gv-material-status-badge .dashicons {
	font-size: 16px;
	width: 16px;
	height: 16px;
	margin-right: 4px;
}

.gv-material-field-label {
	display: flex;
	align-items: center;
	font-size: 14px;
	font-weight: 500;
	color: #555;
	margin-bottom: 8px;
}

.gv-material-field-label span:first-child {
	margin-right: 8px;
}

.gv-material-help-tip {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	width: 18px;
	height: 18px;
	border-radius: 50%;
	background: #f0f0f0;
	color: #666;
	font-size: 12px;
	cursor: help;
	transition: all 0.2s ease;
}

.gv-material-help-tip:hover {
	background: var(--gv-material-primary);
	color: white;
}

.gv-material-help-tip::before {
	content: '?';
	font-weight: bold;
}

.gv-material-input {
	width: 100%;
	padding: 12px 16px;
	border: 2px solid #ddd;
	border-radius: 4px;
	font-size: 15px;
	transition: all 0.3s ease;
	background: #fff;
}

.gv-material-input:focus {
	outline: none;
	border-color: var(--gv-material-primary);
	box-shadow: 0 0 0 3px rgba(30, 136, 229, 0.1);
}

.gv-material-input::placeholder {
	color: #999;
}

.gv-material-field {
	margin-bottom: 20px;
}

.gv-material-button {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	height: 44px;
	padding: 0 24px;
	border-radius: 4px;
	font-weight: 500;
	font-size: 14px;
	text-decoration: none;
	cursor: pointer;
	transition: all 0.2s ease;
	box-shadow: 0 1px 3px rgba(0,0,0,0.12), 0 1px 2px rgba(0,0,0,0.24);
	border: none;
}

.gv-material-button:disabled {
	opacity: 0.6;
	cursor: not-allowed;
}

.gv-material-button .dashicons {
	margin-right: 8px;
}

.gv-material-button-primary {
	background-color: var(--gv-material-primary);
	color: white;
}

.gv-material-button-primary:hover:not(:disabled), 
.gv-material-button-primary:focus:not(:disabled) {
	background-color: var(--gv-material-primary-dark);
	color: white;
	box-shadow: 0 2px 4px rgba(0,0,0,0.2), 0 3px 6px rgba(0,0,0,0.1);
}

.gv-material-button-danger {
	background-color: #f44336;
	color: white;
}

.gv-material-button-danger:hover:not(:disabled),
.gv-material-button-danger:focus:not(:disabled) {
	background-color: #d32f2f;
	color: white;
	box-shadow: 0 2px 4px rgba(0,0,0,0.2), 0 3px 6px rgba(0,0,0,0.1);
}

.gv-material-button-outline {
	background-color: #fff;
	color: #666;
	border: 2px solid #ddd;
}

.gv-material-button-outline:hover:not(:disabled),
.gv-material-button-outline:focus:not(:disabled) {
	background-color: #f5f5f5;
	color: #333;
	border-color: #999;
	box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.gv-material-actions {
	margin-top: 24px;
}

.gv-material-button-group {
	display: flex;
	flex-direction: column;
	gap: 12px;
}

.gv-material-value {
	font-size: 18px;
	font-weight: 500;
	color: #333;
	letter-spacing: 1px;
	margin: 8px 0;
}

/* Loading state for buttons */
.gv-material-button.updating-message {
	position: relative;
	color: transparent;
}

.gv-material-button.updating-message::after {
	content: '';
	position: absolute;
	width: 16px;
	height: 16px;
	top: 50%;
	left: 50%;
	margin-left: -8px;
	margin-top: -8px;
	border: 2px solid #ffffff;
	border-radius: 50%;
	border-top-color: transparent;
	animation: gv-spinner 0.8s linear infinite;
}

@keyframes gv-spinner {
	to { transform: rotate(360deg); }
}

/* Responsive adjustments */
@media (max-width: 782px) {
	.gv-material-grid {
		grid-template-columns: 1fr;
	}
	
	.gv-material-button {
		width: 100%;
	}
}
</style>

<div class="gv-material-section" id="api_settings_section">
	<h2 class="gv-material-section-title" style="font-size: 20px; font-weight: 500; color: #333; margin: 32px 0 24px 0;">
		<?php esc_html_e( 'License Activation', 'gplvault' ); ?>
	</h2>
	
	<div class="gv-material-grid">
		<div class="gv-material-column-card" id="api_settings_column">
			<div class="gv-material-column-header">
				<h3 class="gv-material-column-title">
					<span class="dashicons dashicons-admin-network"></span>
					<?php esc_html_e( 'API Settings', 'gplvault' ); ?>
				</h3>
				<div class="gv-material-status-badge <?php echo $is_license_activated ? 'active' : 'inactive'; ?>">
					<?php if ( $is_license_activated ) : ?>
						<span class="dashicons dashicons-yes-alt"></span>
						<?php esc_html_e( 'Activated', 'gplvault' ); ?>
					<?php else : ?>
						<span class="dashicons dashicons-dismiss"></span>
						<?php esc_html_e( 'Deactivated', 'gplvault' ); ?>
					<?php endif; ?>
				</div>
			</div>
			
			<div class="gv-material-column-body">
				<?php if (! $is_license_activated) : ?>
					<div class="gv-material-field">
						<div class="gv-material-field-label">
							<span><?php esc_html_e( 'User License Key', 'gplvault' ); ?></span>
							<span class="gv-material-help-tip gv-has-tooltip"
								  data-tippy-placement="top-start"
								  data-tippy-content="<?php esc_attr_e( 'Your User License Key can be found in the License Keys section of your GPLVault account', 'gplvault' ); ?>"></span>
						</div>
						<input class="gv-material-input" type="text" id="api_master_key" name="api_master_key" placeholder="<?php esc_attr_e( 'Enter user license key', 'gplvault' ); ?>" />
					</div>
					
					<div class="gv-material-field">
						<div class="gv-material-field-label">
							<span><?php esc_html_e( 'Product ID', 'gplvault' ); ?></span>
							<span class="gv-material-help-tip gv-has-tooltip"
								  data-tippy-placement="top-start"
								  data-tippy-content="<?php esc_attr_e( 'Your Product ID can be found in the License Keys section of your GPLVault account', 'gplvault' ); ?>"></span>
						</div>
						<input class="gv-material-input" type="text" id="api_product_id" name="api_product_id" placeholder="<?php esc_attr_e( 'Enter product id', 'gplvault' ); ?>" />
					</div>
					
					<div class="gv-material-actions">
						<button
							type="button"
							id="gv_activate_api"
							class="gv-material-button gv-material-button-primary"
							data-context="license_activation"
						>
							<span class="dashicons dashicons-admin-network"></span>
							<?php esc_html_e( 'Activate License', 'gplvault' ); ?>
						</button>
					</div>
				<?php else: ?>
					<div class="gv-material-field">
						<div class="gv-material-field-label">
							<span><?php esc_html_e( 'User License Key', 'gplvault' ); ?></span>
						</div>
						<div class="gv-material-value"><?php echo esc_html( gv_mask_str($gv_license_key) ); ?></div>
					</div>
					
					<div class="gv-material-field">
						<div class="gv-material-field-label">
							<span><?php esc_html_e( 'Product ID', 'gplvault' ); ?></span>
						</div>
						<div class="gv-material-value"><?php echo esc_html( $gv_product_id ); ?></div>
					</div>
				<?php endif; ?>
			</div>
		</div>
		
		<div class="gv-material-column-card">
			<div class="gv-material-column-header">
				<h3 class="gv-material-column-title">
					<span class="dashicons dashicons-admin-tools"></span>
					<?php esc_html_e( 'License Actions', 'gplvault' ); ?>
				</h3>
			</div>
			
			<div class="gv-material-column-body">
				<div class="gv-material-button-group">
					<button
						class="gv-material-button gv-material-button-danger gv-has-confirmation gv-has-tooltip"
						data-confirmation="<?php esc_attr_e( 'Do you really want to deactivate the license?', 'gplvault' ); ?>"
						data-tippy-content="<?php esc_attr_e( 'Remove this license from the current site', 'gplvault' ); ?>"
						type="button"
						id="license_deactivation"
						data-context="license_deactivation"
						<?php echo gv_settings_manager()->get_activation_status() ? '' : 'disabled'; ?>
					>
						<span class="dashicons dashicons-dismiss"></span>
						<?php esc_html_e( 'Deactivate License', 'gplvault' ); ?>
					</button>

					<button
						class="gv-material-button gv-material-button-primary gv-has-tooltip"
						data-tippy-content="<?php esc_attr_e( 'Sync license status with GPLVault server', 'gplvault' ); ?>"
						type="button"
						id="check_license"
						data-context="check_license"
						<?php echo gv_settings_manager()->get_activation_status() ? '' : 'disabled'; ?>
					>
						<span class="dashicons dashicons-update"></span>
						<?php esc_html_e( 'Check License', 'gplvault' ); ?>
					</button>

					<button
						class="gv-material-button gv-material-button-outline gv-has-confirmation gv-has-tooltip"
						data-confirmation="<?php esc_attr_e( 'Do you really want to delete local license settings?', 'gplvault' ); ?>"
						data-tippy-content="<?php esc_attr_e( 'Force remove all local license data (use only if deactivation fails)', 'gplvault' ); ?>"
						type="button"
						id="cleanup_settings"
						<?php echo gv_settings_manager()->get_activation_status() ? '' : 'disabled'; ?>
						data-context="cleanup_settings"
					>
						<span class="dashicons dashicons-trash"></span>
						<?php esc_html_e( 'Clear Local Settings', 'gplvault' ); ?>
					</button>
				</div>
			</div>
		</div>
	</div>
</div>
