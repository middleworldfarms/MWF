<?php

/**
 * Render License page.
 */
class Sfwf_License_Page {


	/**
	 * Execute the filters and actions.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'register_menu' ) );
		add_action( 'admin_init', array( $this, 'setting_fields' ) );
	}

	/**
	 * Register license menu under styler in WordPress Dashboard.
	 *
	 * @return void
	 */
	public function register_menu() {
		add_menu_page( 'Styles for WPForms', 'Styler WPForms', 'manage_options', 'sfwf_licenses' );
		add_submenu_page( 'sfwf_licenses', 'Licenses', 'Licenses', 'manage_options', 'sfwf_licenses', array( $this, 'license_settings' ) );
	}

	/**
	 * The HTML of license page.
	 *
	 * @return void
	 */
	public function license_settings() {

		?>
		<!-- Create a header in the default WordPress 'wrap' container -->
		<div class="wrap">

			<!-- Make a call to the WordPress function for rendering errors when settings are saved. -->
			<?php settings_errors(); ?>
			<!-- Create the form that will be used to render our options -->
			<form method="post" action="options.php">
				<?php settings_fields( 'sfwf_licenses' ); ?>
				<?php do_settings_sections( 'sfwf_licenses' ); ?>
				<?php submit_button(); ?>
			</form>

		</div><!-- /.wrap -->
		<?php
	}

	/**
	 * Render Settings fields.
	 *
	 * @return void
	 */
	public function setting_fields() {
		// If settings don't exist, create them.
		if ( false === get_option( 'sfwf_licenses' ) ) {
			add_option( 'sfwf_licenses' );
		}

		add_settings_section(
			'sfwf_licenses_section',
			'Add-On Licenses',
			array( $this, 'section_callback' ),
			'sfwf_licenses'
		);

		do_action( 'sfwf_license_fields', $this );

		// Register settings.
		register_setting( 'sfwf_licenses', 'sfwf_licenses' );
	}

	/**
	 * Show content above license fields.
	 *
	 * @return void
	 */
	public function section_callback() {

		echo '<h4> Licence Fields will automatically appear once you install addons for \'Ultimate Kit for WPForms\'. You can check all the available addons <a href="https://wpmonks.com/downloads/addon-bundle-for-wpforms/?utm_source=plugin&utm_medium=licence-page&utm_campaign=styler_wpforms_plugin" target="_blank">here</a></h4>';
	}
}

new Sfwf_License_Page();
