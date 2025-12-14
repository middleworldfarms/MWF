<?php
defined( 'ABSPATH' ) || exit;
?>

<script type="text/template" id="tmpl-gv-templates-api-header">
	<div class="gv-card__header-label {{ data.activated ? 'gv-status__success' : 'gv-status__error' }}">
	<# if (data.activated) { #>
	<span class="dashicons dashicons-yes"></span>
	<span class="gv-label__text"><?php esc_html_e( 'Activated', 'gplvault' ); ?></span>
	<# } else { #>
	<span class="dashicons dashicons-no"></span>
	<span class="gv-label__text"><?php esc_html_e( 'Deactivated', 'gplvault' ); ?></span>
	<# } #>
	</div>
</script>

<script type="text/template" id="tmpl-gv-templates-api-form">
	<div class="gv-fields__container">
		<div class="gv-fields__item">
			<div
				class="gv-fields__label"><span><?php esc_html_e( 'User License Key', 'gplvault' ); ?></span><span class="gv-help-tip gv-has-tooltip"
																											data-tippy-placement="top-start"
																											data-tippy-content="<?php esc_attr_e( 'Enter the User License Key found on GPLVault account section.', 'gplvault' ); ?>"></span></div>
			<div class="gv-fields__field gv-input__field gv-input__pwd">
				<input class="gv-input" type="text" id="api_master_key" name="api_master_key" placeholder="<?php esc_attr_e( 'Enter user license key', 'gplvault' ); ?>" />
			</div>
		</div>
		<div class="gv-fields__item">
			<div
				class="gv-fields__label"><span><?php esc_html_e( 'Product ID', 'gplvault' ); ?></span><span class="gv-help-tip gv-has-tooltip"
																											data-tippy-placement="top-start"
																											data-tippy-content="<?php esc_attr_e( 'Enter the Product ID of your purchased subscription on the server.', 'gplvault' ); ?>"></span></div>
			<div class="gv-fields__field gv-input__field">
				<input class="gv-input" type="text" id="api_product_id" name="api_product_id" placeholder="<?php esc_attr_e( 'Enter product id', 'gplvault' ); ?>" />
			</div>
		</div>
		<div class="gv-fields__actions">
			<button
				type="button"
				id="gv_activate_api"
				class="button button-primary"
				data-context="license_activation"
			><?php esc_html_e( 'Activate', 'gplvault' ); ?></button>
		</div>
	</div>
</script>

<script type="text/template" id="tmpl-gv-templates-key-info">
	<div class="gv-fields__container">
		<div class="gv-fields__item">
			<div
				class="gv-fields__label"><span><?php esc_html_e( 'User License Key', 'gplvault' ); ?></span>
			</div>
			<div class="gv-fields__field gv-input__field gv-input__pwd">
				<h3 style="letter-spacing: 2px; margin-top: 0.25em;">{{ data.api_key }}</h3>
			</div>
		</div>
		<div class="gv-fields__item">
			<div
				class="gv-fields__label"><span><?php esc_html_e( 'Product ID', 'gplvault' ); ?>{{ data.master_key }}</span>
			</div>
			<div class="gv-fields__field gv-input__field">
				<h3 style="margin-top: 0.25em;">{{ data.product_id }}</h3>
			</div>
		</div>
	</div>
</script>

<script type="text/template" id="tmpl-gv-templates-status">
	<div class="gv-admin-section" id="gv_license_status_section">
		<div class="gv-section-header">
			<h2 class="gv-section-header__title"><?php esc_html_e( 'License Status', 'gplvault' ); ?></h2>
			<hr role="presentation">
		</div>
		<div class="gv-summary">
			<div class="gv-summary__item-container">
				<div class="gv-summary__item">
					<div class="gv-summary__item-label">
						<p><?php esc_html_e( 'Status', 'gplvault' ); ?></p>
					</div>
					<div class="gv-summary__item-data">
						<div class="gv-summary__item-value">
							{{ data.activated ? 'Activated' : 'Deactivated' }}
						</div>
					</div>
				</div>
			</div>
			<div class="gv-summary__item-container">
				<div class="gv-summary__item">
					<div class="gv-summary__item-label">
						<p><?php esc_html_e( 'Total Quota', 'gplvault' ); ?></p>
					</div>
					<div class="gv-summary__item-data">
						<div class="gv-summary__item-value">
							{{ data.total_activations_purchased }}
						</div>
					</div>
				</div>
			</div>
			<div class="gv-summary__item-container">
				<div class="gv-summary__item">
					<div class="gv-summary__item-label">
						<p><?php esc_html_e( 'Already Activated', 'gplvault' ); ?></p>
					</div>
					<div class="gv-summary__item-data">
						<div class="gv-summary__item-value">
							{{ data.total_activations }}
						</div>
					</div>
				</div>
			</div>
			<div class="gv-summary__item-container">
				<div class="gv-summary__item">
					<div class="gv-summary__item-label">
						<p><?php esc_html_e( 'Remaining', 'gplvault' ); ?></p>
					</div>
					<div class="gv-summary__item-data">
						<div class="gv-summary__item-value">
							{{ data.activations_remaining }}
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</script>
<?php
