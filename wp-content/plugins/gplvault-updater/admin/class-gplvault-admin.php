<?php

defined( 'ABSPATH' ) || exit;

class GPLVault_Admin {
	const SLUG_SETTINGS  = 'gplvault_settings';
	const SLUG_PLUGINS   = 'gplvault_plugins';
	const SLUG_SYSTEM    = 'gplvault_system';
	const SLUG_THEME     = 'gplvault_themes';
	const SLUG_LOGS      = 'gplvault_logs';
	const SLUG_BLACKLIST = 'gplvault_blacklists';

	const UPDATES_KEY_PLUGINS = 'gv_plugin_updates';
	const UPDATES_KEY_THEMES  = 'gv_theme_updates';
	const LIFETIME_URL        = 'https://www.gplvault.com/pricing/';

	/**
	 * @var GPLVault_Admin|null
	 */
	protected static $singleton = null;

	private static $initiated = false;

	/**
	 * @var GPLVault_Settings_Manager
	 */
	protected $settings;

	protected $tab_settings = array();

	/**
	 * @var GPLVault_API_Manager $api
	 */
	protected $api;

	/**
	 * @return GPLVault_Admin
	 */
	public static function instance() {
		if ( is_null( self::$singleton ) ) {
			self::$singleton = new self();
		}

		return self::$singleton;
	}

	/**
	 * @return array
	 */
	private function get_ajax_bindings() {
		return array(
			'license_activation'   => array( self::instance(), 'activate_license' ),
			'license_deactivation' => array( self::instance(), 'deactivate_license' ),
			'check_license'        => array( self::instance(), 'check_license' ),
			'cleanup_settings'     => array( self::instance(), 'cleanup_settings' ),
			'plugins_exclusion'    => array( self::instance(), 'exclude_plugins' ),
			'themes_exclusion'     => array( self::instance(), 'exclude_themes' ),
		);
	}

	private function __clone() {}

	public function __wakeup() {}

	private function __construct() {
		$this->load_dep();
	}

	public function init() {
		if ( ! is_admin() ) {
			return;
		}
		$this->init_hooks();
	}

	private function init_hooks() {
		if ( true === self::$initiated ) {
			return;
		}
		self::$initiated = true;

		add_action( 'admin_init', array( $this, 'handle_log_actions' ) );

		add_action( 'admin_notices', array( __CLASS__, 'inject_before_notices' ), -9999 );
		add_action( 'admin_notices', array( __CLASS__, 'inject_after_notices' ), PHP_INT_MAX );

		add_action( 'in_admin_header', array( $this, 'render_header' ) );
		add_action( 'in_plugin_update_message-' . GPLVault()->plugin_basename(), array( $this, 'plugin_update_message' ), 10, 2 );
		if ( is_multisite() ) {
			add_action( 'network_admin_menu', array( $this, 'admin_menu' ) );
			add_action( 'network_admin_menu', array( $this, 'admin_menu_change_name' ), 200 );

			if ( ! $this->settings->is_subscription_lifetime() ) {
				add_action( 'network_admin_menu', array( $this, 'admin_menu_lifetime_link' ), 200 );
				add_action( 'admin_head', array( $this, 'admin_menu_js_css' ) );
			}

			add_filter( 'network_admin_plugin_action_links_' . GPLVault()->plugin_basename(), array( $this, 'actions_links' ) );
			if ( is_network_admin() ) {
				add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 2 );
			}
		} else {
			add_action( 'admin_menu', array( $this, 'admin_menu' ) );
			add_action( 'admin_menu', array( $this, 'admin_menu_change_name' ), 200 );
			if ( ! $this->settings->is_subscription_lifetime() ) {
				add_action( 'admin_menu', array( $this, 'admin_menu_lifetime_link' ), 200 );
				add_action( 'admin_head', array( $this, 'admin_menu_js_css' ) );
			}
			add_filter( 'plugin_action_links_' . GPLVault()->plugin_basename(), array( $this, 'actions_links' ) );
			add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 2 );
		}

		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
		add_action( 'in_admin_footer', array( $this, 'in_admin_footer' ) );

		add_action( 'admin_print_styles', array( $this, 'initial_notices' ) );
		add_filter( 'admin_body_class', array( $this, 'admin_body_class' ) );
		add_action( 'gv_license_deactivated', array( $this, 'act_on_deactivation' ) );
		add_action( 'gv_api_license_activated', array( $this, 'license_activated' ), 10, 1 );
		add_action( 'gv_api_license_activated', array( $this, 'gv_update_plugins' ), 998 );
		add_action( 'gv_api_license_activated', array( $this, 'gv_update_themes' ), 998 );

		add_filter( 'gv_ajax_bindings', array( $this, 'ajax_bindings' ) );
		add_action( 'admin_footer', array( $this, 'admin_js_template' ) );
		add_action( 'upgrader_process_complete', array( $this, 'upgrader_process_handler' ), 10, 2 );
		add_action( 'current_screen', array( $this, 'add_help_tabs' ) );
	}

	public function upgrader_process_handler( $upgrader, $options ) {
		if ( ! isset( $options['type'] ) ) {
			self::gv_update_plugins();
			self::gv_update_themes();
		} elseif ( 'plugin' === $options['type'] ) {
				self::gv_update_plugins();
		} elseif ( 'theme' === $options['type'] ) {
			self::gv_update_themes();
		}
	}

	private function load_dep() {
		if ( ! class_exists( 'GPLVault_API_Manager' ) ) {
			require_once GPLVault()->includes_path( '/api/class-gplvault-api-manager.php' );
		}

		$this->settings = gv_settings_manager();
		$this->api      = GPLVault_API_Manager::instance();
	}

	public function act_on_deactivation() {
		delete_site_transient( self::UPDATES_KEY_PLUGINS );
		delete_site_transient( self::UPDATES_KEY_THEMES );

		gv_settings_manager()->disable_activation_status();
	}

	public function license_activated( $server_response ) {
		$status_data              = $server_response['data'] ?? array();
		$status_data['activated'] = $server_response['activated'];
		gv_settings_manager()->save_license_status( $status_data );
	}

	public function admin_js_template() {
		if ( self::is_admin_page() ) {
			require_once GPLVault()->path( '/views/js-templates.php' );
		}
	}

	public function admin_body_class( $admin_body_class = '' ) {
		if ( ! self::is_admin_page() ) {
			return $admin_body_class;
		}
		$classes = explode( ' ', trim( $admin_body_class ) );

		$classes[]        = 'gv-admin-page';
		$admin_body_class = implode( ' ', $classes );
		return " $admin_body_class ";
	}

	public function plugin_row_meta( $links, $file ) {
		if ( GPLVault()->plugin_basename() === $file ) {
			$row_meta = array(
				'docs'    => '<a href="' . esc_url( apply_filters( 'gplvault_docs_url', 'https://www.gplvault.com/faq' ) ) . '" aria-label="' . esc_attr__( 'View GPL Vault documentation', 'gplvault' ) . '">' . esc_html__( 'Docs', 'gplvault' ) . '</a>',
				'support' => '<a href="' . esc_url( apply_filters( 'gplvault_support_url', 'https://www.gplvault.com/contact' ) ) . '" aria-label="' . esc_attr__( 'Visit customer support', 'gplvault' ) . '">' . esc_html__( 'Support', 'gplvault' ) . '</a>',
			);

			return array_merge( $links, $row_meta );
		}

		return (array) $links;
	}

	public function actions_links( $links ) {
		$admin_path    = 'admin.php?page=' . self::SLUG_SETTINGS;
		$settings_link = '<a href="' . self_admin_url( $admin_path ) . '">' . __( 'Settings', 'gplvault' ) . '</a>';

		array_unshift( $links, $settings_link );

		return $links;
	}

	public function in_admin_footer() {
		if ( GPLVault_Util::is_gplvault_area() ) { ?>
			<div class="gv_admin_note">
				<p><strong><?php esc_html_e( 'Help &amp; Support', 'gplvault' ); ?></strong>:
					<?php
					esc_html_e( 'For questions, technical support, or feedback regarding the Updater Plugin, please log into your account and use the help widget in the bottom right corner of your screen.', 'gplvault' );
					?>
				</p>
			<p><strong><?php esc_html_e( 'Interested in partnership opportunities?', 'gplvault' ); ?></strong> 
				<?php
				/* translators: %1$s: URL to the affiliate area */
				printf( esc_html__( 'Join our %1$s', 'gplvault' ), '<strong><a href="https://www.gplvault.com/affiliate-area/" target="_blank">Affiliate Network</a></strong>' );
				?>
			</p>
			</div>

			<?php
		}
	}

	public function plugin_update_message( $plugin_data, $update_response ) {
		$this->update_message( GPLVault()->version(), $plugin_data['new_version'] );
	}

	private function update_message( $current_version, $update_version ) {
		$current_version_parts  = explode( '.', $current_version );
		$update_version_parts   = explode( '.', $update_version );
		$current_version_majors = $current_version_parts[0] . '.' . $current_version_parts[1];
		$update_version_majors  = $update_version_parts[0] . '.' . $update_version_parts[1];

		if ( ! version_compare( $update_version_majors, $current_version_majors, '>' ) ) {
			return;
		}
		?>
		<hr class="gv-update-info__separator" />
		<div class="gv-update-info">
			<div class="gv-update-info__icon">
				<i class="dashicons dashicons-megaphone"></i>
			</div>
			<div>
				<div class="gv-update-info__title">
					<?php esc_html_e( 'Heads Up! Please take backup before update.', 'gplvault' ); ?>
				</div>
				<div class="gv-update-info__message">
					<?php
					/* translators: %1$s: URL to the settings page */
					printf( __( 'The latest update includes substantial changes across different areas of the plugin. We highly recommend you backup your sites before upgrades and take a look at the "Help" tab on the <a href="%1$s" target="_blank">Settings</a> page after upgrading the plugin.', 'gplvault' ), self_admin_url( 'admin.php?page=' . self::SLUG_SETTINGS ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					?>
				</div>
			</div>
		</div>
		<?php
	}

	public function admin_scripts( $hook ) {

		wp_register_script( 'gv-select2', GPLVault()->admin_assets_url( '/scripts/select2.min.js' ), array( 'jquery' ), '4.1.0-rc.0', true );
		wp_register_script( 'gv-popper', GPLVault()->admin_assets_url( '/scripts/popper.min.js' ), array(), '2.9.2', true );
		wp_register_script( 'gv-tippy', GPLVault()->admin_assets_url( '/scripts/tippy-bundle.umd.min.js' ), array( 'gv-popper' ), '6.3.1', true );
		wp_register_script( 'gv-polipop', GPLVault()->admin_assets_url( '/scripts/polipop.min.js' ), array( 'jquery', 'wp-util', 'wp-sanitize', 'wp-i18n', 'wp-a11y', 'wp-sanitize', 'gv-tippy', 'gv-select2' ), GPLVault()->version(), true );
		wp_register_script( 'gv-common', GPLVault()->admin_assets_url( '/scripts/gv-common-caee8f45.js' ), array( 'jquery', 'wp-util', 'wp-sanitize', 'wp-i18n', 'wp-a11y', 'wp-sanitize', 'gv-polipop', 'gv-tippy', 'gv-select2' ), GPLVault()->version(), true );
		wp_register_script( 'gv-settings', GPLVault()->admin_assets_url( '/scripts/gv-settings-caee8f45.js' ), array( 'gv-common' ), GPLVault()->version(), true );

		wp_register_style( 'gv-polipop-core', GPLVault()->admin_assets_url( '/styles/polipop.core.min.css' ), array(), '1.0.0-master' );
		wp_register_style( 'gv-polipop-default', GPLVault()->admin_assets_url( '/styles/polipop.default.min.css' ), array( 'gv-polipop-core' ), '1.0.0-master' );
		wp_register_style( 'gv-polipop-compact', GPLVault()->admin_assets_url( '/styles/polipop.compact.min.css' ), array( 'gv-polipop-core' ), '1.0.0-master' );
		wp_register_style( 'gv-polipop-minimal', GPLVault()->admin_assets_url( '/styles/polipop.minimal.min.css' ), array( 'gv-polipop-core' ), '1.0.0-master' );
		wp_register_style( 'gv-select2', GPLVault()->admin_assets_url( '/styles/select2.min.css' ), array(), '4.1.0-rc.0' );
		wp_register_style( 'gv-tippy', GPLVault()->admin_assets_url( '/styles/tippy.min.css' ), array(), '6.3.1' );
		wp_register_style( 'gv-admin', GPLVault()->admin_assets_url( '/styles/gv-admin-caee8f45.css' ), array( 'dashicons', 'gv-polipop-default', 'gv-polipop-compact', 'gv-polipop-minimal', 'gv-tippy', 'gv-select2' ), GPLVault()->version() );
		wp_register_style( 'gv-global', GPLVault()->admin_assets_url( '/styles/gv-global-caee8f45.css' ), array( 'dashicons', 'gv-polipop-default', 'gv-polipop-compact', 'gv-polipop-minimal', 'gv-tippy', 'gv-select2' ), GPLVault()->version() );

		// Only enqueue on GPLVault admin pages to prevent conflicts with other plugins
		if ( self::is_admin_page() ) {
			wp_enqueue_style( 'gv-global' );
			wp_enqueue_style( 'gv-admin' );
			wp_enqueue_script( 'gv-common' );

			wp_localize_script(
				'gv-common',
				'_gvCommonSettings',
				array(
					'ajax_url'    => admin_url( 'admin-ajax.php' ),
					'ajax_nonce'  => wp_create_nonce( GPLVault_Ajax::NONCE_KEY ),
					'ajax_action' => GPLVault_Ajax::ACTION,
					'pagenow'     => self::get_admin_page(),
					'popup'       => array(
						'layout'       => 'popups',
						'sticky'       => false,
						'life'         => 8000,
						'position'     => 'top-right',
						'theme'        => 'default',
						'pauseOnHover' => true,
						'pool'         => 0,
						'spacing'      => 5,
						'progressbar'  => true,
						'closer'       => false,
						'effect'       => 'slide',
						'insert'       => 'before',
						'easing'       => 'ease-in-out',
					),
				)
			);
		}

		if ( static::SLUG_LOGS === self::get_admin_page() ) {
			//          wp_enqueue_script( 'gv-common' );
		}

		if ( static::SLUG_SETTINGS === self::get_admin_page() || static::SLUG_BLACKLIST === self::get_admin_page() ) {
			wp_enqueue_script( 'gv-settings' );
			wp_localize_script(
				'gv-settings',
				'_gvAdminSettings',
				array(
					'selectors' => array(
						'page_wrapper' => 'gv_settings_wrapper',
						'license'      => array(
							'section_id' => 'api_settings_section',
							'api'        => array(
								'block_id'      => 'api_settings_column',
								'input_key'     => 'api_master_key',
								'input_product' => 'api_product_id',
								'button_id'     => 'gv_activate_api',
							),
							'actions'    => array(
								'status_btn'       => 'check_license',
								'deactivation_btn' => 'license_deactivation',
								'cleanup_btn'      => 'cleanup_settings',
							),
						),
						'exclusion'    => array(
							'section_id' => 'gv_items_exclusion',
							'plugins'    => array(
								'input_id'  => 'gv_blocked_plugins',
								'button_id' => 'plugins_exclusion_btn',
							),
							'themes'     => array(
								'input_id'  => 'gv_blocked_themes',
								'button_id' => 'themes_exclusion_btn',
							),
						),
					),
				)
			);
		}
	}

	public function initial_notices() {
		$screen          = get_current_screen();
		$screen_id       = $screen ? $screen->id : '';
		$show_on_screens = array(
			'dashboard',
			'dashboard-network',
			'themes-network',
			'plugins-network',
			'plugins',
			'themes',
		);

		// Notices should only show on GPLVault screens, the main dashboard, and on the plugins screen.
		if ( ! in_array( $screen_id, $show_on_screens, true ) && ! self::is_admin_page() ) {
			return;
		}

		if ( ! gv_settings_manager()->api_key_exists() && ! gv_settings_manager()->license_is_activated() ) {
			if ( is_multisite() ) {
				add_action( 'network_admin_notices', array( $this, 'activation_notice' ) );

			} else {
				add_action( 'admin_notices', array( $this, 'activation_notice' ) );
			}
		}

		if ( defined( 'WP_HTTP_BLOCK_EXTERNAL' ) && WP_HTTP_BLOCK_EXTERNAL ) {
			$host = wp_parse_url( GV_UPDATER_API_URL, PHP_URL_HOST );

			if ( ! defined( 'WP_ACCESSIBLE_HOSTS' ) || stristr( WP_ACCESSIBLE_HOSTS, $host ) === false ) {
				if ( is_multisite() ) {
					add_action( 'network_admin_notices', array( $this, 'external_block_notice' ) );
				} else {
					add_action( 'admin_notices', array( $this, 'external_block_notice' ) );
				}
			}
		}
	}

	/**
	 * Get the custom SVG icon for the admin menu.
	 *
	 * @return string Base64 encoded SVG data URI
	 */
	private function get_admin_icon() {
		// Original viewBox: 329.52 317.02 320.98 395.68
		// Adding 20% padding on all sides
		$svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="265 240 450 550" fill="currentColor">
			<path d="M582.115723,391.823883 C566.642700,361.494904 537.116455,346.598724 501.615173,351.125854 C479.676575,353.923462 462.324127,363.972382 449.517761,381.839447 C441.441711,393.106995 437.494629,405.837952 437.347443,419.710876 C437.282013,425.876617 437.503387,432.051147 437.245850,438.207062 C437.073944,442.315216 438.525940,443.752380 442.689117,443.695892 C457.019073,443.501282 471.353333,443.622864 485.685944,443.623322 C533.183533,443.624786 580.683716,443.897339 628.177063,443.450226 C641.212891,443.327515 650.997864,454.460571 650.471741,465.699402 C649.887878,478.171326 650.237183,490.693268 650.406738,503.190369 C650.458069,506.978088 649.399536,508.591400 645.335266,508.454773 C637.511108,508.191772 629.669128,508.249329 621.840332,508.428253 C618.201477,508.511353 616.738464,507.234924 616.827820,503.524384 C616.992126,496.695953 616.724060,489.857574 616.861328,483.027863 C616.929077,479.662048 615.846436,478.315430 612.276428,478.320831 C551.946289,478.412140 491.613800,478.098114 431.286499,478.525116 C397.818970,478.761993 368.913605,505.531494 365.381165,538.749451 C362.892517,562.152283 363.775269,585.715027 364.658020,609.126770 C365.806854,639.595459 380.656006,661.758240 409.621155,673.284973 C417.069580,676.249023 424.918945,677.691833 433.000427,677.675049 C450.666077,677.638306 468.332062,677.706055 485.997620,677.646484 C520.581177,677.529846 550.415039,652.523560 556.350037,618.463745 C557.171082,613.752014 556.648804,608.818726 556.957764,604.001404 C557.209778,600.071106 555.231750,599.325806 551.787354,599.343689 C531.289001,599.450500 510.789703,599.398926 490.290741,599.391174 C478.652893,599.386780 478.652130,599.378723 478.651642,587.809631 C478.651428,582.476562 478.855621,577.133545 478.590454,571.813782 C478.393341,567.858582 479.851105,566.633240 483.761139,566.659241 C505.925720,566.806946 528.091736,566.737366 550.257263,566.734375 C556.914429,566.733459 556.919556,566.722656 556.930481,560.138855 C556.943481,552.305969 557.075806,544.470154 556.900024,536.641418 C556.810486,532.653809 558.116150,530.898499 562.345398,531.031372 C569.837402,531.266846 577.344238,531.180969 584.841187,531.037231 C587.891479,530.978760 589.397522,531.745483 589.386719,535.178223 C589.292664,565.009644 590.205811,594.877136 588.999756,624.660278 C588.228271,643.713013 579.511841,660.536682 567.014893,675.025879 C547.522400,697.625732 522.555054,709.120422 492.965027,711.493958 C471.987213,713.176575 450.983765,712.774902 430.039825,712.149658 C383.979767,710.774597 347.461975,679.431458 335.350494,645.295471 C331.652344,634.872253 329.824524,624.065491 329.714172,613.064270 C329.501862,591.900818 329.488831,570.732117 329.686371,549.568359 C330.115112,503.634857 354.113403,466.885101 396.679230,449.354279 C401.260345,447.467590 402.953308,445.127991 402.755646,440.164124 C402.044434,422.301422 403.113159,404.611725 409.460571,387.603516 C423.201385,350.784576 449.353119,327.763611 487.654663,319.963745 C516.061035,314.178925 544.281982,316.407715 569.695435,331.868866 C598.997070,349.695526 616.605835,375.987946 622.400024,409.980621 C622.960266,413.267242 623.322510,416.531586 623.411438,419.876068 C623.506714,423.455200 621.992493,424.476471 618.646423,424.412323 C610.649841,424.258972 602.647278,424.292877 594.649170,424.400238 C591.430359,424.443481 589.768188,423.486572 589.609863,419.914856 C589.177307,410.162872 586.459534,400.953156 582.115723,391.823883z"/>
			<path d="M650.332642,657.008911 C650.334961,662.170105 650.250061,666.835754 650.367737,671.496338 C650.450684,674.783081 649.277588,676.445129 645.753967,676.393127 C637.761047,676.275208 629.765198,676.299500 621.771179,676.358643 C618.804199,676.380615 617.603333,675.086853 617.617004,672.136353 C617.657104,663.480896 617.580139,654.824585 617.501770,646.169067 C617.473511,643.053101 618.820801,641.662292 622.030762,641.710693 C629.857056,641.828857 637.686646,641.821533 645.513794,641.747192 C648.868042,641.715332 650.427429,643.105774 650.321106,646.518921 C650.217407,649.846375 650.320068,653.180176 650.332642,657.008911z"/>
			<path d="M625.128479,566.038208 C631.924622,566.037781 638.236084,566.074829 644.546936,566.027832 C648.610046,565.997620 650.574585,567.547974 650.410950,571.983887 C650.135681,579.447632 650.228882,586.931152 650.380798,594.402039 C650.454651,598.030579 649.038513,599.481201 645.403320,599.419922 C637.267395,599.282471 629.126526,599.296204 620.989868,599.408997 C617.735474,599.454102 616.601868,598.052429 616.651794,594.923645 C616.763062,587.949829 616.670349,580.972900 616.674133,573.997192 C616.678406,566.164490 616.684875,566.164490 625.128479,566.038208z"/>
		</svg>';
		
		return 'data:image/svg+xml;base64,' . base64_encode( $svg );
	}

	public function admin_menu() {
		$capability = is_multisite() ? 'manage_network' : 'manage_options';

		add_menu_page(
			__( 'GPLVault Plugin Manager', 'gplvault' ),
			__( 'GPLVault', 'gplvault' ),
			$capability,
			static::SLUG_SETTINGS,
			array( $this, 'page_settings' ),
			$this->get_admin_icon()
		);

		add_submenu_page(
			static::SLUG_SETTINGS,
			__( 'GPLVault Item Blacklist', 'gplvault' ),
			__( 'Disable Updates', 'gplvault' ),
			$capability,
			static::SLUG_BLACKLIST,
			array( $this, 'page_blacklists' )
		);

		add_submenu_page(
			static::SLUG_SETTINGS,
			__( 'GPLVault Logs', 'gplvault' ),
			__( 'Logs', 'gplvault' ),
			$capability,
			static::SLUG_LOGS,
			array( $this, 'page_logs' )
		);
	}

	public function admin_menu_change_name() {
		global $submenu;

		if ( isset( $submenu[ static::SLUG_SETTINGS ] ) ) {
			$submenu[ static::SLUG_SETTINGS ][0][0] = __( 'Settings', 'gplvault' ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		}
	}

	public function admin_menu_lifetime_link(): void {
		global $submenu;

		$capability = is_multisite() ? 'manage_network' : 'manage_options';

		if ( isset( $submenu[ static::SLUG_SETTINGS ] ) ) {
			$submenu[ static::SLUG_SETTINGS ][] = array( '<span id="gv_promo_link" class="gv-promo-link">' . esc_html__( 'Go Lifetime', 'gplvault' ) . '</span>', $capability, self::LIFETIME_URL ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		}
	}

	public function admin_menu_js_css() {
		// Only output on GPLVault admin pages to prevent global conflicts
		if ( ! self::is_admin_page() ) {
			return;
		}
		?>
		<style>
			#adminmenu .wp-submenu a .gv-promo-link {
				color: #ffb702;
				font-weight: bold;
			}
		</style>
		<script type="text/javascript">
			jQuery(document).ready( function($) {
				$('#gv_promo_link').parent().attr('target','_blank');
			});
		</script>
		<?php
	}

	public function activate_license( $request_params ) {
		$api_key    = ! empty( $request_params['api_key'] ) ? gv_clean( $request_params['api_key'] ) : null;
		$product_id = ! empty( $request_params['product_id'] ) ? gv_clean( $request_params['product_id'] ) : null;

		if ( empty( $api_key ) && empty( $product_id ) ) {
			return new WP_Error(
				'gv_missing_fields',
				__( 'Both Product ID and API Key fields are required', 'gplvault' ),
				array(
					'http_status' => WP_Http::BAD_REQUEST,
				)
			);
		}
		$api_obj = gv_api_manager()->set_api_key( $api_key )->set_product_id( $product_id );
		$status  = $api_obj->status();
		if ( is_wp_error( $status ) ) {
			$error_data                = $status->get_error_data();
			$error_data                = empty( $error_data ) ? array() : $error_data;
			$error_data['http_status'] = WP_Http::OK;
			$error_data['title']       = GPLVault_Helper::request_error_title( $status );

			return new WP_Error( $status->get_error_code(), $status->get_error_message(), $error_data );
		}

		$status_data = $status['data'];
		if ( ! $status_data['activated'] ) {
			$activation_response = $api_obj->activate();

			if ( is_wp_error( $activation_response ) ) {
				$error_data                = $activation_response->get_error_data();
				$error_data                = empty( $error_data ) ? array() : $error_data;
				$error_data['http_status'] = WP_Http::OK;
				$error_data['title']       = GPLVault_Helper::request_error_title( $activation_response );

				return new WP_Error( $activation_response->get_error_code(), $activation_response->get_error_message(), $error_data );
			}

			gv_settings_manager()->save_api_settings(
				array(
					'api_key'    => $api_key,
					'product_id' => $product_id,
				)
			);

			gv_settings_manager()->enable_activation_status();
			/* translators: %s: Activation response message from the server */
			$message                        = sprintf( __( 'License activated successfully. %s', 'gplvault' ), $activation_response['message'] );
			$activation_response['message'] = $message;

			do_action( 'gv_api_license_activated', $activation_response );

			return array(
				'title'                       => __( 'License activated!', 'gplvault' ),
				'message'                     => $activation_response['message'],
				'activated'                   => $activation_response['activated'],
				'total_activations_purchased' => $activation_response['data']['total_activations_purchased'],
				'total_activations'           => $activation_response['data']['total_activations'],
				'activations_remaining'       => $activation_response['data']['activations_remaining'],
				'api_key'                     => gv_mask_str( $api_key ),
				'product_id'                  => $product_id,
			);
		}

		gv_settings_manager()->save_api_settings(
			array(
				'api_key'    => $api_key,
				'product_id' => $product_id,
			)
		);

		gv_settings_manager()->enable_activation_status();

		return array(
			'title'                       => __( 'Already Active', 'gplvault' ),
			'message'                     => __( 'Your license is already activated!', 'gplvault' ),
			'activated'                   => $status_data['activated'],
			'total_activations_purchased' => $status_data['total_activations_purchased'],
			'total_activations'           => $status_data['total_activations'],
			'activations_remaining'       => $status_data['activations_remaining'],
			'api_key'                     => gv_mask_str( $api_key ),
			'product_id'                  => $product_id,
		);
	}

	public function deactivate_license( $request_params ) {
		$response = gv_api_manager()->set_initials()->deactivate();

		if ( is_wp_error( $response ) ) {
			$error_data                = $response->get_error_data();
			$error_data                = empty( $error_data ) ? array() : $error_data;
			$error_data['http_status'] = WP_Http::OK;
			$error_data['title']       = GPLVault_Helper::request_error_title( $response );

			return new WP_Error( $response->get_error_code(), $response->get_error_message(), $error_data );
		}

		if ( isset( $response['deactivated'] ) && $response['deactivated'] ) {
			$payload = array(
				'status'      => $response['deactivated'] ? 'deactivated' : 'not deactivated',
				'activations' => $response['data']['total_activations_purchased'],
				'used'        => $response['data']['total_activations'],
				'remaining'   => $response['data']['activations_remaining'],
			);

			do_action( 'gv_license_deactivated', $response );

			/* translators: %s: Number of remaining activations */
			$message             = sprintf( __( 'License deactivated successfully. %s', 'gplvault' ), $response['activations_remaining'] );
			$response['message'] = $message;

			return $response;
		}
	}

	public function check_license( $request_params ) {
		$response = gv_api_manager()->status();

		if ( is_wp_error( $response ) ) {
			$error_data                = $response->get_error_data();
			$error_data                = empty( $error_data ) ? array() : $error_data;
			$error_data['http_status'] = WP_Http::OK;
			$error_data['title']       = GPLVault_Helper::request_error_title( $response );

			return new WP_Error( $response->get_error_code(), $response->get_error_message(), $error_data );
		}

		gv_settings_manager()->save_license_status( $response['data'] );

		$state = $response['data']['activated'] ? __( 'active', 'gplvault' ) : __( 'not active', 'gplvault' );
		return array(
			'title'                       => __( 'License Status', 'gplvault' ),
			/* translators: %s: License state (active or not active) */
			'message'                     => sprintf( __( 'License for the site is %s on the GPLVault server', 'gplvault' ), $state ),
			'activated'                   => $response['data']['activated'] ?? false,
			'total_activations_purchased' => $response['data']['total_activations_purchased'] ?? 31,
			'total_activations'           => $response['data']['total_activations'] ?? 0,
			'activations_remaining'       => $response['data']['activations_remaining'] ?? 31,
		);
	}

	public function exclude_plugins( $request_params ) {
		$plugins = $request_params['plugins'] ?? array();
		$plugins = gv_clean( $plugins );

		$updated = gv_settings_manager()->save_blocked_plugins( $plugins );

		if ( ! $updated ) {
			return new WP_Error(
				'plugin_exclusion_failed',
				__( 'Plugin exclusion list was not updated.', 'gplvault' ),
				array(
					'title' => __( 'Not Updated', 'gplvault' ),
				)
			);
		}

		// regenerate update transient
		self::gv_update_plugins();

		return array(
			'title'   => __( 'Updated', 'gplvault' ),
			'message' => __( 'Plugin exclusion list updated successfully.', 'gplvault' ),
		);
	}

	public function exclude_themes( $request_params ) {
		$themes  = $request_params['themes'] ?? array();
		$themes  = array_map( 'wp_unslash', $themes );
		$updated = gv_settings_manager()->save_blocked_themes( $themes );

		if ( ! $updated ) {
			return new WP_Error(
				'theme_exclusion_failed',
				__( 'Theme exclusion list was not updated.', 'gplvault' ),
				array(
					'title' => __( 'Not Updated', 'gplvault' ),
				)
			);
		}

		// regenerate themes update transient
		// self::gv_update_themes();

		return array(
			'title'   => __( 'Updated', 'gplvault' ),
			'message' => __( 'Theme exclusion list updated successfully.', 'gplvault' ),
		);
	}


	public function cleanup_settings( $request_params ) {
		delete_site_transient( self::UPDATES_KEY_PLUGINS );
		delete_site_transient( self::UPDATES_KEY_THEMES );

		gv_settings_manager()->remove_all_schema();
		gv_settings_manager()->disable_activation_status();
		gv_settings_manager()->remove_api_key();
		gv_settings_manager()->remove_license_status();

		return array(
			'title'   => __( 'Settings Removed', 'gplvault' ),
			'message' => __( 'All license related settings removed successfully.', 'gplvault' ),
		);
	}

	//
	//  Callbacks for Settings API ends
	//

	public function page_settings() {
		self::load_view(
			'/settings',
			array(
				'admin_manager'    => $this,
				'settings_manager' => gv_settings_manager(),
			)
		);
	}

	public function page_blacklists(): void {
		self::load_view(
			'/blacklists',
			array(
				'admin_manager'    => $this,
				'settings_manager' => gv_settings_manager(),
			)
		);
	}

	public function page_logs() {
		$log_files  = GPLVault_Psr_Log_Handler::get_log_files();
		$active_log = '';

		if ( ! empty( $_REQUEST['gv_log_file'] ) && isset( $log_files[ sanitize_title( wp_unslash( $_REQUEST['gv_log_file'] ) ) ] ) ) { // @phpcs:ignore
			$active_log = $log_files[ sanitize_title( wp_unslash( $_REQUEST['gv_log_file'] ) ) ]; // @phpcs:ignore
		} elseif ( ! empty( $log_files ) ) {
			$active_log = current( $log_files );
		}

		self::load_view(
			'/logs',
			array(
				'log_files'        => $log_files,
				'active_log'       => $active_log,
				'settings_manager' => gv_settings_manager(),
			)
		);
	}

	/**
	 * Handle log-related actions in a single admin_init callback.
	 * This prevents unnecessary method calls on every admin page load.
	 *
	 * @since 5.3.3
	 */
	public function handle_log_actions() {
		// Only proceed if a log action is being requested
		if ( empty( $_REQUEST['gv_log_remove'] ) &&
		     empty( $_REQUEST['gv_log_download'] ) &&
		     empty( $_REQUEST['gv_download_all_logs'] ) ) {
			return;
		}

		// Route to the appropriate handler
		if ( ! empty( $_REQUEST['gv_log_remove'] ) ) {
			$this->manage_log_remove();
		} elseif ( ! empty( $_REQUEST['gv_log_download'] ) ) {
			$this->manage_log_download();
		} elseif ( ! empty( $_REQUEST['gv_download_all_logs'] ) ) {
			$this->manage_all_logs_download();
		}
	}

	public function manage_log_remove() {
		if ( empty( $_REQUEST['gv_log_remove'] ) ) { // WPCS: input var ok.
			return;
		}
		if ( empty( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( wp_unslash( $_REQUEST['_wpnonce'] ), 'gv_remove_log' ) ) { // WPCS: input var ok, sanitization ok.
			wp_die( esc_html__( 'Action failed. Please refresh the page and retry.', 'gplvault' ) );
		}

		if ( ! empty( $_REQUEST['gv_log_remove'] ) ) {  // WPCS: input var ok.
			$log_handle = wp_unslash( $_REQUEST['gv_log_remove'] );
			GPLVault_Psr_Log_Handler::remove( $log_handle ); // WPCS: input var ok, sanitization ok.
		}

		wp_safe_redirect( esc_url_raw( admin_url( 'admin.php?page=' . static::SLUG_LOGS ) ) );
		exit();
	}

	public function manage_log_download() {
		if ( empty( $_REQUEST['gv_log_download'] ) ) {
			return;
		}
		if ( empty( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( wp_unslash( $_REQUEST['_wpnonce'] ), 'gv_download_log' ) ) { // WPCS: input var ok, sanitization ok.
			wp_die( esc_html__( 'Action failed. Please refresh the page and retry.', 'gplvault' ) );
		}

		if ( ! empty( $_REQUEST['gv_log_download'] ) ) {  // WPCS: input var ok.
			$log_handle = sanitize_title( wp_unslash( $_REQUEST['gv_log_download'] ) );
			GPLVault_Psr_Log_Handler::download( $log_handle );
		}
		exit();
	}

	public function manage_all_logs_download() {
		if ( empty( $_REQUEST['gv_download_all_logs'] ) ) {
			return;
		}
		if ( empty( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( wp_unslash( $_REQUEST['_wpnonce'] ), 'gv_download_all_logs' ) ) {
			wp_die( esc_html__( 'Action failed. Please refresh the page and retry.', 'gplvault' ) );
		}

		$log_files = GPLVault_Psr_Log_Handler::get_log_files();
		if ( empty( $log_files ) ) {
			wp_die( esc_html__( 'No log files found.', 'gplvault' ) );
		}

		// Combine all logs into one content
		$all_logs_content = '';
		$separator        = "========================================\n";

		foreach ( $log_files as $log_file ) {
			$file_path = GV_UPDATER_LOG_DIR . $log_file;
			if ( file_exists( $file_path ) ) {
				$header = "LOG FILE: $log_file\n";
				// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Reading local log file
				$content           = file_get_contents( $file_path );
				$all_logs_content .= $separator . $header . $separator . "\n" . $content . "\n\n";
			}
		}

		// Set headers for download
		$filename = 'gplvault-all-logs-' . gmdate( 'Y-m-d-H-i-s' ) . '.txt';
		header( 'Content-Type: text/plain; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
		header( 'Content-Length: ' . strlen( $all_logs_content ) );
		header( 'Cache-Control: no-cache, no-store, must-revalidate' );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Plain text file download
		echo $all_logs_content;
		exit();
	}

	// TODO: remove the line below
	//
	//  public function page_plugins() {
	//      self::load_view(
	//          '/plugins',
	//          array(
	//              'admin_manager'    => $this,
	//              'settings_manager' => gv_settings_manager(),
	//          )
	//      );
	//  }

	public function activation_notice() {
		include GV_UPDATER_STATIC_PATH . 'notices/html-notice-activate.php';
	}

	public function cron_notice() {
		include GV_UPDATER_STATIC_PATH . 'notices/html-notice-cron.php';
	}

	public function external_block_notice() {
		include GV_UPDATER_STATIC_PATH . 'notices/html-notice-external-block.php';
	}

	public static function load_view( $view, $imported_variables = array(), $path = false ) {
		if ( $imported_variables && is_array( $imported_variables ) ) {
			extract( $imported_variables, EXTR_OVERWRITE ); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract
		}

		if ( ! $path ) {
			$path = GPLVault()->path( '/views' );
		}

		include $path . rtrim( $view, '.php' ) . '.php';
	}

	public static function load_partial( $partial, $imported_variables = array(), $path = false ) {
		$view = '/partials/' . ltrim( $partial, '/\\' );
		self::load_view( $view, $imported_variables, $path );
	}

	/**
	 * @return bool
	 */
	public static function is_admin_page() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- This is a read-only check to determine which admin page to display, not processing form data
		$vault_page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';

		return in_array( $vault_page, static::admin_pages(), true );
	}

	public static function admin_pages() {
		return array(
			static::SLUG_SETTINGS,
			static::SLUG_PLUGINS,
			static::SLUG_THEME,
			static::SLUG_SYSTEM,
			static::SLUG_LOGS,
			static::SLUG_BLACKLIST,
		);
	}

	public function render_header() {
		if ( ! self::is_admin_page() ) {
			return;
		}

		$screen_id = self::get_admin_page();
		$titles    = array(
			static::SLUG_SETTINGS  => __( 'GPLVault Settings', 'gplvault' ),
			static::SLUG_PLUGINS   => __( 'GPLVault Plugins', 'gplvault' ),
			static::SLUG_THEME     => __( 'GPLVault Themes', 'gplvault' ),
			static::SLUG_LOGS      => __( 'GPLVault Logs', 'gplvault' ),
			static::SLUG_SYSTEM    => __( 'GPLVault System Info', 'gplvault' ),
			static::SLUG_BLACKLIST => __( 'GPLVault Disable Updates', 'gplvault' ),
		);

		?>
		<div class="gv-layout__header">
			<div class="gv-layout__header-wrapper">
				<h1 class="gv-layout__header-heading"><?php echo esc_html( $titles[ $screen_id ] ); ?></h1>
			</div>
		</div>
		<?php
	}

	public static function inject_before_notices() {
		if ( ! self::is_admin_page() ) {
			return;
		}

		echo '<div class="gv-layout__notice-list-hide" id="wp__notice-list">';
		echo '<div class="gv-layout__noticelist"> <div id="gv_notice"></div> </div>';
		echo '<div class="wp-header-end" id="gv-layout__notice-catcher"></div>';
	}

	public static function inject_after_notices() {
		if ( ! self::is_admin_page() ) {
			return;
		}

		echo '</div>';
	}

	public static function admin_links( $type = '' ) {
		$links = array(
			'settings'  => self_admin_url( 'admin.php?page=' . self::SLUG_SETTINGS ),
			'plugins'   => self_admin_url( 'admin.php?page=' . self::SLUG_PLUGINS ),
			'themes'    => self_admin_url( 'admin.php?page=' . self::SLUG_THEME ),
			'blacklist' => self_admin_url( 'admin.php?page=' . self::SLUG_BLACKLIST ),
			'logs'      => self_admin_url( 'admin.php?page=' . self::SLUG_LOGS ),
		);

		if ( empty( $type ) ) {
			return $links;
		}

		return $links[ $type ] ?? '';
	}

	public static function get_admin_page() {
		return isset( $_GET['page'] ) ? esc_attr( wp_unslash( $_GET['page'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	}

	public function ajax_bindings( $bindings ) {
		return wp_parse_args( $this->get_ajax_bindings(), $bindings );
	}


	/**
	 * @param WP_Screen $screen
	 */
	public function add_help_tabs( $screen ) {
		if ( ! self::is_admin_page() ) {
			return;
		}

		// phpcs:ignore Squiz.PHP.CommentedOutCode.Found
		//      $screen->set_help_sidebar(
		//          '<p><strong>' . __( 'For more information:' ) . '</strong></p>'
		//      );

		$screen->add_help_tab(
			array(
				'id'      => 'gplvault_tab_about',
				'title'   => __( 'About', 'gplvault' ),
				'content' =>
					'<h2>' .
					/* translators: %s: Plugin version number */
					sprintf( __( 'About GPLVault Update Manager %s', 'gplvault' ), GPLVault()->version() ) .
					'</h2>' .
					'<p>' .
					__( 'The GPLVault Update Manager is the most advanced and flexible plugin for managing updates to GPLVault plugins and themes.', 'gplvault' ) .
					'</p>' .
					'<p>' .
					__( 'The plugin is used to upgrade GPLVault items via not only the WP Native Upgrade system but also the custom Plugins upgrade system built into this plugin.', 'gplvault' ) .
					'</p>' .
					'<p>' .
					__( 'You can use either of these ways to upgrade plugins - and if the WP regular upgrade system fails to upgrade any plugin, you can use the custom GPLVault Plugins page to upgrade instead.', 'gplvault' ) .
					'</p>' .
					'<p>' .
					__( 'For your information, themes are still upgraded using the WP regular upgrade system.', 'gplvault' ) .
					'</p>',
			)
		);

		$screen->add_help_tab(
			array(
				'id'      => 'gplvault_tab_settings',
				'title'   => __( 'Settings', 'gplvault' ),
				'content' =>
				'<h2>' . __( 'GPLVault Settings Sections', 'gplvault' ) . '</h2>' .
				'<h3>' . __( 'License Activation', 'gplvault' ) . '</h3>' .
				'<ul>' .
				sprintf(
					'<li><strong>%1$s</strong>: %2$s',
					__( 'API Settings', 'gplvault' ),
					__( 'This section manages license activation with GPLVault server to keep client plugin functional. Once activated successfully, the submission button is disabled to prevent re-submission of the form area. If you Deactivate the license with the plugin or clear local license settings, the button is enabled again. Both input fields are required in this section.', 'gplvault' )
				) .
					sprintf( '<p><strong><em>%1$s</em></strong>: %2$s</p>', __( 'User License Key', 'gplvault' ), __( 'When you purchase a subscription from GPLVault, you will receive a User License Key. Use this key to activate your license. Once activated, the plugin can retrieve updates and information from www.gplvault.com.', 'gplvault' ) ) .
					sprintf( '<p><strong><em>%1$s</em></strong>: %2$s</p>', __( 'Product ID', 'gplvault' ), __( 'Product ID is the unique number of subscription plan you purchased on GPLVault main server.', 'gplvault' ) ) .
				'</li>' .
					sprintf( '<li><strong>%1$s</strong>: %2$s', __( 'License Actions', 'gplvault' ), __( 'This section helps to manage API Settings - mainly, deactivation, checking license status from the main server, and cleaning up local saved license related options from this site (the site client plugin installed on). All buttons are disabled if license is not activated successfully.', 'gplvault' ) ) .
					sprintf( '<p><strong><em>%1$s</em></strong>: %2$s</p>', __( 'Deactivate License Key', 'gplvault' ), __( 'Use this button to deactivate the current site from the GPLVault activation list. Note that deactivation occurs automatically when you disable or uninstall the plugin. It is important to deactivate the site before manually deleting or replacing plugin files on this server.', 'gplvault' ) ) .
					sprintf( '<p><strong><em>%1$s</em></strong>: %2$s</p>', __( 'Check License', 'gplvault' ), __( 'Sometimes it is necessary to verify communication with the server and check your license activation status. Use this button to check your current license status with GPLVault.', 'gplvault' ) ) .
					sprintf( '<p><strong><em>%1$s</em></strong>: %2$s</p>', __( 'Clear Local Settings', 'gplvault' ), __( 'Please use this with caution. For unknown reasons, this site entry may be missing from the server\'s activated sites list. If this happens, you must clear the locally stored information before reactivating. Use this button to clean up local license settings in such situations.', 'gplvault' ) ) .
				'</li>' .
				'</ul>' .
				'<h3>' . __( 'Updater Item Exclusion', 'gplvault' ) . '</h3>' .
				'<p>' .
				__( 'The Item Exclusion section is used to exclude plugins or themes from GPLVault Update Manager upgrade coverage. This is important when you have purchased the original copy of an item and you want to upgrade that item from the main vendor.', 'gplvault' ) .
				'<br>' .
					sprintf( '<strong>%1$s</strong>: %2$s', __( 'Notes', 'gplvault' ), __( 'If you want to remove the "Restriction" for all previous blacklisted items, you have to empty the input field and hit "Save" button, this will remove all previous entries.', 'gplvault' ) ) .
				'</p>' .
				'<ul>' .
				sprintf(
					'<li><strong>%1$s</strong>: %2$s</li>',
					__( 'Plugins', 'gplvault' ),
					__( 'Here you will see the list of all installed plugins on this WP installation and you have to pick the plugin you want to exclude from GPLVault Update Manager upgrade process. You can add or remove multiple entries from the select box.', 'gplvault' )
				) .
				sprintf(
					'<li><strong>%1$s</strong>: %2$s</li>',
					__( 'Themes', 'gplvault' ),
					__( 'Select the theme you want from listed entries to exclude from GPLVault Update Manager upgrade process. You can add or remove multiple entries from the select box.', 'gplvault' )
				) .
				'</ul>',
			)
		);

		$screen->add_help_tab(
			array(
				'id'      => 'gplvault_tab_plugins',
				'title'   => __( 'Plugins', 'gplvault' ),
				'content' =>
				'<h2>' . __( 'GPLVault Plugins Upgrade', 'gplvault' ) . '</h2>' .
				sprintf(
					'<p><strong>%1$s</strong>: %2$s</p>',
					__( 'WP Native Upgrade System', 'gplvault' ),
					__( 'From version 4.1.0, we have added WP Regular update system for plugins again. So, you will be able to upgrade GPLVault items from plugins from Dashboard > Updates or Plugins page. Please use the custom update system if you are not able to upgrade any plugin using regular upgrade system.', 'gplvault' )
				),
			)
		);

		$screen->add_help_tab(
			array(
				'id'      => 'gplvault_tab_forbidden',
				'title'   => __( 'Forbidden Error', 'gplvault' ),
				'content' =>
					'<h2>' . __( 'Forbidden Error', 'gplvault' ) . '</h2>' .
					'<p>' .
					__( 'If you are experiencing "Forbidden" error during connecting to our server, most probably, your IP or domain is blocked by our Firewall system.<br> Please consider the following reasons first.', 'gplvault' ) .
					'</p>' .
					'<ul>' .
						'<li>' .
						__( 'The IP is found in any IP Blacklist Database.', 'gplvault' ) .
						'</li>' .
						'<li>' .
						__( 'Our edge server or application server is getting an unusual amount of requests from your end that either of them has blocked your IP', 'gplvault' ) .
						'</li>' .
					'</ul><br>' .
					'<p><strong>' . __( 'What you can do!', 'gplvault' ) . '</strong></p>' .
					'<p>' .
					sprintf(
						/* translators: %1$s: URL to the IP checker tool, %2$s: Name of the IP checker tool */
						__(
							'Check your website IP with a blacklist IP checker, or use a reputable tool like <a href="%1$s">%2$s</a>.<br>
					However, in our experience, the client\'s claimed IP and the actual requesting IP often differ. Please ask your web service provider about the IPs they use for outbound traffic from their server.',
							'gplvault'
						),
						'https://www.abuseipdb.com/',
						'AbuseIPDB.com'
					) .
					'<br>' .
					'<p><strong>' . __( 'Note:', 'gplvault' ) . '</strong></p>' .
					'<span>' .
					/* translators: %s: Explanation about security concerns */
					__( 'For security reasons, we are unable to resolve this type of issue. It would make our entire system vulnerable to severe attacks, and we continually face DDoS attempts.', 'gplvault' ) .
					'</span><br><br>',

			)
		);
	}

	public static function gv_update_plugins() {
		GPLVault_Helper::update_plugins_data();
	}

	public static function gv_update_themes() {
		GPLVault_Helper::update_themes_data();
	}
}
