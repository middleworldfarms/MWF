<?php
/**
 * This file belongs to the YIT Plugin Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 *
 *  @package YITH WooCommerce Name Your Price Premium
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$settings = array(

	'category-rules-vendor' => array(

		'category_rules_vendor' => array(
			'type'         => 'custom_tab',
			'action'       => 'yith_wc_name_your_price_category_vendor_rules',
			'hide_sidebar' => true,
		),

	),
);


return apply_filters( 'ywcnp_category_vendor_rules', $settings );
