window.gplvault = window.gplvault || {};

(function ($, gplvault, settings, wp) {
	gplvault = gplvault || {};
	var $document = $(document),
		__ = wp.i18n.__,
		gv = gplvault,
		selectors = settings.selectors || {},
		notifier = gv.common.notifier,
		$gv_license_status_wrapper = $('#gv_license_status_wrapper'),
		console = window.console;
	gv.admin = {};
	gv.admin.selectors = selectors;
	gv.admin.ajaxLocked = false;
	gv.admin.queue = [];
	gv.admin.tmplApiHeader = gv.template('api-header');
	gv.admin.tmplApiStatus = gv.template('status');
	gv.admin.templateApiKeyInfo = gv.template('key-info');
	gv.admin.templateApiForm = gv.template('api-form');

	gv.admin.ajax = function (context, data) {
		var options = {};

		gv.admin.ajaxLocked = true;

		options = _.extend(options, data || {}, { context: context });

		return gv.common.ajax(options).always(gv.admin.ajaxAlways);
	};

	gv.admin.ajaxAlways = function (response) {
		gv.admin.ajaxLocked = false;

		if (response.status) {
			return;
		}

		if (
			'undefined' !== typeof response.debug &&
			window.console &&
			window.console.log
		) {
			_.map(response.debug, function (message) {
				// Remove all HTML tags and write a message to the console.
				window.console.log(wp.sanitize.stripTagsAndEncodeText(message));
			});
		}
	};

	gv.admin.licenseSettingsSuccess = function (response) {
		response = response || {};
		response.payload = response.payload || {};

		// Update the status badge for Material Design
		var $statusBadge = $('#api_settings_column').find(
			'.gv-material-status-badge'
		);
		if ($statusBadge.length) {
			$statusBadge.removeClass('inactive').addClass('active');
			$statusBadge.html(
				'<span class="dashicons dashicons-yes-alt"></span>' +
					__('Activated', 'gplvault')
			);
		}

		// Get the input values since they might not be in the response
		var inputKey = $('#api_master_key').val();
		var inputProductId = $('#api_product_id').val();

		// Update the form to show masked key and product ID
		var $columnBody = $('#api_settings_column').find(
			'.gv-material-column-body'
		);
		if ($columnBody.length) {
			// Use input values if response doesn't have them
			var maskedKey =
				response.payload.api_key ||
				response.payload.master_key ||
				inputKey ||
				'';
			var productId = response.payload.product_id || inputProductId || '';

			// Mask the key if it's not already masked
			if (
				maskedKey &&
				maskedKey.length > 10 &&
				!maskedKey.includes('*')
			) {
				maskedKey =
					maskedKey.substring(0, 4) +
					'*'.repeat(maskedKey.length - 8) +
					maskedKey.substring(maskedKey.length - 4);
			}

			var activatedForm =
				'<div class="gv-material-field">' +
				'<div class="gv-material-field-label">' +
				'<span>' +
				__('User License Key', 'gplvault') +
				'</span>' +
				'</div>' +
				'<div class="gv-material-value">' +
				maskedKey +
				'</div>' +
				'</div>' +
				'<div class="gv-material-field">' +
				'<div class="gv-material-field-label">' +
				'<span>' +
				__('Product ID', 'gplvault') +
				'</span>' +
				'</div>' +
				'<div class="gv-material-value">' +
				productId +
				'</div>' +
				'</div>';

			$columnBody.html(activatedForm);
		}

		// Show the license status section with Material Design styling
		if (response.payload && $gv_license_status_wrapper.length) {
			// Calculate usage percentage
			var totalPurchased = parseInt(response.payload.total_activations_purchased) || 0;
			var totalUsed = parseInt(response.payload.total_activations) || 0;
			var usedPercentage = totalPurchased > 0 ? Math.round((totalUsed / totalPurchased) * 100 * 10) / 10 : 0;
			
			var statusHtml =
				'<div class="gv-material-status-section" id="gv_license_status_section">' +
				'<h2 class="gv-material-section-title" style="font-size: 20px; font-weight: 500; color: #333; margin: 0 0 20px 0;">' +
				__('License Status', 'gplvault') +
				'</h2>' +
				
				// Progress bar container
				'<div class="gv-material-progress-container">' +
				'<div class="gv-material-progress-info">' +
				'<span class="gv-material-progress-text">' +
				totalUsed + ' ' + __('of', 'gplvault') + ' ' + totalPurchased + ' ' + __('licenses used', 'gplvault') +
				'</span>' +
				'<span class="gv-material-progress-percentage">' + usedPercentage + '%</span>' +
				'</div>' +
				'<div class="gv-material-progress-bar">' +
				'<div class="gv-material-progress-fill" style="width: ' + usedPercentage + '%"></div>' +
				'</div>' +
				'</div>' +
				
				// 3 metric cards
				'<div class="gv-material-status-grid">' +
				'<div class="gv-material-status-item total">' +
				'<div class="gv-material-status-label">' +
				__('Total Quota', 'gplvault') +
				'</div>' +
				'<div class="gv-material-status-value">' +
				(response.payload.total_activations_purchased || '0') +
				'</div>' +
				'</div>' +
				'<div class="gv-material-status-item used">' +
				'<div class="gv-material-status-label">' +
				__('Already Activated', 'gplvault') +
				'</div>' +
				'<div class="gv-material-status-value">' +
				(response.payload.total_activations || '0') +
				'</div>' +
				'</div>' +
				'<div class="gv-material-status-item remaining">' +
				'<div class="gv-material-status-label">' +
				__('Remaining', 'gplvault') +
				'</div>' +
				'<div class="gv-material-status-value">' +
				(response.payload.activations_remaining || '0') +
				'</div>' +
				'</div>' +
				'</div>' +
				'</div>';

			$gv_license_status_wrapper.html(statusHtml).fadeIn('slow');
		}

		// Enable action buttons
		$('#license_deactivation, #check_license, #cleanup_settings').prop(
			'disabled',
			false
		);

		// Remove any WordPress notices
		$('#wp__notice-list').find('.notice').remove();

		notifier.add({
			type: 'success',
			title: response.payload.title || __('Success', 'gplvault'),
			content: response.payload.message,
		});
	};

	gv.admin.licenseSettingsError = function (response) {
		response = response || {};
		response.payload = response.payload || {};

		notifier.add({
			type: 'error',
			title: response.payload.title || 'Error!',
			content: response.message,
		});
	};

	gv.admin.activateLicense = function (context, selector) {
		context = context || 'license_activation';
		var inputKey = $('#' + selectors.license.api.input_key),
			inputProduct = $('#' + selectors.license.api.input_product),
			api_key = inputKey.val(),
			product_id = inputProduct.val(),
			promise,
			data = {};

		if (api_key.length < 40 || product_id.length < 1) {
			throw new ValidationError(
				__(
					'Both User License Key and Product ID fields are required.',
					'gplvault'
				),
				__('Required Fields!', 'gplvault')
			);
		}

		data.api_key = api_key;
		data.product_id = product_id;

		$document.trigger('gv-updating-license', {
			context: context,
			selector: selector,
			payload: data,
		});
		promise = gv.admin.ajax(context, data);
		$document.trigger('gv-updated-license', {
			context: context,
			selector: selector,
			payload: data,
			promise: promise,
		});
		promise
			.done(gv.admin.licenseSettingsSuccess)
			.fail(gv.admin.licenseSettingsError);
		return promise;
	};

	gv.admin.resetSettingsForm = function () {
		// $('#api_master_key, #api_product_id').val('');

		$('#api_form_inner').html(gv.admin.templateApiForm()).fadeIn('slow');
	};

	gv.admin.licenseDeactivationSuccess = function (response) {
		response = response || {};
		response.payload = response.payload || {};

		// Update the status badge for Material Design
		var $statusBadge = $('#api_settings_column').find(
			'.gv-material-status-badge'
		);
		if ($statusBadge.length) {
			$statusBadge.removeClass('active').addClass('inactive');
			$statusBadge.html(
				'<span class="dashicons dashicons-dismiss"></span>' +
					__('Deactivated', 'gplvault')
			);
		}

		// Hide the license status section
		$gv_license_status_wrapper.fadeOut('slow').html('');

		// Update the form to show activation inputs
		var $columnBody = $('#api_settings_column').find(
			'.gv-material-column-body'
		);
		if ($columnBody.length) {
			var activationForm =
				'<div class="gv-material-field">' +
				'<div class="gv-material-field-label">' +
				'<span>' +
				__('User License Key', 'gplvault') +
				'</span>' +
				'<span class="gv-material-help-tip gv-has-tooltip" data-tippy-placement="top-start" data-tippy-content="' +
				__(
					'Your User License Key can be found in the License Keys section of your GPLVault account',
					'gplvault'
				) +
				'"></span>' +
				'</div>' +
				'<input class="gv-material-input" type="text" id="api_master_key" name="api_master_key" placeholder="' +
				__('Enter user license key', 'gplvault') +
				'" />' +
				'</div>' +
				'<div class="gv-material-field">' +
				'<div class="gv-material-field-label">' +
				'<span>' +
				__('Product ID', 'gplvault') +
				'</span>' +
				'<span class="gv-material-help-tip gv-has-tooltip" data-tippy-placement="top-start" data-tippy-content="' +
				__(
					'Your Product ID can be found in the License Keys section of your GPLVault account',
					'gplvault'
				) +
				'"></span>' +
				'</div>' +
				'<input class="gv-material-input" type="text" id="api_product_id" name="api_product_id" placeholder="' +
				__('Enter product id', 'gplvault') +
				'" />' +
				'</div>' +
				'<div class="gv-material-actions">' +
				'<button type="button" id="gv_activate_api" class="gv-material-button gv-material-button-primary" data-context="license_activation">' +
				'<span class="dashicons dashicons-admin-network"></span>' +
				__('Activate License', 'gplvault') +
				'</button>' +
				'</div>';

			$columnBody.html(activationForm);

			// Re-initialize tooltips for the new elements
			if (typeof tippy !== 'undefined') {
				tippy('[data-tippy-content]');
			}
		}

		// Disable action buttons
		$('#license_deactivation, #check_license, #cleanup_settings').prop(
			'disabled',
			true
		);

		// Remove update counts
		$('.gv-count-total, .gv-count-plugins, .gv-count-themes').remove();

		notifier.add({
			type: 'success',
			title: response.payload.title || 'Success',
			content: response.payload.message,
		});
	};

	gv.admin.licenseDeactivationError = function (response) {
		response = response || {};
		response.payload = response.payload || {};

		notifier.add({
			type: 'error',
			title: response.payload.title || 'Error',
			content: response.message,
		});
	};

	gv.admin.deactivateLicense = function (context, selector) {
		var promise;
		context = context || 'license_deactivation';

		$document.trigger('gv-deactivating-api', {
			context: context,
			selector: selector,
		});
		promise = gv.admin.ajax(context);
		promise
			.done(gv.admin.licenseDeactivationSuccess)
			.fail(gv.admin.licenseDeactivationError);

		$document.trigger('gv-deactivated-api', {
			context: context,
			selector: selector,
			promise: promise,
		});

		return promise;
	};

	gv.admin.licenseStatusSuccess = function (response) {
		response = response || {};
		response.payload = response.payload || {};

		// Update the status badge for Material Design
		var $statusBadge = $('#api_settings_column').find(
			'.gv-material-status-badge'
		);
		if ($statusBadge.length) {
			if (response.payload.activated) {
				$statusBadge.removeClass('inactive').addClass('active');
				$statusBadge.html(
					'<span class="dashicons dashicons-yes-alt"></span>' +
						__('Activated', 'gplvault')
				);
			} else {
				$statusBadge.removeClass('active').addClass('inactive');
				$statusBadge.html(
					'<span class="dashicons dashicons-dismiss"></span>' +
						__('Deactivated', 'gplvault')
				);
			}
		}

		// Update the license status section with Material Design styling
		if (
			response.payload &&
			response.payload.activated &&
			$gv_license_status_wrapper.length
		) {
			// Calculate usage percentage
			var totalPurchased = parseInt(response.payload.total_activations_purchased) || 0;
			var totalUsed = parseInt(response.payload.total_activations) || 0;
			var usedPercentage = totalPurchased > 0 ? Math.round((totalUsed / totalPurchased) * 100 * 10) / 10 : 0;
			
			var statusHtml =
				'<div class="gv-material-status-section" id="gv_license_status_section">' +
				'<h2 class="gv-material-section-title" style="font-size: 20px; font-weight: 500; color: #333; margin: 0 0 20px 0;">' +
				__('License Status', 'gplvault') +
				'</h2>' +
				
				// Progress bar container
				'<div class="gv-material-progress-container">' +
				'<div class="gv-material-progress-info">' +
				'<span class="gv-material-progress-text">' +
				totalUsed + ' ' + __('of', 'gplvault') + ' ' + totalPurchased + ' ' + __('licenses used', 'gplvault') +
				'</span>' +
				'<span class="gv-material-progress-percentage">' + usedPercentage + '%</span>' +
				'</div>' +
				'<div class="gv-material-progress-bar">' +
				'<div class="gv-material-progress-fill" style="width: ' + usedPercentage + '%"></div>' +
				'</div>' +
				'</div>' +
				
				// 3 metric cards
				'<div class="gv-material-status-grid">' +
				'<div class="gv-material-status-item total">' +
				'<div class="gv-material-status-label">' +
				__('Total Quota', 'gplvault') +
				'</div>' +
				'<div class="gv-material-status-value">' +
				(response.payload.total_activations_purchased || '0') +
				'</div>' +
				'</div>' +
				'<div class="gv-material-status-item used">' +
				'<div class="gv-material-status-label">' +
				__('Already Activated', 'gplvault') +
				'</div>' +
				'<div class="gv-material-status-value">' +
				(response.payload.total_activations || '0') +
				'</div>' +
				'</div>' +
				'<div class="gv-material-status-item remaining">' +
				'<div class="gv-material-status-label">' +
				__('Remaining', 'gplvault') +
				'</div>' +
				'<div class="gv-material-status-value">' +
				(response.payload.activations_remaining || '0') +
				'</div>' +
				'</div>' +
				'</div>' +
				'</div>';

			$gv_license_status_wrapper.html(statusHtml).fadeIn('slow');
		} else if (!response.payload.activated) {
			// Hide status section if not activated
			$gv_license_status_wrapper.fadeOut('slow').html('');
		}

		notifier.add({
			type: 'success',
			title: response.payload.title || __('Success', 'gplvault'),
			content: response.payload.message,
		});
	};

	gv.admin.licenseStatusError = function (response) {
		response = response || {};
		response.payload = response.payload || {};

		notifier.add({
			type: 'error',
			title: response.payload.title || __('Error', 'gplvault'),
			content: response.message,
		});
	};

	gv.admin.checkLicense = function (context, selector) {
		var promise;
		context = context || 'check_license';

		$document.trigger('gv-checking-api', {
			context: context,
			selector: selector,
		});
		promise = gv.admin.ajax(context);
		promise
			.done(gv.admin.licenseStatusSuccess)
			.fail(gv.admin.licenseStatusError);
		$document.trigger('gv-checked-api', {
			context: context,
			selector: selector,
			promise: promise,
		});

		return promise;
	};

	gv.admin.cleanupSettingsSuccess = function (response) {
		response = response || {};
		response.payload = response.payload || {};

		// Update the status badge for Material Design
		var $statusBadge = $('#api_settings_column').find(
			'.gv-material-status-badge'
		);
		if ($statusBadge.length) {
			$statusBadge.removeClass('active').addClass('inactive');
			$statusBadge.html(
				'<span class="dashicons dashicons-dismiss"></span>' +
					__('Deactivated', 'gplvault')
			);
		}

		// Hide the license status section
		$gv_license_status_wrapper.fadeOut('slow').html('');

		// Update the form to show activation inputs
		var $columnBody = $('#api_settings_column').find(
			'.gv-material-column-body'
		);
		if ($columnBody.length) {
			var activationForm =
				'<div class="gv-material-field">' +
				'<div class="gv-material-field-label">' +
				'<span>' +
				__('User License Key', 'gplvault') +
				'</span>' +
				'<span class="gv-material-help-tip gv-has-tooltip" data-tippy-placement="top-start" data-tippy-content="' +
				__(
					'Your User License Key can be found in the License Keys section of your GPLVault account',
					'gplvault'
				) +
				'"></span>' +
				'</div>' +
				'<input class="gv-material-input" type="text" id="api_master_key" name="api_master_key" placeholder="' +
				__('Enter user license key', 'gplvault') +
				'" />' +
				'</div>' +
				'<div class="gv-material-field">' +
				'<div class="gv-material-field-label">' +
				'<span>' +
				__('Product ID', 'gplvault') +
				'</span>' +
				'<span class="gv-material-help-tip gv-has-tooltip" data-tippy-placement="top-start" data-tippy-content="' +
				__(
					'Your Product ID can be found in the License Keys section of your GPLVault account',
					'gplvault'
				) +
				'"></span>' +
				'</div>' +
				'<input class="gv-material-input" type="text" id="api_product_id" name="api_product_id" placeholder="' +
				__('Enter product id', 'gplvault') +
				'" />' +
				'</div>' +
				'<div class="gv-material-actions">' +
				'<button type="button" id="gv_activate_api" class="gv-material-button gv-material-button-primary" data-context="license_activation">' +
				'<span class="dashicons dashicons-admin-network"></span>' +
				__('Activate License', 'gplvault') +
				'</button>' +
				'</div>';

			$columnBody.html(activationForm);

			// Re-initialize tooltips for the new elements
			if (typeof tippy !== 'undefined') {
				tippy('[data-tippy-content]');
			}
		}

		// Disable action buttons
		$('#license_deactivation, #check_license, #cleanup_settings').prop(
			'disabled',
			true
		);

		// Remove update counts
		$('.gv-count-total, .gv-count-plugins, .gv-count-themes').remove();

		notifier.add({
			type: 'success',
			title: response.payload.title || __('Success', 'gplvault'),
			content: response.payload.message,
		});
	};

	gv.admin.cleanupSettingsError = function (response) {
		response = response || {};
		response.payload = response.payload || {};

		notifier.add({
			type: 'error',
			title: __('Error', 'gplvault'),
			content: __('Unknown error occurred.', 'gplvault'),
		});
	};

	gv.admin.cleanupSettings = function (context, selector) {
		var promise;
		context = context || 'cleanup_settings';

		$document.trigger('gv-clearing-settings', {
			context: context,
			selector: selector,
		});
		promise = gv.admin.ajax(context);
		promise
			.done(gv.admin.cleanupSettingsSuccess)
			.fail(gv.admin.cleanupSettingsError);

		$document.trigger('gv-cleared-settings', {
			context: context,
			selector: selector,
			promise: promise,
		});

		return promise;
	};

	gv.admin.itemExclusionSuccess = function (response) {
		// The response structure from wp.ajax.post is different
		var title = response.title || __('Updated', 'gplvault');
		var message =
			response.message ||
			__('Settings updated successfully.', 'gplvault');

		notifier.add({
			type: 'success',
			title: title,
			content: message,
		});
	};

	gv.admin.itemExclusionError = function (response) {
		// For errors, the message is directly in the response
		var title = __('Not Updated', 'gplvault');
		var message =
			response ||
			__('An error occurred while updating settings.', 'gplvault');

		// If response is an object with a message property
		if (typeof response === 'object' && response.message) {
			message = response.message;
		}

		notifier.add({
			type: 'error',
			title: title,
			content: message,
		});
	};

	gv.admin.excludePlugins = function (context, selector) {
		context = context || 'plugins_exclusion';

		// Use the hidden input field for checkbox interface
		var inputEl = $('#gv_blocked_plugins_hidden'),
			promise,
			plugins = [];

		// Only split if there's actual content, otherwise keep empty array
		if (inputEl.val() && inputEl.val().trim() !== '') {
			plugins = inputEl.val().split(',');
		}

		$document.trigger('gv-excluding-plugins', {
			context: context,
			plugins: plugins,
		});

		promise = gv.admin.ajax(context, { plugins: plugins });
		promise
			.done(gv.admin.itemExclusionSuccess)
			.fail(gv.admin.itemExclusionError)
			.always(function () {
				selector.prop('disabled', true);
			});
		return promise;
	};

	gv.admin.excludeThemes = function (context, selector) {
		context = context || 'themes_exclusion';

		// Use the hidden input field for checkbox interface
		var inputEl = $('#gv_blocked_themes_hidden'),
			promise,
			themes = [];

		// Only split if there's actual content, otherwise keep empty array
		if (inputEl.val() && inputEl.val().trim() !== '') {
			themes = inputEl.val().split(',');
		}

		$document.trigger('gv-excluding-themes', {
			context: context,
			themes: themes,
		});
		promise = gv.admin.ajax(context, { themes: themes });
		promise
			.done(gv.admin.itemExclusionSuccess)
			.fail(gv.admin.itemExclusionError)
			.always(function () {
				selector.prop('disabled', true);
			});
		return promise;
	};

	gv.admin.getResolver = function (context) {
		var resolvers = {
			license_activation: gv.admin.activateLicense,
			license_deactivation: gv.admin.deactivateLicense,
			check_license: gv.admin.checkLicense,
			cleanup_settings: gv.admin.cleanupSettings,
			plugins_exclusion: gv.admin.excludePlugins,
			themes_exclusion: gv.admin.excludeThemes,
		};

		return resolvers[context];
	};

	gv.admin.ajaxResolver = function (selector) {
		var context = selector.data('context'),
			resolver = gv.admin.getResolver(context);

		if (resolver) {
			return resolver.call(null, context, selector);
		}

		return gv.admin.ajax(context);
	};

	$(function () {
		var $globalWrapper = $('#' + settings.selectors.page_wrapper),
			$itemExclusionSection = $(
				'#' + settings.selectors.exclusion.section_id
			),
			items;

		$document.on('click', 'button.gv-hide-pw', function (e) {
			e.preventDefault();
			gplvault.common.togglePW(e);
		});

		// Remove the old select change handler since we're using checkboxes now
		// The checkbox functionality is handled in the main blacklists.php file

		if ($globalWrapper.length > 0) {
			$globalWrapper.on('click', '[data-context]', function (event) {
				event.preventDefault();
				if (gv.admin.ajaxLocked) return false;

				var $_instance = $(this);

				if ($_instance.hasClass('gv-has-confirmation')) {
					// Store the instance for use in the callback
					var confirmationText = $_instance.data('confirmation');

					// Use native confirm for license_deactivation and cleanup_settings buttons
					// as Polipop doesn't support interactive buttons properly
					if ($_instance.attr('id') === 'license_deactivation' || $_instance.attr('id') === 'cleanup_settings') {
						if (window.confirm(confirmationText)) {
							proceedWithAction();
						} else {
							$_instance.removeClass('updating-message');
						}
						return false;
					}

					// Show custom confirmation dialog for other buttons
					notifier.add({
						type: 'warning',
						title: __('Please Confirm', 'gplvault'),
						content: confirmationText,
						life: 0, // Don't auto-close
						closer: true,
						buttons: [
							{
								text: __('Cancel', 'gplvault'),
								class: 'button-secondary',
								action: function () {
									$_instance.removeClass('updating-message');
									return false;
								},
							},
							{
								text: __('Confirm', 'gplvault'),
								class: 'button-primary',
								action: function () {
									// Continue with the original action
									proceedWithAction();
								},
							},
						],
					});

					return false; // Prevent default action until confirmed
				}

				// Extract the action logic into a separate function
				function proceedWithAction() {
					$_instance.addClass('updating-message');
					try {
						var result = gv.admin.ajaxResolver($_instance);
						result &&
							result.always(function () {
								$_instance.removeClass('updating-message');
							});
					} catch (e) {
						notifier.add({
							type: 'error',
							title: e.title || e.name,
							content: e,
						});
						$_instance.removeClass('updating-message');

						return false;
					}
				}

				// If no confirmation needed, proceed immediately
				if (!$_instance.hasClass('gv-has-confirmation')) {
					proceedWithAction();
				}
			});
		}
	});
})(jQuery, window.gplvault, window._gvAdminSettings, window.wp);
