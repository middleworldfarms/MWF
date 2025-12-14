/**
 * This file belongs to the YIT Plugin Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */


(function ($) {
	"use strict";

	if ( typeof yithLicenceData === 'undefined' ) {
		console.error('Missing required YITH licence deps');
		return;
	}

	const YITHLicencePanel = {

		init: function () {
			$(document).on('click', '.yith-licence-where-find-these', this.toggleWhereToFindModal.bind(this));
			// Handle form activation
			$(document).on('submit', '.yith-licence-activation-form', this.handleActivation.bind(this));
			$(document).on('focusout', '.yith-licence-activation-form :input', this.checkActivationInput.bind(this));
			// Handle deactivation
			$(document).on('click', '.yith-plugin-fw__action-button--delete-action', this.handleDeactivation.bind(this));
		},

		// UTILS

		isEmail: function (email) {
			/* https://stackoverflow.com/questions/2855865/jquery-validate-e-mail-address-regex */
			var re = new RegExp(/^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/i);
			return re.test(email);
		},

		isLicenceKey: function (key) {
			var re = new RegExp(/^[a-zA-Z0-9]{8}-[a-zA-Z0-9]{4}-[a-zA-Z0-9]{4}-[a-zA-Z0-9]{4}-[a-zA-Z0-9]{12}$/g);
			return re.test(key);
		},

		doAjaxRequest: function (data, wrap) {

			data = data || [];

			data.push(
				{name: 'action', value: yithLicenceData.ajaxAction},
				{name: 'security', value: yithLicenceData.ajaxNonce},
				{name: 'yith-license-debug', value: -1 !== location.search.indexOf('yith-license-debug')}
			);

			return jQuery.ajax({
				type: 'POST',
				url: yithLicenceData.ajaxUrl,
				data,
				dataType: 'json',
				beforeSend: function () {
					wrap.block({
						message: null,
						overlayCSS: {
							background: '#fff',
							opacity: 0.6,
						},
					});
				}
			}).fail((jqXHR, textStatus, errorThrown) => {
				console.error( textStatus + ': ' + errorThrown );
				wrap.unblock();
			});
		},

		addActivationRow: function (html) {
			// Make sure is a jQuery object.
			html = $(html);
			$(html).addClass('activated');

			// add activation
			$('.yith-licence-header').show().after(html);

			setTimeout(function () {
				html.removeClass('activated');
			}, 5000);

			window.scrollTo({
				top: html.position().top - 100,
				behavior: 'smooth',
			});
		},

		addActivationFormRow: function (html) {
			// Make sure is a jQuery object.
			html = $(html);

			$('.yith-licences-list').append(html);

			// Remove header if activations list is empty.
			if ( !$('.yith-licence-activation').length ) {
				$('.yith-licence-header').hide();
			}
		},

		// MODALS

		toggleWhereToFindModal: function (ev) {
			ev.preventDefault();

			yith.ui.modal(
				{
					title: yithLicenceData.modal.title,
					content: yithLicenceData.modal.content,
					footer: yithLicenceData.modal.footer,
					width: 960,
					allowWpMenu: false,
					closeWhenClickingOnOverlay: true,
					classes: {
						title: 'yith-license-modal-title',
						content: 'yith-license-modal-content',
						footer: 'yith-license-modal-footer'
					}
				}
			);
		},

		// HANDLE ACTIVATION FORM INPUT.

		addInputError: function (input, errorMessage) {
			if ( !$(input).next('.yith-licence-error-message').length ) {
				$(input).after('<span class="yith-licence-error-message" />');
			}

			$(input).next('.yith-licence-error-message').html(errorMessage);
		},

		removeInputError: function (input) {
			$(input).next('.yith-licence-error-message').remove();
		},

		checkActivationInput: function (event) {
			const input = $(event.target);
			const key = $(input).attr('name');

			if ( this.isActivationInputValid(input) ) {
				this.removeInputError(input);
			} else {
				this.addInputError(input, yithLicenceData.errors[key]);
			}

			// Always validate form.
			this.isActivationFormValid(input.closest('form'));
		},

		isActivationInputValid: function (input) {
			const key = $(input).attr('name');
			const value = $(input).val()?.trim();

			return value && ('email' === key && this.isEmail(value)) || ('licence_key' === key && this.isLicenceKey(value));
		},

		isActivationFormValid: function (form) {
			const submitButton = $(form).find('input[type="submit"]');
			const isValid = !$(form).find('input[type="text"]').filter((i, input) => !this.isActivationInputValid(input)).length;

			isValid ? submitButton.removeAttr('disabled') : submitButton.attr('disabled', true);

			return isValid;
		},

		// LICENCE ACTIONS

		handleActivation: function (event) {
			event.preventDefault();

			const handler = this;
			const licenceForm = $(event.target);

			if ( !handler.isActivationFormValid(licenceForm) ) {
				return false;
			}

			let data = licenceForm.serializeArray();
			data.push({name: 'request', value: 'licence_activation'});

			handler.doAjaxRequest(data, licenceForm).done((response) => {
				if ( response?.success ) {
					licenceForm.remove();
					handler.addActivationRow(response.data.html);
				} else {
					licenceForm.unblock();
					handler.addInputError( licenceForm.find( '[name="licence_key"]' ), response.data.message );
				}
			});
		},

		handleDeactivation: function (event) {
			event.preventDefault();

			const handler = this;
			const licenceWrap = $(event.target).closest('.yith-licence-activation');
			const pluginInit = licenceWrap.data('product');

			yith.ui.confirm(
				{
					title: yithLicenceData.deactivationConfirmTitle,
					message: yithLicenceData.deactivationConfirm.replace('{{plugin_name}}', '<strong>' + licenceWrap.find('.yith-licence-product').text() + '</strong>'),
					confirmButtonType: 'delete',
					onConfirm: function () {
						handler.doAjaxRequest(
							[
								{name: 'request', value: 'licence_deactivation'},
								{name: 'product_init', value: pluginInit}
							],
							licenceWrap
						).done((response) => {
							if ( response?.success ) {
								licenceWrap.remove();
								handler.addActivationFormRow(response.data.html);
							} else {
								licenceWrap.unblock();
							}
						});
					},
				}
			);
		}
	}

	YITHLicencePanel.init();

})(jQuery);
