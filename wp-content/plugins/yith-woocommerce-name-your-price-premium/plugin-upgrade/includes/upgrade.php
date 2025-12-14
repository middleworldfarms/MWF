<?php // phpcs:ignore WordPress.NamingConventions
/**
 * This file belongs to the YIT Framework.
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 *
 * @author  YITH
 * @package YITH/PluginUpgrade
 */

declare( strict_types = 1 );

namespace YITH\PluginUpgrade;

defined( 'ABSPATH' ) || exit;  // Exit if accessed directly.

use Exception;
use stdClass;
use WP_Ajax_Upgrader_Skin;
use WP_CLI\UpgraderSkin;
use WP_Error;
use WP_Upgrader;
use YITH\PluginUpgrade\Legacy\Upgrade as UpgradeLegacy;
use const ABSPATH;
use const DAY_IN_SECONDS;
use const HOUR_IN_SECONDS;
use const PHP_URL_QUERY;

if ( ! class_exists( 'YITH\PluginUpgrade\Upgrade' ) ) :
	/**
	 * Upgrade plugin class.
	 * Handle download/update package
	 *
	 * @since   5.0.0
	 */
	class Upgrade extends UpgradeLegacy {

		/**
		 * Current plugin upgrading
		 *
		 * @var string
		 */
		protected $plugin_upgrading = '';

		/**
		 * The single instance of the class
		 *
		 * @since 5.0
		 * @var Upgrade
		 */
		protected static $instance = null;

		/**
		 * Main plugin Instance
		 *
		 * @since  5.0.0
		 * @return Upgrade Main instance
		 */
		public static function instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Class constructor
		 *
		 * @since 5.0.0
		 * @return void
		 */
		protected function __construct() {
			// plugins.php page customization.
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), 20 );
			add_action( 'wp_ajax_yith_plugin_fw_get_premium_changelog', array( $this, 'show_changelog_for_premium_plugins' ) );
			add_action( 'load-plugins.php', array( $this, 'remove_wp_plugin_update_row' ), 25 );
			// Handle update.
			add_action( 'pre_auto_update', array( $this, 'catch_plugin_upgrading' ), 10, 3 );
			add_filter( 'upgrader_pre_download', array( $this, 'upgrader_pre_download' ), 10, 3 );
			add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'check_update' ) );
			add_filter( 'pre_update_site_option_auto_update_plugins', array( $this, 'avoid_auto_update_bulk' ), 10, 4 );
			// Update multisite.
			add_filter( 'plugin_auto_update_setting_html', array( $this, 'hide_auto_update_multisite' ), 10, 2 );

			add_action( 'deactivated_plugin', array( $this, 'maybe_delete_update_plugins_transient' ) );
			add_action( 'self_admin_url', array( $this, 'details_plugin_url_in_update_core_page' ), 10, 2 );
			add_filter( 'site_transient_update_plugins', array( $this, 'filter_site_transient_update_plugins' ) );
		}

		/**
		 * Enqueue admin scripts
		 *
		 * @since    5.0.0
		 * @return void
		 */
		public function admin_enqueue_scripts() {
			global $pagenow;

			if ( 'plugins.php' !== $pagenow ) {
				return;
			}

			$suffix = defined( 'SCRIPT_DEBUG' ) && \SCRIPT_DEBUG ? '' : '.min';
			wp_enqueue_style( 'yith-upgrader', YITH_PLUGIN_UPGRADE_URL . '/assets/css/upgrader.css', array(), YITH_PLUGIN_UPGRADE_VERSION );
			wp_enqueue_script( 'yith-upgrader', YITH_PLUGIN_UPGRADE_URL . '/assets/js/upgrader' . $suffix . '.js', array( 'jquery' ), YITH_PLUGIN_UPGRADE_VERSION, true );

			wp_localize_script(
				'yith-upgrader',
				'yithUpgradePlugins',
				array(
					'ajaxUrl'  => admin_url( 'admin-ajax.php', 'relative' ),
					'security' => wp_create_nonce( 'updates' ),
					'l10n'     => array(
						/* translators: %s: Plugin name and version */
						'updating' => _x( 'Updating %s...', 'plugin-fw', 'yith-plugin-upgrade-fw' ), // No ellipsis.
						/* translators: %s: Plugin name and version */
						'updated'  => _x( '%s updated!', 'plugin-fw', 'yith-plugin-upgrade-fw' ),
						/* translators: %s: Plugin name and version */
						'failed'   => _x( '%s update failed', 'plugin-fw', 'yith-plugin-upgrade-fw' ),
					),
				)
			);
		}

		/**
		 * Show changelog for premium plugins
		 *
		 * @since 5.0.0
		 * @return void
		 */
		public function show_changelog_for_premium_plugins() {
			// phpcs:disable WordPress.Security.NonceVerification.Recommended
			if ( ! isset( $_GET['plugin'], $_GET['section'] ) || 'changelog' !== sanitize_text_field( wp_unslash( $_GET['section'] ) ) ) {
				return;
			}

			$plugin_init = sanitize_text_field( $_GET['plugin'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			if ( ! isset( $this->plugins[ $plugin_init ] ) ) {
				return;
			}

			// If no changelog is set, print error.
			if ( empty( $this->plugins[ $plugin_init ]['info']['changelog'] ) ) {
				$error    = esc_html__( 'An unexpected error occurred, please try again later. Thanks!', 'yith-plugin-upgrade-fw' );
				$template = YITH_PLUGIN_UPGRADE_PATH . '/templates/upgrade/error.php';
			} else {
				$plugin_name = $this->plugins[ $plugin_init ]['info']['Name'];
				$changelog   = $this->plugins[ $plugin_init ]['info']['changelog'];
				$template    = YITH_PLUGIN_UPGRADE_PATH . '/templates/upgrade/changelog.php';
			}

			include $template;
			die();
			// phpcs:enable WordPress.Security.NonceVerification.Recommended
		}

		/**
		 * Remove the standard plugin_update_row
		 * Remove the standard plugin_update_row and add a custom plugin update row in plugin page.
		 *
		 * @since  5.0.0
		 * @return void
		 */
		public function remove_wp_plugin_update_row() {
			foreach ( $this->plugins as $init => $plugin ) {
				remove_action( "after_plugin_row_$init", 'wp_plugin_update_row' );
				add_action( "after_plugin_row_$init", array( $this, 'plugin_update_row' ) );
			}
		}

		/**
		 * Get plugin update row message
		 *
		 * @since 5.0.0
		 * @param string $init      The plugin init.
		 * @param object $transient The update transient object.
		 * @return string
		 */
		public function get_plugin_update_row_message( string $init, object $transient ): string {

			$licence     = Licences::instance()->get_single_licence( $init );
			$plugin      = $this->plugins[ $init ];
			$details_url = $this->get_view_details_url( $init );

			$update_now_class = apply_filters( 'yith_plugin_fw_update_now_class', '' );
			$update_now_class = trim( $update_now_class . ' yith-update-link update-link' );

			$message = sprintf(
			// translators: %1$s, %3$s are placeholders for the plugin name, %2$s the link to open changelog modal, %4$s is the new plugin version.
				__( 'There is a new version of %1$s available. <a href="%2$s" class="thickbox yith-changelog-button open-plugin-details-modal" title="%3$s">View version %4$s details</a>', 'yith-plugin-upgrade-fw' ),
				$plugin['info']['Name'],
				esc_url( $details_url ),
				esc_attr( $plugin['info']['Name'] ),
				$transient->new_version
			);

			// If current user cannot update plugin, or we are in network admin and type is not YITH.
			if ( ! current_user_can( 'update_plugins' ) || ( is_network_admin() && 'yith' !== $licence->get_type() ) ) {
				return $message;
			}

			if ( is_network_admin() && ! $this->is_enabled_in_all_blogs( $init ) ) {
				$message = sprintf(
				// translators: %1$s, %3$s are placeholders for the plugin name, %2$s the link to open changelog modal, %4$s is the new plugin version.
					__( 'There is a new version of %1$s available. <a href="%2$s" class="thickbox yit-changelog-button open-plugin-details-modal" title="%3$s">View version %4$s details</a>. <em>Make sure the plugin license has been activated on each site of the network to benefits from automatic updates.</em>', 'yith-plugin-upgrade-fw' ),
					$this->plugins[ $init ]['info']['Name'],
					esc_url( $details_url ),
					esc_attr( $this->plugins[ $init ]['info']['Name'] ),
					$transient->new_version
				);
			} elseif ( 'yith' === $licence->get_type() && ! $licence->is_activated() ) {
				$message = sprintf(
				// translators: %1$s, %3$s, %6$s are placeholders for the plugin name, %2$s the link to open changelog modal, %4$s is the new plugin version, %5$s is the link to activation page.
					__( 'There is a new version of %1$s available. <a href="%2$s" class="thickbox yit-changelog-button open-plugin-details-modal" title="%3$s">View version %4$s details</a>. <em>Automatic update is unavailable for this plugin, please <a href="%5$s" title="License activation">activate</a> your copy of %1$s.</em>', 'yith-plugin-upgrade-fw' ),
					$this->plugins[ $init ]['info']['Name'],
					esc_url( $details_url ),
					esc_attr( $this->plugins[ $init ]['info']['Name'] ),
					$transient->new_version,
					Licences::instance()->get_admin_panel()->get_url()
				);
			} elseif ( $licence->is_activated() ) {
				$message = sprintf(
					__( 'There is a new version of %1$s available. <a href="%2$s" class="thickbox yit-changelog-button open-plugin-details-modal" title="%3$s">View version %4$s details</a> or <a href="%5$s" class="%6$s" data-plugin="%7$s" data-slug="%8$s" data-name="%1$s">update now</a>.', 'yith-plugin-upgrade-fw' ),
					$this->plugins[ $init ]['info']['Name'],
					esc_url( $details_url ),
					esc_attr( $this->plugins[ $init ]['info']['Name'] ),
					$transient->new_version,
					wp_nonce_url( self_admin_url( "update.php?action=upgrade-plugin&plugin=$init" ), 'upgrade-plugin_' . $init ),
					$update_now_class,
					$init,
					$this->plugins[ $init ]['slug']
				);
			}

			// Maybe print version error for YITH licences.
			if ( 'yith' === $licence->get_type() && version_compare( $this->plugins[ $init ]['info']['Version'], $transient->new_version, '>' ) ) {
				$message .= sprintf(
					__( '<br/><b>Please note:</b> You are using a higher version than the latest available one. </em>Please, make sure you\'ve downloaded the latest version of <em>%1$s</em> from the only <a href="https://yithemes.com" target="_blank">YITH official website</a>, specifically, from your <a href="https://yithemes.com/my-account/recent-downloads/" target="_blank">Downloads page</a>. This is the only way to be sure the version you are using is 100%% malware-free.', 'yith-plugin-upgrade-fw' ),
					$this->plugins[ $init ]['info']['Name'],
					esc_url( $details_url ),
					esc_attr( $this->plugins[ $init ]['info']['Name'] ),
					$transient->new_version,
					Licences::instance()->get_admin_panel()->get_url(),
					$this->plugins[ $init ]['info']['Name']
				);
			}

			return $message;
		}

		/**
		 * Add the plugin update row in plugin page
		 *
		 * @since 5.0.0
		 * @return void
		 */
		public function plugin_update_row() {
			$current = get_site_transient( 'update_plugins' );
			$init    = str_replace( 'after_plugin_row_', '', current_filter() );
			if ( ! isset( $current->response[ $init ] ) ) {
				return;
			}

			$wp_list_table = _get_list_table( 'WP_MS_Themes_List_Table' );
			$message       = $this->get_plugin_update_row_message( $init, $current->response[ $init ] );

			include YITH_PLUGIN_UPGRADE_PATH . '/templates/upgrade/update-row.php';
		}

		/**
		 * Get the view details url for premium plugins
		 *
		 * @since 5.0.0
		 * @param string $init The plugin init file.
		 * @return string View details url for plugins.
		 */
		public function get_view_details_url( string $init ): string {
			return admin_url( 'admin-ajax.php?action=yith_plugin_fw_get_premium_changelog&tab=plugin-information&plugin=' . $init . '&section=changelog&TB_iframe=true&width=640&height=662' );
		}

		/**
		 * Avoid active auto update bulk for plugin that has auto update disabled
		 *
		 * @since  5.0.0
		 * @param mixed  $value      New value of the network option.
		 * @param mixed  $old_value  Old value of the network option.
		 * @param string $option     Option name.
		 * @param int    $network_id ID of the network.
		 * @return mixed
		 */
		public function avoid_auto_update_bulk( $value, $old_value, $option, $network_id ) {
			return array_filter(
				$value,
				function ( $p ) {
					$licence = Licences::instance()->get_single_licence( $p );
					return empty( $this->plugins[ $p ] ) || ( is_plugin_active( $p ) && $licence && $licence->is_activated() );
				}
			);
		}

		/**
		 * Register plugin for upgrade process.
		 *
		 * @since 5.0.0
		 * @param string $plugin_slug The plugin slug.
		 * @param string $plugin_init The plugin init file.
		 * @return void
		 */
		public function register( string $plugin_slug, string $plugin_init ) {

			if ( ! function_exists( 'get_plugins' ) ) {
				include_once ABSPATH . 'wp-admin/includes/plugin.php';
			}

			$plugins     = get_plugins();
			$plugin_info = $plugins[ $plugin_init ];

			// Register plugin data.
			$this->plugins[ $plugin_init ] = array(
				'info' => $plugin_info,
				'slug' => $plugin_slug,
			);

			// Add remote info.
			$info = $this->get_remote_info( $plugin_init );
			$this->plugins[ $plugin_init ]['info']['Latest']    = $info['latest'] ?? '';
			$this->plugins[ $plugin_init ]['info']['changelog'] = $info['changelog'] ?? '';
		}

		/**
		 * Catch YITH plugin upgrading
		 *
		 * @since  5.0.0
		 * @param string $type    The type of update being checked: 'core', 'theme', 'plugin', or 'translation'.
		 * @param object $item    The update offer.
		 * @param string $context The filesystem context (a path) against which filesystem access and status
		 *                        should be checked.
		 * @return void
		 */
		public function catch_plugin_upgrading( $type, $item, $context ) {
			if ( 'plugin' !== $type || empty( $item->plugin ) ) {
				return;
			}
			$this->plugin_upgrading = $item->plugin;
		}

		/**
		 * Hide auto update on multisite for plugin. The update will be available only on single blog with active licence
		 *
		 * @since  5.0.0
		 * @param string $html        The HTML of the plugin's auto-update column content, including
		 *                            toggle auto-update action links and time to next update.
		 * @param string $plugin_init Path to the plugin file relative to the plugins' directory.
		 * @return string
		 */
		public function hide_auto_update_multisite( string $html, string $plugin_init ): string {
			return ( is_multisite() && ! empty( $this->plugins[ $plugin_init ] ) ) ? '' : $html;
		}

		/**
		 * Filter the View Details url in update core page
		 *
		 * @since 5.0.0
		 * @param string $url  The complete URL including scheme and path.
		 * @param string $path Path relative to the URL. Blank string if no path is specified.
		 * @return string Admin URL link with optional path appended.
		 */
		public function details_plugin_url_in_update_core_page( string $url, string $path ): string {
			global $pagenow;

			// In plugins.php page use the filter after_plugin_row_{plugin_init} instead.
			if ( 'plugins.php' !== $pagenow && strpos( $path, 'plugin-install.php' ) === 0 ) {
				$parsed_url = wp_parse_url( $url, PHP_URL_QUERY );
				$query_args = array();

				if ( ! ! $parsed_url ) {
					parse_str( $parsed_url, $query_args );
				}

				$tab     = $query_args['tab'] ?? '';
				$plugin  = $query_args['plugin'] ?? '';
				$section = $query_args['section'] ?? '';

				if ( ! ! $plugin && 'plugin-information' === $tab && 'changelog' === $section ) {
					$transient = 'yith_update_core_plugins_list';
					$plugins   = get_transient( $transient );

					if ( empty( $plugins ) || count( $plugins ) !== count( YITH_Plugin_Licence()->get_products() ) ) {
						$plugins = array_flip( wp_list_pluck( YITH_Plugin_Licence()->get_products(), 'product_id' ) );
						set_transient( $transient, $plugins, DAY_IN_SECONDS );
					}

					if ( isset( $plugins[ $plugin ] ) ) {
						$url = $this->get_view_details_url( $plugins[ $plugin ] );
					}
				}
			}

			return $url;
		}

		/**
		 * Check for plugins update
		 * If a new plugin version is available set it in the pre_set_site_transient_update_plugins hooks
		 *
		 * @since  5.0.0
		 * @param mixed $transient update_plugins transient value.
		 * @param bool  $save      Default: false. Set true to regenerate the update_transient plugins.
		 * @return mixed $transient | The new update_plugins transient value
		 * @see    update_plugins transient and pre_set_site_transient_update_plugins hooks
		 */
		public function check_update( $transient, bool $save = false ) {

			// Double check that transient ia a stdClass object.
			if ( ! is_object( $transient ) ) {
				return $transient;
			}

			foreach ( $this->plugins as $init => $plugin ) {

				$licence = Licences::instance()->get_single_licence( $init );
				if ( empty( $licence ) ) {
					continue;
				}

				$update_data  = $this->get_update_data( $init );
				$is_activated = $licence->is_activated();

				$item = array(
					'id'            => $init,
					'plugin'        => $init,
					'slug'          => $plugin['slug'],
					'new_version'   => $plugin['info']['Version'],
					'url'           => '',
					'package'       => '',
					'icons'         => array(),
					'banners'       => array(),
					'banners_rtl'   => array(),
					'tested'        => '',
					'requires_php'  => '',
					'compatibility' => new stdClass(),
				);

				// If not activated disable auto update.
				if ( ! $is_activated ) {
					$item['auto-update-forced'] = false;
				}

				if ( ! empty( $update_data ) ) {
					$wp_version = preg_replace( '/-.*$/', '', get_bloginfo( 'version' ) );
					if ( strpos( $wp_version, $update_data['tested_up_to'] ) !== false ) {
						$core_updates                = function_exists( 'get_core_updates' ) ? get_core_updates() : false;
						$update_data['tested_up_to'] = false !== $core_updates && ! empty( $core_updates[0]->current ) ? $core_updates[0]->current : $wp_version;
					}

					// Merge default item with the plugin data.
					$item = array_merge(
						$item,
						array(
							'new_version' => (string) $update_data['latest'],
							'changelog'   => (string) $update_data['changelog'],
							'package'     => '',
							'icons'       => ! empty( $update_data['icons'] ) ? (array) $update_data['icons'] : array(),
							'tested'      => $update_data['tested_up_to'],
						)
					);

					$transient->response[ $init ] = (object) $item;
				} else {
					// Adding the "mock" item to the `no_update` property is required
					// for the enable/disable auto-updates links to correctly appear in UI.
					$transient->no_update[ $init ] = (object) $item;
				}
			}

			$save && set_site_transient( 'update_plugins', $transient );
			return $transient;
		}

		/**
		 * Retrieve the zip package file
		 *
		 * @since  5.0.0
		 * @param mixed       $reply    Whether to bail without returning the package. Default false.
		 * @param string      $package  The package file name.
		 * @param WP_Upgrader $upgrader WP_Upgrader instance.
		 * @return string|WP_Error
		 * @see    wp-admin/includes/class-wp-upgrader.php
		 */
		public function upgrader_pre_download( $reply, $package, $upgrader ) {

			$plugin_init = $this->get_plugin_upgrading( $upgrader );
			if ( empty( $plugin_init ) || ! isset( $this->plugins[ $plugin_init ] ) ) {
				return false;
			}

			$licence = Licences::instance()->get_single_licence( $plugin_init );
			if ( empty( $licence ) || ! $licence->is_activated() ) {
				return new WP_Error( 'licence_not_valid', esc_html_x( 'You have to activate the plugin to benefit from automatic updates.', '[Update Plugin Message: License not enabled]', 'yith-plugin-upgrade-fw' ) );
			}
			$upgrader->skin->feedback( 'downloading_package', esc_html__( 'YITH Repository', 'yith-plugin-upgrade-fw' ) );
			$download_file = $this->download( $licence );

			// Regenerate update_plugins transient.
			$this->delete_update_plugins_transient();
			if ( is_wp_error( $download_file ) ) {
				return new WP_Error( 'download_failed', $upgrader->strings['download_failed'], $download_file->get_error_message() );
			}

			return $download_file;
		}

		/**
		 * Get current plugin upgrading
		 *
		 * @since  5.0.0
		 * @param WP_Upgrader $upgrader WP_Upgrader instance.
		 * @return string
		 */
		public function get_plugin_upgrading( WP_Upgrader $upgrader ): string {
			if ( ! empty( $this->plugin_upgrading ) ) {
				return $this->plugin_upgrading;
			}

			$plugin_upgrading = '';
			// WordPress 4.9 or greater support.
			$is_bulk      = $upgrader->skin instanceof \Bulk_Plugin_Upgrader_Skin;
			$is_bulk_ajax = $upgrader->skin instanceof WP_Ajax_Upgrader_Skin;
			// WP-CLI support.
			$is_wp_cli = $upgrader->skin instanceof UpgraderSkin;
			// ManageWP support.
			$is_manage_wp = $upgrader->skin instanceof \MWP_Updater_TraceableUpdaterSkin;

			if ( $is_wp_cli || $is_manage_wp ) {
				$plugin_info_name = $upgrader->skin->plugin_info['Name'] ?? '';
				if ( ! empty( $plugin_info_name ) ) {
					$plugins = Licences::instance()->get_products();
					foreach ( $plugins as $init => $info ) {
						if ( html_entity_decode( $plugin_info_name ) === html_entity_decode( $info['Name'] ) ) {
							$plugin_upgrading = $init;
							break;
						}
					}
				}
			} elseif ( ! $is_bulk && ! $is_bulk_ajax ) {
				// Bulk Action: Support for old WordPress Version.
				$plugin_upgrading = $upgrader->skin->plugin ?? '';
			} elseif ( $is_bulk_ajax ) {
				// Bulk Update for WordPress 4.9 or greater.
				if ( ! empty( $_POST['plugin'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.NonceVerification.Missing
					$plugin_upgrading = plugin_basename( sanitize_text_field( wp_unslash( $_POST['plugin'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.NonceVerification.Missing
				}
			} else {
				// Bulk action upgrade.
				$action_url = wp_parse_url( $upgrader->skin->options['url'] );
				parse_str( rawurldecode( htmlspecialchars_decode( $action_url['query'] ) ), $output );
				$plugins = $output['plugins'] ?? '';
				$plugins = explode( ',', $plugins );
				foreach ( $plugins as $plugin_init ) {
					$to_upgrade = get_plugin_data( \WP_PLUGIN_DIR . '/' . $plugin_init );
					if ( $to_upgrade['Name'] === $upgrader->skin->plugin_info['Name'] ) {
						$plugin_upgrading = $plugin_init;
					}
				}
			}

			return $plugin_upgrading;
		}

		/**
		 * Delete the update plugins transient
		 *
		 * @since  1.0
		 * @author Andrea Grillo <andrea.grillo@yithemes.com>
		 * @return void
		 * @see    update_plugins transient and pre_set_site_transient_update_plugins hooks
		 */
		public function delete_update_plugins_transient() {
			delete_site_transient( 'update_plugins' );
		}

		/**
		 * Maybe Delete the update plugins transient
		 *
		 * @since      5.0.0
		 * @param string $plugin The plugin init file.
		 * @return void
		 */
		public function maybe_delete_update_plugins_transient( string $plugin ) {
			if ( isset( $this->plugins[ $plugin ] ) ) {
				$this->delete_update_plugins_transient();
			}
		}

		/**
		 * Fix the view details url in plugins.php page.
		 * Prevent to update the plugins in update-core page if not enabled in all networks
		 *
		 * @since  5.0.0
		 * @param object $update_plugins Current updating plugin data object.
		 * @return mixed $update_plugins filtered transient value
		 * @see    site_transient_update_plugins filter
		 */
		public function filter_site_transient_update_plugins( $update_plugins ) {
			global $pagenow;

			if ( 'plugins.php' === $pagenow || 'update-core.php' === $pagenow ) {
				$yith_plugins = array_keys( Licences::instance()->get_licences() );
				foreach ( $yith_plugins as $init ) {
					if ( 'plugins.php' === $pagenow ) {
						unset( $update_plugins->response[ $init ]->slug );
						unset( $update_plugins->no_update[ $init ]->slug );

					} elseif ( 'update-core.php' === $pagenow && ! $this->is_enabled_in_all_blogs( $init ) ) {
						unset( $update_plugins->response[ $init ] );
					}
				}
			}

			return $update_plugins;
		}

		/**
		 * Check if given plugin is enabled in all blog.
		 *
		 * @since 5.0.0
		 * @param string $plugin_init The plugin init.
		 * @return bool True if the plugin is enabled in all blogs, false otherwise.
		 */
		public function is_enabled_in_all_blogs( string $plugin_init ): bool {
			if ( ! is_multisite() ) {
				$licence = Licences::instance()->get_single_licence( $plugin_init );
				return $licence && $licence->is_activated();
			} else {
				return ! empty( Licences::instance()->get_network_handler() ) && Licences::instance()->get_network_handler()->is_activated_in_all_blogs( $plugin_init );
			}
		}

		/**
		 * Check if plugin update is available and return remote info
		 *
		 * @since  5.0.0
		 * @param string $plugin_init The plugin init.
		 * @return array
		 */
		protected function get_update_data( string $plugin_init ): array {
			$remote_info = $this->get_remote_info( $plugin_init );
			return ( ! empty( $remote_info['latest'] ) && ! version_compare( $this->plugins[ $plugin_init ]['info']['Version'], $remote_info['latest'], '=' ) ) ? $remote_info : array();
		}

		/**
		 * Download plugin package
		 *
		 * @since 5.0.0
		 * @param Licence $licence The licence instance.
		 * @return string|WP_Error
		 * @throws Exception Error downloading plugin package.
		 */
		protected function download( Licence $licence ) {

			try {

				$package_url = $licence->get_download_url();
				if ( ! $package_url ) {
					throw new Exception( esc_html__( 'Invalid URL Provided.', 'yith-plugin-upgrade-fw' ) );
				}

				$tmpfname = wp_tempnam( md5( $package_url ) ); // get unique filename.
				$args     = apply_filters(
					'yith_plugin_upgrade_download_request_args',
					array(
						'timeout'  => 300,
						'stream'   => true,
						'filename' => $tmpfname,
					)
				);

				if ( ! $tmpfname ) {
					throw new Exception( esc_html__( 'Could not create Temporary file.', 'yith-plugin-upgrade-fw' ) );
				}

				add_filter( 'block_local_requests', '__return_false' );

				$response = wp_safe_remote_get( $package_url, $args );
				if ( is_wp_error( $response ) ) {
					throw new Exception( $response->get_error_message() );
				}

				$response_code = intval( wp_remote_retrieve_response_code( $response ) );
				// Firstly we check if server gives a 404 error. In this case the upgrade won't check on backup system.
				if ( 404 === $response_code ) {
					throw new Exception( trim( wp_remote_retrieve_response_message( $response ) ) );

				} elseif ( 200 !== $response_code ) {
					// If the error code is not 404 but neither a 200 then the upgrade will check on backup system.
					$url      = add_query_arg( array( 'request' => 'download' ), $licence->get_download_url( true ) );
					$response = wp_safe_remote_get( $url, $args );

					if ( is_wp_error( $response ) || 200 !== intval( wp_remote_retrieve_response_code( $response ) ) ) {
						// If errors persists also on backup system then we throw an error.
						throw new Exception( trim( wp_remote_retrieve_response_message( $response ) ) );
					}
				}

				$content_md5 = wp_remote_retrieve_header( $response, 'content-md5' );
				if ( $content_md5 ) {
					$md5_check = verify_file_md5( $tmpfname, $content_md5 );
					if ( is_wp_error( $md5_check ) ) {
						throw new Exception( $md5_check->get_error_message() );
					}
				}

				return $tmpfname;

			} catch ( Exception $e ) {
				Debug::log( "Error downloading package for $licence->product_id: {$e->getMessage()}" );
				empty( $tmpfname ) || wp_delete_file( $tmpfname );

				return new WP_Error( 'download_failed', $e->getMessage() );
			}
		}

		/**
		 * Get remote product info
		 *
		 * @since 5.0.0
		 * @param string $plugin_init THe plugin init.
		 * @return array
		 * @throws Exception Error getting remote plugin info.
		 */
		protected function get_remote_info( string $plugin_init ): array {

			try {
				// Search first on cache.
				$transient = 'yith_register_' . md5( $plugin_init );
				$info      = get_transient( $transient );

				if ( false === $info || apply_filters( 'yith_register_delete_transient', false ) ) {
					$info       = array();
					$xml        = $this->get_xml_url( $plugin_init );
					$remote_xml = wp_remote_get( $xml );

					if ( is_wp_error( $remote_xml ) || ! isset( $remote_xml['response']['code'] ) || 200 !== intval( $remote_xml['response']['code'] ) ) {
						throw new Exception( sprintf( 'Error get remote XML info for %s', $plugin_init ) );
					}

					$plugin_remote_info = function_exists( 'simplexml_load_string' ) ? @simplexml_load_string( $remote_xml['body'] ) : false; // phpcs:ignore
					if ( empty( $plugin_remote_info ) ) {
						throw new Exception( sprintf( 'SimpleXML error in %s:%s [plugin init: %s]', __FILE__, __FUNCTION__, $plugin_init ) );
					}

					$tested_up_to = (string) str_replace( '.x', '', (string) $plugin_remote_info->{'up-to'} );
					$tested_up_to = preg_replace( '/-.*$/', '', $tested_up_to );

					// Check if a set of icons is available for this plugin.
					$preferred_icons = array( 'svg', '2x', '1x', 'default' );
					$icons           = array();
					foreach ( $preferred_icons as $icon ) {
						if ( ! empty( $plugin_remote_info->icons->$icon ) ) {
							$icons[ $icon ] = esc_url_raw( (string) $plugin_remote_info->icons->$icon );
						}
					}

					$info = array(
						'latest'       => (string) $plugin_remote_info->latest,
						'icons'        => $icons,
						'tested_up_to' => $tested_up_to,
						'changelog'    => (string) $plugin_remote_info->changelog,
					);

					set_transient( $transient, $info, DAY_IN_SECONDS );
				}
			} catch ( Exception $e ) {
				Debug::log( $e->getMessage() );
				set_transient( $transient, array(), HOUR_IN_SECONDS );
			} finally {
				return $info;
			}
		}

		/**
		 * Retrieve the remote url with query string args
		 *
		 * @since 5.0.0
		 * @param string $plugin_init The plugin init.
		 * @return string The remote xml url
		 */
		protected function get_xml_url( string $plugin_init ): string {
			$args = array(
				'plugin'                => isset( $this->plugins[ $plugin_init ] ) ? $this->plugins[ $plugin_init ]['slug'] : false,
				'instance'              => md5( $_SERVER['SERVER_NAME'] ?? get_bloginfo( 'url' ) ),
				'licence'               => '',
				'is_membership_licence' => false,
				'server_ip'             => isset( $_SERVER['SERVER_NAME'] ) ? gethostbyname( $_SERVER['SERVER_NAME'] ) : '127.0.0.1',
				'version'               => $this->plugins[ $plugin_init ]['info']['Version'] ?? '1.0.0',
				'locale'                => function_exists( 'get_locale' ) ? get_locale() : 'en_US',
			);

			$licence = Licences::instance()->get_single_licence( $plugin_init );
			if ( ! empty( $licence ) && $licence->is_activated() ) {
				$args['licence']               = $licence->licence_key;
				$args['is_membership_licence'] = ! ! $licence->is_membership;
			}

			return add_query_arg( apply_filters( 'yith_get_remove_url_args', $args ), $this->remote_url );
		}
	}
endif;
