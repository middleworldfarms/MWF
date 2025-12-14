<?php
/*
Plugin Name: Ultimate Kit ( Styler ) for WPForms
Plugin URI:  http://wpmonks.com/styler-wpforms
Description: Create beautiful styles for your WPForms
Version:     3.8
Author:      Sushil Kumar
Author URI:  http://wpmonks.com/
License:     GPL2License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

// don't load directly.
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}
define( 'SFWF_DIR', WP_PLUGIN_DIR . '/' . basename( __DIR__ ) );
define( 'SFWF_URL', plugins_url() . '/' . basename( __DIR__ ) );
define( 'SFWF_STORE_URL', 'https://wpmonks.com' );

if ( ! class_exists( 'Sfwf_EDD_SL_Plugin_Updater' ) ) {
	include_once SFWF_DIR . '/admin-menu/sfwf-edd-sl-plugin-updater.php';
}

require_once 'helpers/utils/class-sfwf-review.php';
require_once SFWF_DIR . '/admin-menu/class-sfwf-license-page.php';
require_once SFWF_DIR . '/admin-menu/class-sfwf-welcome-page.php';
require_once SFWF_DIR . '/admin-menu/class-sfwf-addons-page.php';
require_once SFWF_DIR . '/includes/class-sfwf-wpforms-styler-fetch.php';
require_once 'helpers/utils/responsive.php';

use WPForms\Frontend\CSSVars;

/**
 * Main class responsible for loading the plugin
 */
class Sk_Sfwf_Main_Class {


	const VERSION = '3.8';
	const SLUG    = 'styler-wpforms';
	const NAME    = 'Styler for WPForms';
	const AUTHOR  = 'Sushil Kumar';
	const PREFIX  = 'sk_sfwf';

	/**
	 * Review.
	 * Instance of class.
	 *
	 * @var   instance
	 * @since 1.0
	 */
	private static $instance;

	/**
	 * Contains if customizer ur
	 *
	 * @var string
	 */
	private $customizer_url;

	/**
	 * Contains if customizer is open
	 *
	 * @var string
	 */
	private $trigger;

	/**
	 * Form currently edited
	 *
	 * @var string
	 */
	private $sfwf_form_id;
	/**
	 * Plugin Directory
	 *
	 * @since 1.0
	 * @var   string $dir
	 */
	public static $dir = '';

	/**
	 * Plugin URL
	 *
	 * @since 1.0
	 * @var   string $url
	 */
	public static $url = '';

	/**
	 * Main Plugin Instance
	 *
	 * Insures that only one instance of a plugin class exists in memory at any one
	 * time. Also prevents needing to define globals all over the place.
	 *
	 * @since  1.0
	 * @static
	 * @static var array $instance
	 * @return sk_sfwf_main_class instance
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof sk_sfwf_main_class ) ) {
			self::$dir      = plugin_dir_path( __FILE__ );
			self::$url      = plugin_dir_url( __FILE__ );
			self::$instance = new sk_sfwf_main_class();
		}

		return self::$instance;
	}

	/**
	 * All action and filters
	 */
	public function __construct() {

		add_action( 'customize_register', array( $this, 'sfwf_customize_register' ) );
		add_action( 'customize_controls_enqueue_scripts', array( $this, 'sfwf_autosave_form' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ) );
		add_action( 'customize_preview_init', array( $this, 'sfwf_live_preview' ) );
		add_action( 'customize_save_after', array( $this, 'customize_save_after' ) );
		add_action( 'wpforms_frontend_output_before', array( $this, 'swfw_display_styles_frontend' ), 10, 2 );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_action( 'wpforms_admin_menu', array( $this, 'wpforms_admin_menu' ), 99 );
		add_filter( 'wpforms_forms_anti_spam_v3_is_honeypot_enabled', array( $this, 'filter_is_honeypot_enabled' ) );

		add_action( 'upgrader_process_complete', array( $this, 'sfwf_plugin_upgrade_completed' ), 10, 2 );

		// Primary panel button.
		if ( function_exists( 'wpforms' ) || class_exists( 'wpforms' ) ) {
			add_action( 'template_redirect', array( $this, 'sfwf_preview_template' ) );
			$this->trigger = 'sfwf-customizer';
			// only load controls for this plugin.
			if ( isset( $_GET[ $this->trigger ] ) ) {
				if ( ! empty( $_GET['sfwf_form_id'] ) ) {
					$this->sfwf_form_id = sanitize_text_field( wp_unslash( $_GET['sfwf_form_id'] ) );
				}
				add_filter( 'query_vars', array( $this, 'add_query_vars' ) );
			}
		}

		// Admin footer text.
		add_filter( 'admin_footer_text', array( $this, 'admin_footer' ), 2, 2 );
	}


	/**
	 * Filters whether the honeypot anti-spam feature is enabled for WPForms.
	 *
	 * @param bool $is_honeypot_enabled Whether the honeypot anti-spam feature is enabled.
	 * @return bool Whether the honeypot anti-spam feature is enabled.
	 */
	public function filter_is_honeypot_enabled( $is_honeypot_enabled ) {

		$action = sanitize_key( $_REQUEST['action'] ?? '' );

		if (
			in_array( $action, array( 'sfwf_wpforms_form_html' ), true )
		) {
			$is_honeypot_enabled = false;
		}

		return $is_honeypot_enabled;
	}

	/**
	 * If the right query var is present load the WPForms Forms preview template.
	 *
	 * @param  array $wp_query query var.
	 * @return array
	 */
	public function sfwf_preview_template( $wp_query ) {

		// load this conditionally based on the query var.
		if ( get_query_var( $this->trigger ) ) {
			wp_head();
			ob_start();
			$form_id = isset( $_GET['sfwf_form_id'] ) ? sanitize_text_field( wp_unslash( $_GET['sfwf_form_id'] ) ) : 0;
			include self::$dir . '/helpers/utils/html-template-preview.php';
			$message = ob_get_clean();
			wp_footer();
			echo $message;
			exit;
		}
		return $wp_query;
	}

	/**
	 * Callback function that is executed after the plugin has been upgraded.
	 *
	 * @param WP_Upgrader $upgrader_object The upgrader object.
	 * @param array       $options         An array of upgrade options.
	 */
	public function sfwf_plugin_upgrade_completed( $upgrader_object, $options ) {

		// $our_plugin = plugin_basename( __FILE__ );
		$our_plugin = 'styler-for-wpforms/styler-for-wpforms.php';

		if ( 'update' === $options['action'] && 'plugin' === $options['type'] ) {
			foreach ( $options['plugins'] as $plugin ) {
				if ( $plugin === $our_plugin ) {
					if ( class_exists( 'wpforms' ) || function_exists( 'wpforms' ) ) {
						$field_names = array( 'padding', 'margin' );

						$field_types = array( 'text-fields', 'submit-button', 'radio-inputs', 'paragraph-textarea', 'form-wrapper', 'form-title', 'form-description', 'form-header', 'field-labels', 'field-descriptions', 'error-message', 'dropdown-fields', 'confirmation-message', 'checkbox-inputs' );

						$forms = wpforms()->form->get();
						foreach ( $forms as $form ) {
							// Get the form ID.
							$form_id      = isset( $form->ID ) ? $form->ID : '';
							$sfwf_options = get_option( 'sfwf_form_id_' . $form_id );
							if ( ! empty( $sfwf_options ) ) {
								foreach ( $field_types as $field_type ) {

									foreach ( $field_names as $field_name ) {
										if ( isset( $sfwf_options[ $field_type ][ $field_name ] ) ) {
											$value  = trim( $sfwf_options[ $field_type ][ $field_name ] );
											$values = preg_split( '/[\s]+/', $value );
											$count  = count( $values );

											switch ( $count ) {
												case 4:
													$sfwf_options[ $field_type ][ $field_name . '-top' ]    = $values[0];
													$sfwf_options[ $field_type ][ $field_name . '-right' ]  = $values[1];
													$sfwf_options[ $field_type ][ $field_name . '-bottom' ] = $values[2];
													$sfwf_options[ $field_type ][ $field_name . '-left' ]   = $values[3];
													unset( $sfwf_options[ $field_type ][ $field_name ] );
													update_option( 'sfwf_form_id_' . $form_id, $sfwf_options );
													break;
												case 3:
													$sfwf_options[ $field_type ][ $field_name . '-top' ]    = $values[0];
													$sfwf_options[ $field_type ][ $field_name . '-right' ]  = $values[1];
													$sfwf_options[ $field_type ][ $field_name . '-bottom' ] = $values[2];
													$sfwf_options[ $field_type ][ $field_name . '-left' ]   = $values[1];
													unset( $sfwf_options[ $field_type ][ $field_name ] );
													update_option( 'sfwf_form_id_' . $form_id, $sfwf_options );
													break;
												case 2:
													$sfwf_options[ $field_type ][ $field_name . '-top' ]    = $values[0];
													$sfwf_options[ $field_type ][ $field_name . '-right' ]  = $values[1];
													$sfwf_options[ $field_type ][ $field_name . '-bottom' ] = $values[0];
													$sfwf_options[ $field_type ][ $field_name . '-left' ]   = $values[1];
													unset( $sfwf_options[ $field_type ][ $field_name ] );
													update_option( 'sfwf_form_id_' . $form_id, $sfwf_options );
													break;
												case 1:
													$sfwf_options[ $field_type ][ $field_name . '-top' ]    = $values[0];
													$sfwf_options[ $field_type ][ $field_name . '-right' ]  = $values[0];
													$sfwf_options[ $field_type ][ $field_name . '-bottom' ] = $values[0];
													$sfwf_options[ $field_type ][ $field_name . '-left' ]   = $values[0];
													unset( $sfwf_options[ $field_type ][ $field_name ] );
													update_option( 'sfwf_form_id_' . $form_id, $sfwf_options );
													break;
												default:
													// if the value is empty, set the default values.
													$sfwf_options[ $field_type ][ $field_name . '-top' ]    = '';
													$sfwf_options[ $field_type ][ $field_name . '-right' ]  = '';
													$sfwf_options[ $field_type ][ $field_name . '-bottom' ] = '';
													$sfwf_options[ $field_type ][ $field_name . '-left' ]   = '';
													unset( $sfwf_options[ $field_type ][ $field_name ] );
													update_option( 'sfwf_form_id_' . $form_id, $sfwf_options );
											}
										}
									}
								}
							}
						}
					}
				}
			}
		}
	}

	/**
	 * Register Ultimate Kit under wpforms
	 *
	 * @return void
	 */
	public function wpforms_admin_menu() {

		// Payments sub menu item.
		add_submenu_page(
			'wpforms-overview',
			esc_html__( 'Ultimate Kit', 'wpforms-lite' ),
			esc_html__( 'Ultimate Kit', 'wpforms-lite' ),
			'manage_options',
			'sfwf_wpforms_ultimate',
			array( $this, 'show_ultimate_kit_admin_page' )
		);
	}

	/**
	 * Backend Ultimate Kit interface root.
	 *
	 * @return void
	 */
	public function show_ultimate_kit_admin_page() {

		if ( class_exists( 'WPForms\Frontend\CSSVars' ) ) {
			$css_vars = new CSSVars();
			$css_vars->init();
			$css_vars->output_root( true );
		} elseif ( class_exists( 'WPForms_Frontend' ) ) {
			$wpforms_frontend = new WPForms_Frontend();
			// Check if the method exists and is callable.
			if ( method_exists( $wpforms_frontend, 'assets_css' ) && is_callable( array( $wpforms_frontend, 'assets_css' ) ) ) {
				$wpforms_frontend->assets_css();
			}
		}

		$form_id = isset( $_GET['formId'] ) ? sanitize_text_field( wp_unslash( $_GET['formId'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		echo '<div id="sfwf-wpform-ulitimate-addon"></div>';

		$form_content = array();
		if ( ! empty( $form_id ) ) {
			$form_content = wpforms()->form->get( $form_id, array( 'content_only' => true ) );
		}

		$forms = array( $form_content );
		do_action( 'sfwf_ultimate_addon_page_footer', $forms );
		do_action( 'sfwf_ultimate_kit_page_footer', $forms );
	}

	/**
	 * Add custom variables to the available query vars
	 *
	 * @since  1.0.0
	 * @param  array $vars query vars.
	 * @return array
	 */
	public function add_query_vars( $vars ) {
		$vars[] = $this->trigger;
		return $vars;
	}


	/**
	 * Set the customizer url
	 *
	 * @param  string $form_id id of the form.
	 * @return string
	 */
	private function set_customizer_url( $form_id ) {

		$url = admin_url( 'customize.php' );
		$url = add_query_arg( 'sfwf-customizer', 'true', $url );
		$url = add_query_arg( 'sfwf_form_id', $form_id, $url );
		$url = add_query_arg( 'autofocus[panel]', 'sfwf_panel', $url );

		$url                  = add_query_arg(
			'url',
			wp_nonce_url(
				rawurlencode(
					add_query_arg(
						array(
							'sfwf_form_id'     => $form_id,
							'sfwf-customizer'  => 'true',
							'autofocus[panel]' => 'sfwf_panel',
						),
						site_url()
					)
				),
				'preview-popup'
			),
			$url
		);
		$url                  = add_query_arg(
			'return',
			rawurlencode(
				add_query_arg(
					array(
						'page'    => 'wpforms-builder',
						'form_id' => $form_id,
					),
					admin_url( 'admin.php' )
				)
			),
			$url
		);
		$this->customizer_url = esc_url_raw( $url );
		return $this->customizer_url;
	}

	/**
	 * Enqueue scrips for customizer
	 *
	 * @return void
	 */
	public function wp_enqueue_scripts() {

		if ( is_customize_preview() ) {
			wp_enqueue_style( 'sfwf_live_preview_styles', self::$url . '/css/live-preview.css', array(), self::VERSION );
			wp_enqueue_script( 'sfwf_frontend_preview_wp', self::$url . '/js/frontend.js', array( 'jquery', 'customize-preview' ), self::VERSION, true );
		}
	}
	/**
	 * Function to display styles in frontend
	 *
	 * @param  array $form_data form data.
	 * @return void
	 */
	public function swfw_display_styles_frontend( $form_data, $form ) {
		$style_current_form = get_option( 'sfwf_form_id_' . $form->ID );

		if ( ! empty( $style_current_form ) ) {
			$css_form_id       = $form->ID;
			$main_class_object = self::instance();
			include 'display/class-styles.php';
		}

		do_action( 'sfwf_after_post_style_display' );
	}

	/**
	 * Enqueue js file that autosaves the form selection in database.
	 *
	 * @author Sushil Kumar
	 * @since  v1.0
	 */
	public function sfwf_autosave_form() {

		wp_enqueue_script( 'sfwf_customizer_controls', self::$url . '/js/customizer-controls/customizer-controls.js', array( 'jquery' ), self::VERSION, true );
		wp_enqueue_script( 'sfwf_auto_save_form', self::$url . '/js/auto-save-form.js', array( 'jquery' ), self::VERSION, true );
		wp_enqueue_style( 'sfwf_customizer_css', self::$url . '/css/customizer/sfwf-customizer-controls.css', array(), self::VERSION );
		wp_enqueue_style( 'sfwf_admin_css', self::$url . '/css/customizer-controls.css', array(), self::VERSION );
	}

	/**
	 * Enqueue style and scrips for admin
	 *
	 * @return void
	 */
	public function admin_enqueue_scripts() {

		if ( function_exists( 'wpforms' ) || class_exists( 'wpforms' ) ) {

			$wpforms_render_engine = '';
			if ( function_exists( 'wpforms_get_render_engine' ) ) {
				$wpforms_render_engine = wpforms_get_render_engine();
			}

			$min = '';
			if ( function_exists( 'wpforms_get_min_suffix' ) ) {
				$min = wpforms_get_min_suffix();
			}

			$disable_css = 0;
			if ( function_exists( 'wpforms_setting' ) ) {
				$disable_css = intval( wpforms_setting( 'disable-css', '1' ) );
			}

			if ( $disable_css === 3 || $wpforms_render_engine === 'classic' ) {
				return;
			}

			$style_name = $disable_css === 1 ? 'full' : 'base';

			wp_enqueue_style(
				"wpforms-{$wpforms_render_engine}-full",
				WPFORMS_PLUGIN_URL . "assets/css/frontend/{$wpforms_render_engine}/wpforms-{$style_name}{$min}.css",
				array(),
				WPFORMS_VERSION
			);

			wp_enqueue_style(
				"sfwf-wpforms-pro-{$wpforms_render_engine}-{$style_name}",
				WPFORMS_PLUGIN_URL . "assets/pro/css/frontend/{$wpforms_render_engine}/wpforms-{$style_name}{$min}.css",
				'',
				// array( "$wpforms_render_engine}-{$style_name}" ),
				WPFORMS_VERSION
			);

			wp_enqueue_script(
				'wpforms-flatpickr',
				WPFORMS_PLUGIN_URL . 'assets/lib/flatpickr/flatpickr.min.js',
				array( 'jquery' ),
				'4.6.9',
				true
			);
			wp_enqueue_script(
				'wpforms-jquery-timepicker',
				WPFORMS_PLUGIN_URL . 'assets/lib/jquery.timepicker/jquery.timepicker.min.js',
				array( 'jquery' ),
				'1.11.5',
				true
			);
		}

		wp_enqueue_style( 'sfwf_welcome_page_css', self::$url . '/css/admin.css', array(), self::VERSION );

		// include articles urls.
		include 'helpers/utils/article-urls.php';
		if ( is_admin() || defined( 'REST_REQUEST' ) || function_exists( 'wpforms' ) || class_exists( 'wpforms' ) ) {

			// for wpforms page.
			if ( isset( $_GET['page'] ) && 'wpforms-builder' === $_GET['page'] ) {
				wp_enqueue_script( 'sfwf-admin-wpforms-backend-editor-js', SFWF_URL . '/js/admin/admin.js', array( 'jquery' ), self::VERSION, true );

				$form_id = isset( $_GET['form_id'] ) ? intval( $_GET['form_id'] ) : '';
				wp_localize_script(
					'sfwf-admin-wpforms-backend-editor-js',
					'sfwfPowerUpsFormBuilderData',
					array(
						'adminUrl'        => get_admin_url(),
						'ultimatePageUrl' => admin_url( 'admin.php?page=sfwf_wpforms_ultimate' ),
						'formId'          => $form_id,
						'articlesUrls'    => $articles_urls,
					)
				);
			}

			if ( ( ! isset( $_GET['page'] ) || 'sfwf_wpforms_ultimate' !== $_GET['page'] ) ) {
				return;
			}
		}

		$asset_file = include SFWF_DIR . '/build/index.asset.php';

		wp_enqueue_style( 'sfwf-admin-styles', SFWF_URL . '/build/index.css', array( 'wp-components' ), $asset_file['version'] );

		wp_enqueue_media();

		wp_enqueue_script( 'sfwf-admin-wpforms-ultimate', SFWF_URL . '/build/index.js', $asset_file['dependencies'], $asset_file['version'], true );

		// wp_enqueue_script( 'sfwf-admin-wpforms-ultimate', SFWF_URL . '/build/index.js', '', $asset_file['version'], true );

		// Generate a nonce.
		$nonce = wp_create_nonce( 'sfwf_wpforms_ultimate_nonce' );

		$form_id = 0;
		if ( ! empty( $_GET['formId'] ) ) {
			$form_id = sanitize_text_field( wp_unslash( $_GET['formId'] ) );
		}

		$panel_id = 'styler';
		if ( ! empty( $_GET['panelId'] ) ) {
			$panel_id = sanitize_text_field( wp_unslash( $_GET['panelId'] ) );
		}

		$section_id = '';
		if ( ! empty( $_GET['sectionId'] ) ) {
			$section_id = sanitize_text_field( wp_unslash( $_GET['sectionId'] ) );
		}

		$customizer_url = $this->set_customizer_url( $form_id );
		$addons_info    = $this->get_ultimate_admin_js_addons_info();

		wp_localize_script(
			'sfwf-admin-wpforms-ultimate',
			'sfwfAdminWpformsUltimate',
			array(
				'nonce'         => $nonce,
				'formId'        => $form_id,
				'panelId'       => $panel_id,
				'sectionId'     => $section_id,
				'status'        => $addons_info['status'],
				'version'       => $addons_info['version'],
				'isRtl'         => is_rtl(),
				'customizerUrl' => $customizer_url,
				'adminUrl'      => get_admin_url(),
			)
		);

		// enqueue scripts from addons.
		do_action( 'sfwf_ultimate_enqueue_admin_scripts', $articles_urls );
	}

	/**
	 * The status and version information of other addons.
	 *
	 * @return array
	 */
	public function get_ultimate_admin_js_addons_info() {

		$asset_file        = include SFWF_DIR . '/build/index.asset.php';
		$js_dependencies   = $asset_file['dependencies'];
		$addon_dependecies = array();
		$status            = array();
		$version           = array();

		$addon_slugs = array(
			'tooltips'    => 'sfwf-tooltips/sfwf-tooltips.php',
			'fieldIcons'  => 'sfwf-field-icons/sfwf-field-icons.php',
			'bootstrap'   => 'sfwf-bootstrap/sfwf-bootstrap.php',
			'ai'          => 'ai-for-wpforms/ai-for-wpforms.php',
			'blacklist'   => 'sfwf-blacklist/sfwf-blacklist.php',
			'powerUps'    => 'sfwf-power-ups/sfwf-power-ups.php',
			'fileRenamer' => 'sfwf-file-renamer/sfwf-file-renamer.php',
			'bulkActions' => 'sfwf-bulk-actions/sfwf-bulk-actions.php',

		);

		foreach ( $addon_slugs as $name => $slug ) {

			if ( is_plugin_active( $slug ) ) {
				switch ( $name ) {
					case 'bootstrap':
						$status['bootstrap']  = 'active';
						$version['bootstrap'] = defined( 'SFWF_BOOT_VERSION' ) ? SFWF_BOOT_VERSION : '1.0';
						if ( (int) $version['bootstrap'] >= 2 ) {
							$addon_dependecies[] = 'sfwf-admin-bootstrap';
						}
						break;

					case 'tooltips':
						$status['tooltips']  = 'active';
						$version['tooltips'] = defined( 'SFWF_TOOLTIPS_VERSION' ) ? SFWF_TOOLTIPS_VERSION : '1.0';
						if ( (int) $version['tooltips'] >= 4 ) {
							$addon_dependecies[] = 'sfwf-admin-tooltips';
						}
						break;

					case 'fieldIcons':
						$status['fieldIcons']  = 'active';
						$version['fieldIcons'] = defined( 'SFWF_FIELD_ICONS_VERSION' ) ? SFWF_FIELD_ICONS_VERSION : '1.0';
						if ( (int) $version['fieldIcons'] >= 3 ) {
							$addon_dependecies[] = 'sfwf-admin-field-icons';
						}
						break;

					case 'ai':
						$status['ai']        = 'active';
						$version['ai']       = defined( 'UAFWF_AI_FOR_WPFORMS_VERSION' ) ? UAFWF_AI_FOR_WPFORMS_VERSION : '1.0';
						$addon_dependecies[] = 'sfwf-admin-ai';
						break;

					case 'blacklist':
						$status['blacklist']  = 'active';
						$version['blacklist'] = defined( 'SFWF_BLACKLIST_VERSION' ) ? SFWF_BLACKLIST_VERSION : '1.0';
						$addon_dependecies[]  = 'sfwf-admin-blacklist';
						break;

					case 'powerUps':
						$status['powerUps']  = 'active';
						$version['powerUps'] = defined( 'SFWF_POWER_UPS_VERSION' ) ? SFWF_POWER_UPS_VERSION : '1.0';
						$addon_dependecies[] = 'sfwf-admin-power-ups';
						break;

					case 'fileRenamer':
						$status['fileRenamer']  = 'active';
						$version['fileRenamer'] = defined( 'SFWF_FILE_RENAMER_VERSION' ) ? SFWF_FILE_RENAMER_VERSION : '1.0';
						$addon_dependecies[]    = 'sfwf-admin-file-renamer';
						break;
					case 'bulkActions':
						$status['bulkActions']  = 'active';
						$version['bulkActions'] = defined( 'SFWF_FILE_BULK_ACTIONS_VERSION' ) ? SFWF_BULK_ACTIONS_VERSION : '1.0';
						$addon_dependecies[]    = 'sfwf-admin-bulk-actions';
						break;
				}
			} else {
				$installed_plugins = get_plugins();
				if ( array_key_exists( $slug, $installed_plugins ) || in_array( $slug, $installed_plugins, true ) ) {
					$status[ $name ] = 'inActive';
				} else {
					$status[ $name ] = 'notInstalled';
				}
			}
		}
		$dependencies = array_merge( $addon_dependecies, $js_dependencies );
		return array(
			'dependencies' => $dependencies,
			'status'       => $status,
			'version'      => $version,
		);
	}
	/**
	 * Shows live preview of css changes.
	 *
	 * @author Sushil Kumar
	 * @since  v1.0
	 */
	public function sfwf_live_preview() {

		$current_form_id = get_option( 'sfwf_select_form_id' );

		wp_enqueue_script( 'sfwf_show_live_changes', self::$url . 'js/live-preview/live-preview-changes.js', array( 'jquery', 'customize-preview' ), self::VERSION, true );
		$current_form_id = get_option( 'sfwf_select_form_id' );
		wp_enqueue_script( 'sfwf_customizer_shortcut_icons', self::$url . 'js/live-preview/edit-shortcuts.js', array( 'jquery', 'customize-preview', 'wpforms' ), self::VERSION, true );

		wp_localize_script( 'sfwf_show_live_changes', 'sfwf_localize_current_form', array( 'formId' => $current_form_id ) );
		wp_localize_script( 'sfwf_customizer_shortcut_icons', 'sfwf_localize_edit_shortcuts', array( 'formId' => $current_form_id ) );
	}

	/**
	 * For deleting styles using customizer
	 *
	 * @return void
	 */
	public function customize_save_after() {

		// get name of style to be deleted.
		$style_to_be_deleted = get_option( 'sfwf_general_settings' );
		if ( -1 !== $style_to_be_deleted['reset-styles'] || ! empty( $style_to_be_deleted['reset-styles'] ) ) {
			delete_option( 'sfwf_form_id_' . $style_to_be_deleted['reset-styles'] );
			$style_to_be_deleted['reset-styles'] = -1;
			update_option( 'sfwf_general_settings', $style_to_be_deleted );
		}
	}

	/**
	 * Adding new panel in customizer
	 *
	 * @param  object $wp_customize main customizer object.
	 * @return void
	 */
	public function sfwf_customize_register( $wp_customize ) {

		if ( isset( $this->sfwf_form_id ) ) {
			update_option( 'sfwf_select_form_id', $this->sfwf_form_id );
		}

		$current_form_id = get_option( 'sfwf_select_form_id' );

		$border_types = array(
			'inherit' => 'Inherit',
			'solid'   => 'Solid',
			'dotted'  => 'Dotted',
			'dashed'  => 'Dashed',
			'double'  => 'Double',
			'groove'  => 'Groove',
			'ridge'   => 'Ridge',
			'inset'   => 'Inset',
			'outset'  => 'Outset',
		);
		$align_pos    = array(
			'left'    => 'Left',
			'center'  => 'Center',
			'justify' => 'Justify',
			'right'   => 'Right',
		);

		include 'helpers/utils/fonts.php';
		$wp_customize->add_panel(
			'sfwf_panel',
			array(
				'title'       => __( 'Styler for WPForms' ),
				'description' => '<p> Craft your Forms</p>', // Include html tags such as <p>.
				'priority'    => 160, // Mixed with top-level-section hierarchy.
			)
		);

		// hidden field to get form id in jquery.
		if ( ! array_key_exists( 'autofocus', $_GET ) || ( array_key_exists( 'autofocus', $_GET ) && isset( $_GET['autofocus']['panel'] ) && 'sfwf_panel' !== $_GET['autofocus']['panel'] ) ) {

			$wp_customize->add_setting(
				'sfwf_hidden_field_for_form_id',
				array(
					'default'   => $current_form_id,
					'transport' => 'postMessage',
					'type'      => 'option',
				)
			);

			$wp_customize->add_control(
				'sfwf_hidden_field_for_form_id',
				array(
					'type'        => 'hidden',
					'priority'    => 10, // Within the section.
					'section'     => 'sfwf_select_form_section', // Required, core or custom.
					'input_attrs' => array(
						'value' => $current_form_id,
						'id'    => 'sfwf_hidden_field_for_form_id',
					),
				)
			);
		}
		$border_types = array(
			'solid'  => 'Solid',
			'dotted' => 'Dotted',
			'dashed' => 'Dashed',
			'double' => 'Double',
			'groove' => 'Groove',
			'ridge'  => 'Ridge',
			'inset'  => 'Inset',
			'outset' => 'Outset',
		);
		$align_pos    = array(
			'left'    => 'Left',
			'center'  => 'Center',
			'right'   => 'Right',
			'justify' => 'Justify',
		);

		$font_style_choices = array(
			'bold'      => 'Bold',
			'italic'    => 'Italic',
			'uppercase' => 'Uppercase',
			'underline' => 'underline',
		);

		include 'helpers/customizer-controls/margin-padding.php';
		include 'helpers/customizer-controls/class-sfwf-desktop-text-input-option.php';
		include 'helpers/customizer-controls/class-sfwf-tab-text-input-option.php';
		include 'helpers/customizer-controls/class-sfwf-mobile-text-input-option.php';
		include 'helpers/customizer-controls/class-sfwf-text-alignment-option.php';
		include 'helpers/customizer-controls/class-sfwf-font-style-option.php';
		include 'helpers/customizer-controls/class-sfwf-customize-control-range-slider.php';
		include 'helpers/customizer-controls/class-sfwf-label-only.php';
		include 'helpers/customizer-controls/custom-controls.php';
		include 'includes/form-select.php';
		include 'includes/customizer-addons.php';
		include 'includes/general-settings.php';
		do_action( 'sfwf_add_addons_section', $wp_customize, $current_form_id );
		include 'includes/form-wrapper.php';
		include 'includes/form-header.php';
		include 'includes/form-title.php';
		include 'includes/form-description.php';
		include 'includes/field-labels.php';
		include 'includes/field-descriptions.php';
		include 'includes/text-fields.php';
		include 'includes/dropdown-fields.php';
		include 'includes/radio-inputs.php';
		include 'includes/checkbox-inputs.php';
		include 'includes/paragraph-textarea.php';
		include 'includes/submit-button.php';
		include 'includes/confirmation-message.php';
		include 'includes/error-message.php';
	} // main customizer function ends here.

	/**
	 * Convert saved database values to CSS propeties
	 *
	 * @param  int    $form_id   form id to get the saved values for it.
	 * @param  string $category  settings section for which details must be used.
	 * @param  string $important to mark the styles as important.
	 * @return string
	 */
	public function swfw_get_saved_styles( $form_id, $category, $important = '' ) {

		$settings = get_option( 'sfwf_form_id_' . $form_id );
		if ( is_customize_preview() ) {
			$important = '';
		}
		if ( empty( $settings ) ) {
			return;
		}
		$input_styles = '';

		if ( ! empty( $settings[ $category ]['font-style'] ) ) {
			$font_styles = trim( $settings[ $category ]['font-style'] );
			$font_styles = explode( '|', $font_styles );

			foreach ( $font_styles as $value ) {
				switch ( $value ) {
					case 'bold':
						$input_styles .= 'font-weight: bold;';
						break;
					case 'italic':
						$input_styles .= 'font-style: italic;';
						break;
					case 'uppercase':
						$input_styles .= 'text-transform: uppercase;';
						break;
					case 'underline':
						$input_styles .= 'text-decoration: underline;';
						break;
					default:
						break;
				}
			}
		}

		if ( isset( $settings[ $category ]['use-outer-shadows'] ) ) {
			$input_styles .= empty( $settings[ $category ]['horizontal-offset'] ) ? 'box-shadow: 0px ' : 'box-shadow:' . $settings[ $category ]['outer-horizontal-offset'] . ' ';
			$input_styles .= empty( $settings[ $category ]['outer-vertical-offset'] ) ? '0px ' : $settings[ $category ]['outer-vertical-offset'] . ' ';
			$input_styles .= empty( $settings[ $category ]['outer-blur-radius'] ) ? '0px ' : $settings[ $category ]['outer-blur-radius'] . ' ';
			$input_styles .= empty( $settings[ $category ]['outer-spread-radius'] ) ? '0px ' : $settings[ $category ]['outer-spread-radius'] . ' ';
			$input_styles .= empty( $settings[ $category ]['outer-shadow-color'] ) ? ';' : $settings[ $category ]['outer-shadow-color'] . ' ';

			if ( isset( $settings[ $category ]['use-inner-shadows'] ) ) {
				$input_styles .= empty( $settings[ $category ]['inner-horizontal-offset'] ) ? ', 0px ' : ', ' . $settings[ $category ]['inner-horizontal-offset'] . ' ';
				$input_styles .= empty( $settings[ $category ]['inner-vertical-offset'] ) ? '0px ' : $settings[ $category ]['inner-vertical-offset'] . ' ';
				$input_styles .= empty( $settings[ $category ]['inner-blur-radius'] ) ? '0px ' : $settings[ $category ]['inner-blur-radius'] . ' ';
				$input_styles .= empty( $settings[ $category ]['inner-spread-radius'] ) ? '0px ' : $settings[ $category ]['inner-spread-radius'] . ' ';
				$input_styles .= empty( $settings[ $category ]['inner-shadow-color'] ) ? ';' : $settings[ $category ]['inner-shadow-color'] . ' inset; ';
			} else {
				$input_styles .= ';';
			}
		}

		if ( isset( $settings[ $category ]['use-outer-shadows'] ) ) {
			$input_styles .= empty( $settings[ $category ]['outer-horizontal-offset'] ) ? '-moz-box-shadow: 0px ' : '-moz-box-shadow:' . $settings[ $category ]['outer-horizontal-offset'] . ' ';
			$input_styles .= empty( $settings[ $category ]['outer-vertical-offset'] ) ? '0px ' : $settings[ $category ]['outer-vertical-offset'] . ' ';
			$input_styles .= empty( $settings[ $category ]['outer-blur-radius'] ) ? '0px ' : $settings[ $category ]['outer-blur-radius'] . ' ';
			$input_styles .= empty( $settings[ $category ]['outer-spread-radius'] ) ? '0px ' : $settings[ $category ]['outer-spread-radius'] . ' ';
			$input_styles .= empty( $settings[ $category ]['outer-shadow-color'] ) ? ';' : $settings[ $category ]['outer-shadow-color'] . ' ';

			if ( isset( $settings[ $category ]['use-inner-shadows'] ) ) {
				$input_styles .= empty( $settings[ $category ]['inner-horizontal-offset'] ) ? ', 0px ' : ', ' . $settings[ $category ]['inner-horizontal-offset'] . ' ';
				$input_styles .= empty( $settings[ $category ]['inner-vertical-offset'] ) ? '0px ' : $settings[ $category ]['inner-vertical-offset'] . ' ';
				$input_styles .= empty( $settings[ $category ]['inner-blur-radius'] ) ? '0px ' : $settings[ $category ]['inner-blur-radius'] . ' ';
				$input_styles .= empty( $settings[ $category ]['inner-spread-radius'] ) ? '0px ' : $settings[ $category ]['inner-spread-radius'] . ' ';
				$input_styles .= empty( $settings[ $category ]['inner-shadow-color'] ) ? ';' : $settings[ $category ]['inner-shadow-color'] . ' inset; ';
			} else {
				$input_styles .= ';';
			}
		}

		if ( isset( $settings[ $category ]['use-outer-shadows'] ) ) {

			$input_styles .= empty( $settings[ $category ]['outer-horizontal-offset'] ) ? '-webkit-box-shadow: 0px ' : '-webkit-box-shadow:' . $settings[ $category ]['outer-horizontal-offset'] . ' ';
			$input_styles .= empty( $settings[ $category ]['outer-vertical-offset'] ) ? '0px ' : $settings[ $category ]['outer-vertical-offset'] . ' ';
			$input_styles .= empty( $settings[ $category ]['outer-blur-radius'] ) ? '0px ' : $settings[ $category ]['outer-blur-radius'] . ' ';
			$input_styles .= empty( $settings[ $category ]['outer-spread-radius'] ) ? '0px ' : $settings[ $category ]['outer-spread-radius'] . ' ';
			$input_styles .= empty( $settings[ $category ]['outer-shadow-color'] ) ? ';' : $settings[ $category ]['outer-shadow-color'] . ' ';

			if ( isset( $settings[ $category ]['use-inner-shadows'] ) ) {
				$input_styles .= empty( $settings[ $category ]['inner-horizontal-offset'] ) ? ', 0px ' : ', ' . $settings[ $category ]['inner-horizontal-offset'] . ' ';
				$input_styles .= empty( $settings[ $category ]['inner-vertical-offset'] ) ? '0px ' : $settings[ $category ]['inner-vertical-offset'] . ' ';
				$input_styles .= empty( $settings[ $category ]['inner-blur-radius'] ) ? '0px ' : $settings[ $category ]['inner-blur-radius'] . ' ';
				$input_styles .= empty( $settings[ $category ]['inner-spread-radius'] ) ? '0px ' : $settings[ $category ]['inner-spread-radius'] . ' ';
				$input_styles .= empty( $settings[ $category ]['inner-shadow-color'] ) ? ';' : $settings[ $category ]['inner-shadow-color'] . ' inset; ';
			} else {
				$input_styles .= ';';
			}
		}

		$input_styles .= empty( $settings[ $category ]['color'] ) ? '' : 'color:' . $settings[ $category ]['color'] . ' ' . $important . ';';
		$input_styles .= empty( $settings[ $category ]['background-color'] ) ? '' : 'background-color:' . $settings[ $category ]['background-color'] . ' ' . $important . ';';

		$input_styles .= empty( $settings[ $category ]['gradient-color'] ) ? '' : 'background-image:' . $settings[ $category ]['gradient-color'] . ' ' . $important . ';';

		$input_styles .= empty( $settings[ $category ]['background-color1'] ) ? '' : 'background:-webkit-linear-gradient(to left,' . $settings[ $category ]['background-color'] . ',' . $settings[ $category ]['background-color1'] . ') ' . $important . ';';
		$input_styles .= empty( $settings[ $category ]['background-color1'] ) ? '' : 'background:linear-gradient(to left,' . $settings[ $category ]['background-color'] . ',' . $settings[ $category ]['background-color1'] . ') ' . $important . ';';

		$input_styles .= empty( $settings[ $category ]['width'] ) ? '' : 'width:' . $settings[ $category ]['width'] . $this->sfwf_add_px_to_value( $settings[ $category ]['width'] ) . ' ' . $important . ';';
		$input_styles .= empty( $settings[ $category ]['height'] ) ? '' : 'height:' . $settings[ $category ]['height'] . $this->sfwf_add_px_to_value( $settings[ $category ]['height'] ) . ' ' . $important . ';';

		$input_styles .= empty( $settings[ $category ]['title-position'] ) ? '' : 'text-align:' . $settings[ $category ]['title-position'] . ' ' . $important . ';';
		$input_styles .= empty( $settings[ $category ]['text-align'] ) ? '' : 'text-align:' . $settings[ $category ]['text-align'] . ' ' . $important . ';';
		$input_styles .= empty( $settings[ $category ]['error-position'] ) ? '' : 'text-align:' . $settings[ $category ]['error-position'] . ' ' . $important . ';';
		$input_styles .= empty( $settings[ $category ]['description-position'] ) ? '' : 'text-align:' . $settings[ $category ]['description-position'] . ' ' . $important . ';';

		$input_styles .= empty( $settings[ $category ]['title-color'] ) ? '' : 'color:' . $settings[ $category ]['title-color'] . ' ' . $important . ';';
		$input_styles .= empty( $settings[ $category ]['font-color'] ) ? '' : 'color:' . $settings[ $category ]['font-color'] . ' ' . $important . ';';

		$input_styles .= empty( $settings[ $category ]['description-color'] ) ? '' : 'color:' . $settings[ $category ]['description-color'] . ' ' . $important . ';';

		$input_styles .= empty( $settings[ $category ]['button-color'] ) ? '' : 'background-color:' . $settings[ $category ]['button-color'] . ' ' . $important . ';';

		$input_styles .= empty( $settings[ $category ]['description-color'] ) ? '' : 'color:' . $settings[ $category ]['description-color'] . ' ' . $important . ';';
		$input_styles .= empty( $settings[ $category ]['line-height'] ) ? '' : 'line-height:' . $settings[ $category ]['line-height'] . ' ' . $important . ';';
		$input_styles .= empty( $settings[ $category ]['font-family'] ) ? '' : 'font-family:' . $settings[ $category ]['font-family'] . ' ' . $important . ';';
		$input_styles .= empty( $settings[ $category ]['font-size'] ) ? '' : 'font-size:' . $settings[ $category ]['font-size'] . $this->sfwf_add_px_to_value( $settings[ $category ]['font-size'] ) . ';';
		$input_styles .= empty( $settings[ $category ]['max-width'] ) ? '' : 'width:' . $settings[ $category ]['max-width'] . $this->sfwf_add_px_to_value( $settings[ $category ]['max-width'] ) . ' ' . $important . ';';

		$input_styles .= empty( $settings[ $category ]['maximum-width'] ) ? '' : 'width:' . $settings[ $category ]['maximum-width'] . $this->sfwf_add_px_to_value( $settings[ $category ]['maximum-width'] ) . ' ' . $important . ';';

		$input_styles .= empty( $settings[ $category ]['margin'] ) ? '' : 'margin:' . $settings[ $category ]['margin'] . ' ' . $important . ';';
		$input_styles .= empty( $settings[ $category ]['margin-left'] ) ? '' : 'margin-left:' . $settings[ $category ]['margin-left'] . $this->sfwf_add_px_to_value( $settings[ $category ]['margin-left'] ) . $important . ';';

		$input_styles .= empty( $settings[ $category ]['margin-right'] ) ? '' : 'margin-right:' . $settings[ $category ]['margin-right'] . $this->sfwf_add_px_to_value( $settings[ $category ]['margin-right'] ) . $important . ';';

		$input_styles .= empty( $settings[ $category ]['margin-top'] ) ? '' : 'margin-top:' . $settings[ $category ]['margin-top'] . $this->sfwf_add_px_to_value( $settings[ $category ]['margin-top'] ) . $important . ';';

		$input_styles .= empty( $settings[ $category ]['margin-bottom'] ) ? '' : 'margin-bottom:' . $settings[ $category ]['margin-bottom'] . $this->sfwf_add_px_to_value( $settings[ $category ]['margin-bottom'] ) . $important . ';';

		$input_styles .= empty( $settings[ $category ]['padding'] ) ? '' : 'padding:' . $settings[ $category ]['padding'] . ' ' . $important . ';';

		$input_styles .= empty( $settings[ $category ]['padding-left'] ) ? '' : 'padding-left:' . $settings[ $category ]['padding-left'] . $this->sfwf_add_px_to_value( $settings[ $category ]['padding-left'] ) . $important . ';';

		$input_styles .= empty( $settings[ $category ]['padding-right'] ) ? '' : 'padding-right:' . $settings[ $category ]['padding-right'] . $this->sfwf_add_px_to_value( $settings[ $category ]['padding-right'] ) . $important . ';';

		$input_styles .= empty( $settings[ $category ]['padding-top'] ) ? '' : 'padding-top:' . $settings[ $category ]['padding-top'] . $this->sfwf_add_px_to_value( $settings[ $category ]['padding-top'] ) . $important . ';';

		$input_styles .= empty( $settings[ $category ]['padding-bottom'] ) ? '' : 'padding-bottom:' . $settings[ $category ]['padding-bottom'] . $this->sfwf_add_px_to_value( $settings[ $category ]['padding-bottom'] ) . $important . ';';

		$input_styles .= empty( $settings[ $category ]['border-size'] ) ? '' : 'border-width:' . $settings[ $category ]['border-size'] . $this->sfwf_add_px_to_value( $settings[ $category ]['border-size'] ) . ' ' . $important . ';';

		$input_styles .= empty( $settings[ $category ]['border-color'] ) ? '' : 'border-color:' . $settings[ $category ]['border-color'] . ' ' . $important . ';';

		if ( ! empty( $settings[ $category ]['border-size'] ) ) {

			$input_styles .= empty( $settings[ $category ]['border-type'] ) ? '' : 'border-style:' . $settings[ $category ]['border-type'] . ' ' . $important . ';';
		}

		$input_styles .= empty( $settings[ $category ]['border-bottom'] ) ? '' : 'border-bottom-style:' . $settings[ $category ]['border-bottom'] . ' ' . $important . ';';
		$input_styles .= empty( $settings[ $category ]['border-bottom-size'] ) ? '' : 'border-bottom-width:' . $settings[ $category ]['border-bottom-size'] . $this->sfwf_add_px_to_value( $settings[ $category ]['border-bottom-size'] ) . ' ' . $important . ';';
		$input_styles .= empty( $settings[ $category ]['border-bottom-color'] ) ? '' : 'border-bottom-color:' . $settings[ $category ]['border-bottom-color'] . ' ' . $important . ';';

		$input_styles .= empty( $settings[ $category ]['background-image-url'] ) ? '' : 'background: url(' . $settings[ $category ]['background-image-url'] . ') no-repeat ' . $important . ';';
		$input_styles .= empty( $settings[ $category ]['border-bottom-color'] ) ? '' : 'border-bottom-color:' . $settings[ $category ]['border-bottom-color'] . ' ' . $important . ';';
		if ( ! empty( $settings[ $category ]['display'] ) && true === $settings[ $category ]['display'] ) {
			$input_styles .= 'display:none ' . $important . ';';
		}
		if ( ! empty( $settings[ $category ]['border-radius'] ) ) {
			$input_styles .= 'border-radius:' . $settings[ $category ]['border-radius'] . $this->sfwf_add_px_to_value( $settings[ $category ]['border-radius'] ) . ' ' . $important . ';';
			$input_styles .= '-web-border-radius:' . $settings[ $category ]['border-radius'] . $this->sfwf_add_px_to_value( $settings[ $category ]['border-radius'] ) . ' ' . $important . ';';
			$input_styles .= '-moz-border-radius:' . $settings[ $category ]['border-radius'] . $this->sfwf_add_px_to_value( $settings[ $category ]['border-radius'] ) . ' ' . $important . ';';
		}

		$input_styles .= empty( $settings[ $category ]['custom-css'] ) ? '' : $settings[ $category ]['custom-css'] . ';';
		return $input_styles;
	}

	/**
	 * Get saved styles for tablets
	 *
	 * @param  int    $form_id   id of the form for which the styles should be got.
	 * @param  string $category  the section for which styles should be taken.
	 * @param  string $important mark as important or not.
	 * @return string
	 */
	public function swfw_get_saved_styles_tab( $form_id, $category, $important ) {
		$settings = get_option( 'sfwf_form_id_' . $form_id );
		if ( empty( $settings ) ) {
			return;
		}
		$input_styles  = '';
		$input_styles .= empty( $settings[ $category ]['width-tab'] ) ? '' : 'width:' . $settings[ $category ]['width-tab'] . $this->sfwf_add_px_to_value( $settings[ $category ]['width-tab'] ) . ' ' . $important . ';';
		$input_styles .= empty( $settings[ $category ]['max-width-tab'] ) ? '' : 'width:' . $settings[ $category ]['max-width-tab'] . $this->sfwf_add_px_to_value( $settings[ $category ]['max-width-tab'] ) . ' ' . $important . ';';
		$input_styles .= empty( $settings[ $category ]['maximum-width-tab'] ) ? '' : 'width:' . $settings[ $category ]['maximum-width-tab'] . $this->sfwf_add_px_to_value( $settings[ $category ]['maximum-width-tab'] ) . ' ' . $important . ';';
		$input_styles .= empty( $settings[ $category ]['height-tab'] ) ? '' : 'height:' . $settings[ $category ]['height-tab'] . $this->sfwf_add_px_to_value( $settings[ $category ]['height-tab'] ) . ' ' . $important . ';';
		$input_styles .= empty( $settings[ $category ]['font-size-tab'] ) ? '' : 'font-size:' . $settings[ $category ]['font-size-tab'] . $this->sfwf_add_px_to_value( $settings[ $category ]['font-size-tab'] ) . ' ' . $important . ';';

		$input_styles .= empty( $settings[ $category ]['line-height-tab'] ) ? '' : 'line-height:' . $settings[ $category ]['line-height-tab'] . $important . ';';

		return $input_styles;
	}

	/**
	 * Get styles for phone
	 *
	 * @param  int    $form_id   id of the form for which the styles should be got.
	 * @param  string $category  the section for which styles should be taken.
	 * @param  string $important mark as important or not.
	 * @return string
	 */
	public function swfw_get_saved_styles_phone( $form_id, $category, $important ) {
		$settings = get_option( 'sfwf_form_id_' . $form_id );
		if ( empty( $settings ) ) {
			return;
		}
		$input_styles  = '';
		$input_styles .= empty( $settings[ $category ]['width-phone'] ) ? '' : 'width:' . $settings[ $category ]['width-phone'] . $this->sfwf_add_px_to_value( $settings[ $category ]['width-phone'] ) . ' ' . $important . ';';
		$input_styles .= empty( $settings[ $category ]['max-width-phone'] ) ? '' : 'width:' . $settings[ $category ]['max-width-phone'] . $this->sfwf_add_px_to_value( $settings[ $category ]['max-width-phone'] ) . ' ' . $important . ';';
		$input_styles .= empty( $settings[ $category ]['maximum-width-phone'] ) ? '' : 'width:' . $settings[ $category ]['maximum-width-phone'] . $this->sfwf_add_px_to_value( $settings[ $category ]['maximum-width-phone'] ) . ' ' . $important . ';';
		$input_styles .= empty( $settings[ $category ]['height-phone'] ) ? '' : 'height:' . $settings[ $category ]['height-phone'] . $this->sfwf_add_px_to_value( $settings[ $category ]['height-phone'] ) . ' ' . $important . ';';
		$input_styles .= empty( $settings[ $category ]['font-size-phone'] ) ? '' : 'font-size:' . $settings[ $category ]['font-size-phone'] . $this->sfwf_add_px_to_value( $settings[ $category ]['font-size-phone'] ) . ' ' . $important . ';';
		$input_styles .= empty( $settings[ $category ]['line-height-phone'] ) ? '' : 'line-height:' . $settings[ $category ]['line-height-phone'] . $important . ';';

		return $input_styles;
	}

	/**
	 * Add px to value if its missing.
	 *
	 * @param  string $value value to check for missing px.
	 * @return string
	 */
	public function sfwf_add_px_to_value( $value ) {

		if ( ! empty( $value ) && ctype_digit( $value ) ) {
			$value = 'px';
		} else {
			$value = '';
		}
		return $value;
	}

	/**
	 * When user is on a Styler for WPForms related admin page, display footer text
	 * that graciously asks them to rate us.
	 *
	 * @since 1.3.2
	 *
	 * @param string $text text to show.
	 *
	 * @return string
	 */
	public function admin_footer( $text ) {

		global $current_screen;

		if ( ! empty( $current_screen->id ) && strpos( $current_screen->id, 'styler-wpforms' ) !== false ) {
			$url  = 'https://wordpress.org/support/plugin/styler-for-wpforms/reviews/?filter=5#new-post';
			$text = sprintf(
				wp_kses(
					/* translators: $1$s - WPForms plugin name; $2$s - WP.org review link; $3$s - WP.org review link. */
					__( 'Please rate %1$s <a href="%2$s" target="_blank" rel="noopener noreferrer">&#9733;&#9733;&#9733;&#9733;&#9733;</a> on <a href="%3$s" target="_blank" rel="noopener">WordPress.org</a> to help us spread the word. Thank you from the Utimate Addons for WPForms team!', 'sk_sfwf' ),
					array(
						'a' => array(
							'href'   => array(),
							'target' => array(),
							'rel'    => array(),
						),
					)
				),
				'<strong>Utimate Addons for WPForms</strong>',
				$url,
				$url
			);
		}

		return $text;
	}
} // Class ends here.

add_action( 'plugins_loaded', 'sk_sfwf_initialize_main_class' );

/**
 * The main function responsible for returning The Highlander Plugin
 * Instance to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * @since  3.0
 * @return {class} Highlander Instance
 */
function sk_sfwf_initialize_main_class() {

	return Sk_Sfwf_Main_Class::instance();
}
