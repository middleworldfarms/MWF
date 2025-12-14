<?php
/**
 * BFCM 2025 Banner.
 *
 * @package Wt_Smart_Coupon
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Wt_Bfcm_Twenty_Twenty_Five' ) ) {

	/**
	 * Class Wt_Bfcm_Twenty_Twenty_Five
	 *
	 * This class is responsible for displaying and handling the Black Friday and Cyber Monday CTA banners for 2025.
	 */
	class Wt_Bfcm_Twenty_Twenty_Five {

		/**
		 * Banner id.
		 *
		 * @var string
		 */
		private $banner_id = 'wt-bfcm-twenty-twenty-five';

		/**
		 * Banner state option name.
		 *
		 * @var string
		 */
		private static $banner_state_option_name = 'wt_bfcm_twenty_twenty_five_banner_state'; // Banner state, 1: Show, 2: Closed by user, 3: Clicked the grab button.

		/**
		 * Banner state.
		 *
		 * @var int
		 */
		private $banner_state = 1;

		/**
		 * Show banner.
		 *
		 * @var bool|null
		 */
		private static $show_banner = null;

		/**
		 * Ajax action name.
		 *
		 * @var string
		 */
		private static $ajax_action_name = 'wt_bcfm_twenty_twenty_five_banner_state';

		/**
		 * Promotion link.
		 *
		 * @var string
		 */
		private static $promotion_link = 'https://www.webtoffee.com/plugins/?utm_source=BFCM_promotion&utm_medium=Recommendations&utm_campaign=BFCM-Promotion';

		/**
		 * Banner version.
		 *
		 * @var string
		 */
		private static $banner_version = '';

		/**
		 * Constructor.
		 */
		public function __construct() {
			self::$banner_version = WT_P_IEW_VERSION; // Plugin version.

			$this->banner_state = get_option( self::$banner_state_option_name ); // Current state of the banner.
			$this->banner_state = absint( false === $this->banner_state ? 1 : $this->banner_state );

			// Enqueue styles.
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles_and_scripts' ) );

			// Add banner.
            add_action('admin_notices', array($this, 'show_banner'), 999);

			// Ajax hook to save banner state.
			add_action( 'wp_ajax_' . self::$ajax_action_name, array( $this, 'update_banner_state' ) );

			add_action( 'admin_head-edit.php', array($this, 'show_smart_coupon_BFCM_sbanner') );

			// Ajax hook to hide Smart Coupons promotion banner.
			add_action( 'wp_ajax_wbte_sc_hide_promotion_banner', array( $this, 'hide_smart_coupon_promotion_banner' ) );

		}

		/**
		 * To add the banner styles
		 */
		public function enqueue_styles_and_scripts() {
			wp_enqueue_style( $this->banner_id . '-css', plugin_dir_url( __FILE__ ) . 'assets/css/wt-bfcm-twenty-twenty-five.css', array(), self::$banner_version, 'all' );
			$params = array(
				'ajax_url' => esc_url_raw( admin_url( 'admin-ajax.php' ) ),
				'nonce'    => wp_create_nonce( 'wt_bfcm_twenty_twenty_five_banner_nonce' ),
				'action'   => self::$ajax_action_name,
				'cta_link' => self::$promotion_link,
			);
			wp_enqueue_script( $this->banner_id . '-js', plugin_dir_url( __FILE__ ) . 'assets/js/wt-bfcm-twenty-twenty-five.js', array( 'jquery' ), self::$banner_version, false );
			wp_localize_script( $this->banner_id . '-js', 'wt_bfcm_twenty_twenty_five_banner_js_params', $params );
		}

		public function show_smart_coupon_BFCM_sbanner()
        {
            global $current_screen;
			include_once ABSPATH . 'wp-admin/includes/plugin.php';

			$installed_plugins = get_plugins();
			// Only show banner if SC Pro is not installed and current page is coupons page.
			if ( ! is_object( $current_screen ) || 'shop_coupon' !== $current_screen->post_type || array_key_exists( 'wt-smart-coupon-pro/wt-smart-coupon-pro.php', $installed_plugins ) ) {
				return;
			}
            
			?>
			<script type="text/javascript">
				jQuery(document).ready(function($){					
					<?php
					$hidden_banners = get_option( 'wbte_sc_hidden_promotion_banners', array() );
					if ( ! defined( 'WBTE_BFCM_SC_COUPONS_PAGE' ) && ! in_array( 'sc_cpns_page', $hidden_banners, true ) ) {
						define( 'WBTE_BFCM_SC_COUPONS_PAGE', true );

						$campaign_url = 'https://www.webtoffee.com/product/smart-coupons-for-woocommerce/?utm_source=free_plugin_add_coupon_menu&utm_medium=product_import_free&utm_campaign=smart_coupons';

						$bulk_plugin_text = sprintf(
							'<div data-wbte-sc-promotion-banner-id="sc_cpns_page" class="wbte_sc_promotion_banner_div"><span><img src="%s" style="width: 16px;" /></span>&nbsp;<span class="wbte_sc_promotion_banner_title">%s</span><div class="wbte_sc_promotion_banner_content"><p style="margin: 0; font-size: 14px;"> %s </p><div class="wbte_sc_promotion_banner_actions"> <a class="button button-secondary wbte_sc_promotion_banner_link_btn" href="%s" target="_blank"> %s <span class="dashicons dashicons-arrow-right-alt" style="font-size: 14px; line-height: 1.5;"></span> </a>&ensp;<button type="button" class="button button-secondary wbte_sc_promotion_banner_close wbte_sc_promotion_banner_later"> %s </button></div></div><span class="dashicons dashicons-no-alt wbte_sc_promotion_banner_close wbte_sc_promotion_banner_close_btn"></span></div>',
                            esc_url( plugin_dir_url(__FILE__) . 'assets/images/idea_bulb_purple.svg' ),
							esc_html__( 'Did you know?', 'product-import-export-for-woo' ),
							sprintf(
								// translators: 1: a tag opening, 2: a tag closing.
								__( 'With the %1$s Smart Coupons %2$s plugin, you can create Buy One Get One offers and advanced coupons that boost sales during BFCM.', 'product-import-export-for-woo' ),
								'<a href="' . esc_url( $campaign_url ) . '" target="_blank"><b>',
								'</b></a>'
							),
							esc_url( $campaign_url ),
							esc_html__( 'Get Plugin Now', 'product-import-export-for-woo' ),
							esc_html__( 'Maybe later', 'product-import-export-for-woo' )
						);
						?>
						jQuery( '.page-title-action' ).after( '<?php echo wp_kses_post( $bulk_plugin_text ); ?>' );

						// Banner close button callback
						$( document ).on('click', '.wbte_sc_promotion_banner_close', function( e ){
							e.preventDefault();
							const banner_div = $(this).closest('.wbte_sc_promotion_banner_div');
							const banner_id = banner_div.attr('data-wbte-sc-promotion-banner-id');

							banner_div.css({'opacity': '0.6', 'pointer-events': 'none'});

							if( banner_id ) {
								$.ajax({
									url: '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>',
									type: 'POST',
									data: {
										action: 'wbte_sc_hide_promotion_banner',
										banner_id: banner_id,
										_wpnonce: '<?php echo esc_js( wp_create_nonce( 'wt_bfcm_twenty_twenty_five_banner_nonce' ) ); ?>'
									},
									success: function(response) {
										banner_div.fadeOut(300, function() {
											banner_div.remove();
										});
									},
									error: function() {
										banner_div.fadeOut(300, function() {
											banner_div.remove();
										});
									}
								});
							} else {
								banner_div.fadeOut(300, function() {
									banner_div.remove();
								});
							}
						});
						<?php
					}
					?>
				});
			</script>
		<?php
        }


		/**
		 * Show the banner.
		 */
		public function show_banner() {
			if ( $this->is_show_banner() ) {
				?>
					<div class="wt-bfcm-banner-2025 notice is-dismissible">
						<div class="wt-bfcm-banner-body">
							<div class="wt-bfcm-banner-body-img-section">
								<img src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . 'assets/images/black-friday-2025.svg' ); ?>" alt="<?php esc_attr_e( 'Black Friday Cyber Monday 2025', 'product-import-export-for-woo' ); ?>">
							</div>
							<div class="wt-bfcm-banner-body-info">
								<div class="wt-bfcm-never-miss-this-deal">
									<p><?php echo esc_html__( 'Never Miss This Deal', 'product-import-export-for-woo' ); ?></p>
								</div>
								<div class="info">
									<p>
									<?php
										printf(
											// translators: 1: Discount text with span wrapper, e.g. <span>30% OFF</span>.
											esc_html__( 'Your Last Chance to Avail %1$s on WebToffee Plugins. Grab the deal before it`s gone!', 'product-import-export-for-woo' ),
											'<span>30% ' . esc_html__( 'OFF', 'product-import-export-for-woo' ) . '</span>'
										);
									?>
									</p>
								</div>
								<div class="info-button">
									<a href="<?php echo esc_url( self::$promotion_link ); ?>" class="bfcm_cta_button" target="_blank"><?php echo esc_html__( 'View plugins', 'product-import-export-for-woo' ); ?> <span class="dashicons dashicons-arrow-right-alt"></span></a>
								</div>
							</div>
						</div>
					</div>
				<?php
			} 
		}

		/**
		 * Check if the banner should be shown.
		 *
		 * @return bool
		 */
		public function is_show_banner() {

			// Check if the current date is less than the start date then wait for the start date.
			if ( ! class_exists( 'Wt_Import_Export_For_Woo_Basic' ) || ! method_exists( 'Wt_Import_Export_For_Woo_Basic', 'is_bfcm_season' ) || ! Wt_Import_Export_For_Woo_Basic::is_bfcm_season() ) {
				return self::$show_banner = false;
			}

			// Already checked.
			if ( ! is_null( self::$show_banner ) ) {
				return self::$show_banner;
			}

			// Check current banner state.
			if ( 1 !== $this->banner_state ) {
				self::$show_banner = false;
				return self::$show_banner;
			}

			// Check screens.
			$screen    = get_current_screen();
			$screen_id = $screen ? $screen->id : '';

			/**
			 *  Pages to show this black friday and cyber monday banner for 2025.
			 *
			 *  @since 1.1.0
			 *  @param  string[]    Default screen ids
			 */
			$screens_to_show = (array) apply_filters( 'wt_bfcm_banner_screens', array() );

			self::$show_banner = in_array( $screen_id, $screens_to_show, true );
			return self::$show_banner;
		}

		/**
		 *  Update banner state ajax hook
		 */
		public function update_banner_state() {
			check_ajax_referer( 'wt_bfcm_twenty_twenty_five_banner_nonce' );
			
			if ( isset( $_POST['wt_bfcm_twenty_twenty_five_banner_action_type'] ) ) {

				$action_type = absint( sanitize_text_field( wp_unslash( $_POST['wt_bfcm_twenty_twenty_five_banner_action_type'] ) ) );
				if ( in_array( $action_type, array( 2, 3 ), true ) ) {
					update_option( self::$banner_state_option_name, $action_type );
				}
			}
			wp_send_json_success();
		}

		/**
		 *  Hide Smart Coupons promotion banner
		 *
		 *  @since 2.5.7
		 */
		public function hide_smart_coupon_promotion_banner() {
			check_ajax_referer( 'wt_bfcm_twenty_twenty_five_banner_nonce', '_wpnonce' );

			$hided_banners   = get_option( 'wbte_sc_hidden_promotion_banners', array() );
			$hided_banners[] = isset( $_POST['banner_id'] ) ? sanitize_text_field( wp_unslash( $_POST['banner_id'] ) ) : '';
			update_option( 'wbte_sc_hidden_promotion_banners', $hided_banners );
			wp_send_json_success();
		}
	}

	new Wt_Bfcm_Twenty_Twenty_Five();
}