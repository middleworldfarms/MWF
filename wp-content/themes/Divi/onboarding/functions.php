<?php
/**
 * Onboarding functions.php file.
 *
 * @package Divi
 * @subpackage onboarding
 *
 * @since ??
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Trigger redirect to onboarding page after theme activation.
 *
 * @return void
 */
function et_onboarding_trigger_redirect() {
	// Do not redirect if WP-CLI is active.
	if ( defined( 'WP_CLI' ) && WP_CLI ) {
		return;
	}

	if ( ! class_exists( 'ET_Onboarding' ) ) {
		// get_template_directory() does not output a trailing slash.
		// {@see https://developer.wordpress.org/reference/functions/get_template_directory/}.
		require_once get_template_directory() . '/onboarding/onboarding.php';
	}

	ET_Onboarding::redirect_to_onboarding_page();
}

add_action(
	'after_switch_theme',
	'et_onboarding_trigger_redirect'
);

/**
 * Trigger remove transients when theme is changed.
 *
 * @return void
 */
function et_onboarding_remove_transients() {
	if ( ! class_exists( 'ET_Onboarding' ) ) {
		// get_template_directory() does not output a trailing slash.
		// {@see https://developer.wordpress.org/reference/functions/get_template_directory/}.
		require_once get_template_directory() . '/onboarding/onboarding.php';
	}

	ET_Onboarding::remove_transients();
}

add_action(
	'switch_theme',
	'et_onboarding_remove_transients'
);
