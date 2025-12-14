<?php
defined( 'ABSPATH' ) || exit;

/** @var GPLVault_Settings_Manager $settings_manager */
//$api_settings         = $settings_manager->get_api_settings();
$blocked_plugins      = $settings_manager->blocked_plugins();
$blocked_themes       = $settings_manager->blocked_themes();
$is_license_activated = $settings_manager->license_is_activated();
$settings_url         = GPLVault_Admin::admin_links( 'settings' );
//$gv_license_key       = $api_settings[ GPLVault_Settings_Manager::API_KEY ] ?? '';
//$gv_product_id        = $api_settings[ GPLVault_Settings_Manager::PRODUCT_KEY ] ?? '';
//$license_status_class = $is_license_activated ? 'gv-status__success' : 'gv-status__error';

//$license_summary = $is_license_activated ? $settings_manager->license_status() : array();
?>
<style>
/* Material Design inspired styles for disable updates page */
:root {
	--gv-material-primary: #1e88e5;
	--gv-material-primary-dark: #1565c0;
	--gv-material-shadow: 0 2px 5px 0 rgba(0,0,0,0.16), 0 2px 10px 0 rgba(0,0,0,0.12);
	--gv-material-radius: 8px;
	--gv-checkbox-size: 20px;
	--gv-item-hover: #f5f5f5;
	--gv-item-selected: #e3f2fd;
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

.gv-material-field-label {
	display: flex;
	align-items: center;
	font-size: 14px;
	font-weight: 500;
	color: #555;
	margin-bottom: 12px;
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

.gv-material-select-wrapper {
	margin-bottom: 20px;
}

.gv-material-select-wrapper .select2-container {
	width: 100% !important;
}

.gv-material-select-wrapper .select2-container .select2-selection {
	min-height: 44px;
	border: 2px solid #ddd;
	border-radius: 4px;
	transition: all 0.3s ease;
	background-color: #fff;
	cursor: pointer;
	position: relative;
}

.gv-material-select-wrapper .select2-container .select2-selection:hover {
	border-color: var(--gv-material-primary);
	background-color: #f8f9fa;
}

.gv-material-select-wrapper .select2-container.select2-container--focus .select2-selection {
	border-color: var(--gv-material-primary);
	box-shadow: 0 0 0 2px rgba(30, 136, 229, 0.2);
	background-color: #fff;
}

.gv-material-select-wrapper .select2-container .select2-selection__rendered {
	padding: 8px 40px 8px 12px;
	line-height: 28px;
	color: #666;
}

.gv-material-select-wrapper .select2-container .select2-selection__arrow {
	height: 42px;
	right: 12px;
}

.gv-material-select-wrapper .select2-container .select2-selection__arrow b {
	border-color: var(--gv-material-primary) transparent transparent transparent;
	border-width: 6px 6px 0 6px;
	margin-left: -6px;
	margin-top: -3px;
}

.gv-material-select-wrapper .select2-container .select2-selection__placeholder {
	color: #999;
	font-style: italic;
}

/* Add a subtle animation on focus */
.gv-material-select-wrapper .select2-container .select2-selection {
	background-image: linear-gradient(to right, var(--gv-material-primary) 0%, var(--gv-material-primary) 100%);
	background-size: 0% 2px;
	background-repeat: no-repeat;
	background-position: left bottom;
	transition: background-size 0.3s ease;
}

.gv-material-select-wrapper .select2-container.select2-container--focus .select2-selection {
	background-size: 100% 2px;
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

.gv-material-activation-card {
	background: #f8f9fa;
	border: 2px dashed #ddd;
	border-radius: var(--gv-material-radius);
	padding: 48px;
	text-align: center;
}

.gv-material-activation-title {
	font-size: 20px;
	font-weight: 400;
	color: #333;
	margin: 0 0 24px 0;
}

.gv-material-activation-icon {
	font-size: 48px;
	color: #999;
	margin-bottom: 16px;
}

/* Responsive adjustments */
@media (max-width: 782px) {
	.gv-material-grid {
		grid-template-columns: 1fr;
	}
	
	.gv-material-card {
		padding: 24px 16px;
	}
	
	.gv-material-column-card {
		padding: 20px 16px;
	}
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

/* Checkbox list styles */
.gv-checkbox-list-container {
	background: #fff;
	border: 1px solid #e0e0e0;
	border-radius: 4px;
	overflow: hidden;
}

.gv-checkbox-list-header {
	background: #fafafa;
	border-bottom: 1px solid #e0e0e0;
	padding: 12px 16px;
	display: flex;
	justify-content: space-between;
	align-items: center;
	gap: 16px;
}

.gv-search-box {
	flex: 1;
	position: relative;
}

.gv-search-box input {
	width: 100%;
	padding: 8px 36px 8px 12px;
	border: 1px solid #ddd;
	border-radius: 4px;
	font-size: 14px;
	transition: all 0.2s ease;
}

.gv-search-box input:focus {
	outline: none;
	border-color: var(--gv-material-primary);
	box-shadow: 0 0 0 2px rgba(30, 136, 229, 0.2);
}

.gv-search-box .dashicons {
	position: absolute;
	right: 10px;
	top: 50%;
	transform: translateY(-50%);
	color: #999;
	pointer-events: none;
}

.gv-bulk-actions {
	display: flex;
	gap: 8px;
}

.gv-bulk-actions button {
	padding: 6px 12px;
	font-size: 13px;
	background: #fff;
	border: 1px solid #ddd;
	border-radius: 3px;
	cursor: pointer;
	transition: all 0.2s ease;
}

.gv-bulk-actions button:hover {
	background: var(--gv-material-primary);
	color: white;
	border-color: var(--gv-material-primary);
}

.gv-checkbox-list {
	max-height: 400px;
	overflow-y: auto;
	overflow-x: hidden;
}

.gv-checkbox-item {
	display: flex;
	align-items: center;
	padding: 12px 16px;
	border-bottom: 1px solid #f0f0f0;
	transition: all 0.2s ease;
	cursor: pointer;
	user-select: none;
}

.gv-checkbox-item:hover {
	background: var(--gv-item-hover);
}

.gv-checkbox-item.selected {
	background: var(--gv-item-selected);
}

.gv-checkbox-item:last-child {
	border-bottom: none;
}

.gv-checkbox-wrapper {
	position: relative;
	margin-right: 12px;
	display: inline-block;
	width: var(--gv-checkbox-size);
	height: var(--gv-checkbox-size);
}

.gv-checkbox-wrapper input[type="checkbox"] {
	position: absolute;
	opacity: 0;
	cursor: pointer;
	width: 100%;
	height: 100%;
	margin: 0;
	z-index: 1;
}

.gv-checkbox-custom {
	display: block;
	width: var(--gv-checkbox-size);
	height: var(--gv-checkbox-size);
	border: 2px solid #ddd;
	border-radius: 3px;
	transition: all 0.2s ease;
	position: relative;
	pointer-events: none;
}

.gv-checkbox-wrapper input[type="checkbox"]:checked ~ .gv-checkbox-custom {
	background: var(--gv-material-primary);
	border-color: var(--gv-material-primary);
}

.gv-checkbox-custom::after {
	content: '';
	position: absolute;
	display: none;
	left: 6px;
	top: 2px;
	width: 5px;
	height: 10px;
	border: solid white;
	border-width: 0 2px 2px 0;
	transform: rotate(45deg);
}

.gv-checkbox-wrapper input[type="checkbox"]:checked ~ .gv-checkbox-custom::after {
	display: block;
}

.gv-item-icon {
	width: 32px;
	height: 32px;
	margin-right: 12px;
	border-radius: 4px;
	overflow: hidden;
	flex-shrink: 0;
	display: flex;
	align-items: center;
	justify-content: center;
	background: #f0f0f0;
}

.gv-item-icon img {
	width: 100%;
	height: 100%;
	object-fit: cover;
}

.gv-item-icon .dashicons {
	font-size: 20px;
	width: 20px;
	height: 20px;
	color: #666;
}

.gv-item-details {
	flex: 1;
	min-width: 0;
}

.gv-item-name {
	font-weight: 500;
	color: #333;
	margin-bottom: 2px;
	white-space: nowrap;
	overflow: hidden;
	text-overflow: ellipsis;
}

.gv-item-meta {
	font-size: 12px;
	color: #666;
}

.gv-item-version {
	margin-left: auto;
	padding-left: 12px;
	font-size: 13px;
	color: #999;
	white-space: nowrap;
}

.gv-checkbox-list-footer {
	background: #fafafa;
	border-top: 1px solid #e0e0e0;
	padding: 12px 16px;
	font-size: 13px;
	color: #666;
	text-align: center;
}

.gv-no-results {
	padding: 40px;
	text-align: center;
	color: #999;
}

/* Smooth scrollbar styling */
.gv-checkbox-list::-webkit-scrollbar {
	width: 8px;
}

.gv-checkbox-list::-webkit-scrollbar-track {
	background: #f1f1f1;
}

.gv-checkbox-list::-webkit-scrollbar-thumb {
	background: #ccc;
	border-radius: 4px;
}

.gv-checkbox-list::-webkit-scrollbar-thumb:hover {
	background: #999;
}

/* Animation for filtering */
.gv-checkbox-item.gv-filtered-out {
	display: none;
}

.gv-checkbox-item.gv-filtering {
	animation: gv-fade-in 0.3s ease;
}

@keyframes gv-fade-in {
	from {
		opacity: 0;
		transform: translateY(-10px);
	}
	to {
		opacity: 1;
		transform: translateY(0);
	}
}
</style>

<script>
jQuery(document).ready(function($) {
	// Initialize checkbox list functionality
	function initCheckboxList(containerId, inputId) {
		const container = $('#' + containerId);
		const hiddenInput = $('#' + inputId);
		const searchInput = container.find('.gv-search-input');
		const selectAllBtn = container.find('.gv-select-all');
		const selectNoneBtn = container.find('.gv-select-none');
		const checkboxes = container.find('.gv-checkbox-input');
		const items = container.find('.gv-checkbox-item');
		const footer = container.find('.gv-checkbox-list-footer');
		const saveBtn = container.closest('.gv-fields__container').find('[data-context]');
		
		// Update selected count
		function updateCount() {
			const total = checkboxes.length;
			const checked = checkboxes.filter(':checked').length;
			footer.text(checked + ' of ' + total + ' selected');
			
			// Update hidden input with selected values
			const values = [];
			checkboxes.filter(':checked').each(function() {
				values.push($(this).val());
			});
			// Store as comma-separated string for the hidden input
			hiddenInput.val(values.join(',')).trigger('change');
			
			// Enable save button if there are changes
			const originalValues = hiddenInput.data('original-values') || [];
			const hasChanges = JSON.stringify(values.sort()) !== JSON.stringify(originalValues.sort());
			saveBtn.prop('disabled', !hasChanges);
		}
		
		// Store original values
		const initialValues = [];
		checkboxes.filter(':checked').each(function() {
			initialValues.push($(this).val());
		});
		hiddenInput.data('original-values', initialValues);
		
		// Checkbox change handler
		checkboxes.on('change', function() {
			const item = $(this).closest('.gv-checkbox-item');
			if ($(this).is(':checked')) {
				item.addClass('selected');
			} else {
				item.removeClass('selected');
			}
			updateCount();
		});
		
		// Click on item to toggle checkbox
		items.on('click', function(e) {
			// Don't do anything if clicking on the checkbox itself or its label
			if ($(e.target).is('input, label') || $(e.target).closest('label').length) {
				return;
			}
			
			const checkbox = $(this).find('.gv-checkbox-input');
			checkbox.prop('checked', !checkbox.prop('checked')).trigger('change');
		});
		
		// Select all
		selectAllBtn.on('click', function() {
			checkboxes.not('.gv-filtered-out').prop('checked', true).trigger('change');
		});
		
		// Select none
		selectNoneBtn.on('click', function() {
			checkboxes.not('.gv-filtered-out').prop('checked', false).trigger('change');
		});
		
		// Search functionality
		searchInput.on('input', function() {
			const searchTerm = $(this).val().toLowerCase();
			
			items.each(function() {
				const item = $(this);
				const text = item.find('.gv-item-name').text().toLowerCase();
				
				if (searchTerm === '' || text.includes(searchTerm)) {
					item.removeClass('gv-filtered-out').addClass('gv-filtering');
					setTimeout(() => item.removeClass('gv-filtering'), 300);
				} else {
					item.addClass('gv-filtered-out');
				}
			});
			
			// Update no results message
			const visibleItems = items.not('.gv-filtered-out');
			if (visibleItems.length === 0) {
				if (!container.find('.gv-no-results').length) {
					container.find('.gv-checkbox-list').append(
						'<div class="gv-no-results">' + 
						'<span class="dashicons dashicons-search"></span><br>' +
						'No items found matching "' + searchTerm + '"' +
						'</div>'
					);
				}
			} else {
				container.find('.gv-no-results').remove();
			}
		});
		
		// Initialize count
		updateCount();
		
		// Listen for successful save events to update original values
		$(document).on('gv-excluded-plugins gv-excluded-themes', function(e) {
			// After successful save, update original values
			const currentValues = [];
			container.find('.gv-checkbox-input').filter(':checked').each(function() {
				currentValues.push($(this).val());
			});
			hiddenInput.data('original-values', currentValues);
			updateCount();
		});
		
		// Also update after any successful AJAX call to the exclusion endpoints
		$(document).ajaxSuccess(function(event, xhr, settings) {
			if (settings.data && settings.data.includes(containerId.replace('-list', '').replace('gv-', '') + '_exclusion')) {
				// Wait a moment for the DOM to update
				setTimeout(function() {
					const currentValues = [];
					container.find('.gv-checkbox-input').filter(':checked').each(function() {
						currentValues.push($(this).val());
					});
					hiddenInput.data('original-values', currentValues);
					updateCount();
				}, 100);
			}
		});
	}
	
	// Initialize both lists
	if ($('#gv-plugins-list').length) {
		initCheckboxList('gv-plugins-list', 'gv_blocked_plugins_hidden');
	}
	
	if ($('#gv-themes-list').length) {
		initCheckboxList('gv-themes-list', 'gv_blocked_themes_hidden');
	}
	
});
</script>

<div class="wrap gv-wrapper gv-wrapper-blacklists" id="gv_settings_wrapper" style="max-width:1400px;margin: 0 auto 40px auto;">
	<!-- Notification container -->
	<div id="gv_popups"></div>
	
	<div class="gv-material-card">
		<?php if ( $is_license_activated ) : ?>
			<div class="gv-material-header">
				<h1 class="gv-material-title">
					<span class="dashicons dashicons-dismiss" style="margin-right: 12px; color: var(--gv-material-primary);"></span>
					<?php esc_html_e( 'Disable Automatic Updates', 'gplvault' ); ?>
				</h1>
				<p class="gv-material-description">
					<?php esc_html_e( 'You can choose not to update certain plugins or themes from GPL Vault. This is useful if you have a direct license from the developer and prefer getting updates from them instead of GPL Vault.', 'gplvault' ); ?>
				</p>
			</div>
			
			<div class="gv-material-grid" id="gv_items_exclusion">
				<?php
				GPLVault_Admin::load_partial( 'settings/blocked-plugins', compact( 'blocked_plugins' ) );
				GPLVault_Admin::load_partial( 'settings/blocked-themes', compact( 'blocked_themes' ) );
				?>
			</div>
		<?php else : ?>
			<?php
			GPLVault_Admin::load_partial( 'settings/activation-note', compact( 'settings_url' ) );
			?>
		<?php endif; ?>
	</div>
</div>
