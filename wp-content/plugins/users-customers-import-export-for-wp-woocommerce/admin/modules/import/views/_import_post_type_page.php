<?php
if (!defined('ABSPATH')) {
	exit;
}
?>
<div class="wt_iew_import_main">
	<p><?php echo esc_html($this->step_description); ?></p>
	<div class="wt_iew_post-type-cards">
		<?php
		foreach ($post_types as $key => $value) {
			$postTypeLink = wt_iew_get_post_type_link($key);
			if (!$postTypeLink) {
				continue;
			}
			$postImageLink = WT_U_IEW_PLUGIN_URL . 'assets/images/post_types/' . strtolower($key) . '.svg';
			$postImageLinkactive = WT_U_IEW_PLUGIN_URL . 'assets/images/post_types/' . strtolower($key) . 'active.svg';
		?>
			<div class="wt_iew_post-type-card <?php echo ($item_type == $key ? 'selected' : ''); ?>" data-post-type="<?php echo esc_attr($key); ?>">
				<div class="wt_iew_post-type-card2">
					<div class="wt_iew_image <?php echo 'wt_iew_image_' . esc_html($key); ?>" style="display : <?php echo ($item_type == $key) ? 'none' : 'block'; ?>">
						<img src="<?php echo esc_url($postImageLink); ?>" />
					</div>
					<div class="<?php echo 'wt_iew_active_image_' . esc_html($key); ?>" style="display : <?php echo ($item_type == $key) ? 'block' : 'none'; ?>">
						<img src="<?php echo esc_url($postImageLinkactive); ?>" />
					</div>

				</div>
				<h3 class="wt_iew_post-type-card-hd"><?php echo esc_html($value); ?></h3>
				<div class="wt_iew_free_addon_warn <?php echo 'wt_iew_type_' . esc_html($key); ?>" style="display:block;">
					<?php
					$btn_href   = '';
					$btn_text   = '';
					$btn_class  = '';
					if (!file_exists(WP_PLUGIN_DIR . '/' . $postTypeLink['basic_plugin'])) 
                    {
						$btn_href = $postTypeLink['link'];
                        $btn_text   = __('Install now', 'users-customers-import-export-for-wp-woocommerce');
                        $btn_class  = '';
                        $btn_target = '';
                    } elseif (!is_plugin_active($postTypeLink['basic_plugin'])) {
                        $btn_href   = wp_nonce_url(self_admin_url('plugins.php?action=activate&plugin=' . urlencode($postTypeLink['basic_plugin']) . '&plugin_status=all&paged=1&s'), 'activate-plugin_' . $postTypeLink['basic_plugin']);
                        $btn_text   = __('Activate', 'users-customers-import-export-for-wp-woocommerce');
                        $btn_class  = 'activate';
                        $btn_target = '';
                    }

                    if ('subscription' === $key) {
                        $btn_href = $postTypeLink['link'];
                        $btn_text   = __('Premium', 'users-customers-import-export-for-wp-woocommerce');
                        $btn_class  = 'premium-button';
                        $btn_target = '_blank';
                    }

					if ($btn_href) {
					?>
						<a class="<?php echo esc_attr($btn_class); ?>" href="<?php echo esc_url($btn_href); ?>" <?php if ($btn_target) echo 'target="' . esc_attr($btn_target) . '"'; ?>><?php echo esc_html($btn_text); ?></a>
					<?php
					}
					?>
				</div>

			</div>
		<?php
		}
		?>
	</div>
	<br />
	<?php
	function wt_iew_get_post_type_link($post_type)
	{
		$wt_iew_post_types = array(
			'user' => array(
				'basic_plugin' => 'users-customers-import-export-for-wp-woocommerce/users-customers-import-export-for-wp-woocommerce.php'

			),
			'order' => array(
				'link' => admin_url('plugin-install.php?tab=plugin-information&plugin=order-import-export-for-woocommerce'),
				'basic_plugin'  => 'order-import-export-for-woocommerce/order-import-export-for-woocommerce.php',
			),
			'coupon' => array(
				'link' => admin_url('plugin-install.php?tab=plugin-information&plugin=order-import-export-for-woocommerce'),
				'basic_plugin'  => 'order-import-export-for-woocommerce/order-import-export-for-woocommerce.php',
			),
			'product' => array(
				'link' => admin_url('plugin-install.php?tab=plugin-information&plugin=product-import-export-for-woo'),
				'basic_plugin' => 'product-import-export-for-woo/product-import-export-for-woo.php',
			),
			'product_review' => array(
				'link' => admin_url('plugin-install.php?tab=plugin-information&plugin=product-import-export-for-woo'),
				'basic_plugin' => 'product-import-export-for-woo/product-import-export-for-woo.php'

			),
			'product_categories' => array(
				'link' => admin_url('plugin-install.php?tab=plugin-information&plugin=product-import-export-for-woo'),
				'basic_plugin' => 'product-import-export-for-woo/product-import-export-for-woo.php'

			),
			'product_tags' => array(
				'link' => admin_url('plugin-install.php?tab=plugin-information&plugin=product-import-export-for-woo'),
				'basic_plugin' => 'product-import-export-for-woo/product-import-export-for-woo.php'

			),
			'subscription' => array(
				'link' => esc_url('https://www.webtoffee.com/product/order-import-export-plugin-for-woocommerce/?utm_source=free_plugin_revamp_post_type&utm_medium=basic_revamp&utm_campaign=Order_Import_Export&utm_content=' . WT_U_IEW_VERSION),
				'basic_plugin' => 'xa-woocommerce-subscriptions/xa-woocommerce-subscriptions.php'
			)

		);

		if (isset($wt_iew_post_types[$post_type])) {
			return $wt_iew_post_types[$post_type];
		} else {
			return false;
		}
	}
	?>
	<div class="wt_iew_suite_banner">
		<div class="wt_iew_suite_banner_border"></div>
		<p style="font-size: 13px; font-weight: 400; margin-top: -61px;margin-left: 13px; padding: 10px 10px;">
			<strong><?php esc_html_e('💡 Did You Know?', 'users-customers-import-export-for-wp-woocommerce'); ?></strong> <?php esc_html_e('You can now get an all-in-one bundled solution to import and export WooCommerce products, orders, users, and more with premium exclusive features. Get', 'users-customers-import-export-for-wp-woocommerce'); ?>
			<a href="<?php echo esc_url("https://www.webtoffee.com/product/woocommerce-import-export-suite/?utm_source=free_plugin_data_type&utm_medium=basic_revamp&utm_campaign=Import_Export_Suite" . WT_U_IEW_VERSION); ?>" style="color: blue;" target="_blank"><?php esc_html_e('Import Export Suite for WooCommerce.', 'users-customers-import-export-for-wp-woocommerce'); ?></a>
		</p>
	</div>
</div>