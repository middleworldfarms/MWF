<?php

$ds_obj = Wbte\Pimpexp\Ds\Wbte_Ds::get_instance(WT_P_IEW_VERSION);
$wf_admin_view_path=plugin_dir_path(WT_P_IEW_PLUGIN_FILENAME).'admin/views/';

// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- Already escaped in the get_component.
echo $ds_obj->get_component('header', array( // @codingStandardsIgnoreLine
	'values' => array(
		'plugin_logo' => WT_P_IEW_PLUGIN_URL . 'assets/images/plugin_img.png',
		'plugin_name' => esc_html__('WebToffee Import Export', 'product-import-export-for-woo'),
		'developed_by_txt' => esc_html__('Developed by ', 'product-import-export-for-woo')
	),
	'class' => array(''),
));

echo $ds_obj->get_component('help-widget', array( // @codingStandardsIgnoreLine
	'values' => array(
		'items' => array(
			array('title' => esc_html__('FAQ', 'product-import-export-for-woo'), 'icon' => 'chat-1', 'href' => 'https://wordpress.org/plugins/product-import-export-for-woo/#:~:text=Export%20for%20WooCommerce-,FAQ,-Import%20of%20attributes', 'target' => '_blank'),
			array('title' => esc_html__('Setup guide', 'product-import-export-for-woo'), 'icon' => 'book', 'href' => 'https://www.webtoffee.com/category/basic-plugin-documentation/#:~:text=Product%20Import/Export', 'target' => '_blank'),
			array('title' => esc_html__('Contact support', 'product-import-export-for-woo'), 'icon' => 'headphone', 'href' => 'https://wordpress.org/support/plugin/product-import-export-for-woo/ ', 'target' => '_blank'),
			array('title' => esc_html__('Request a feature', 'product-import-export-for-woo'), 'icon' => 'light-bulb-1'),
		),
		'hover_text' => esc_html__('Help', 'product-import-export-for-woo'),
	)
));
// phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped.

include $wf_admin_view_path."top_upgrade_header.php";
