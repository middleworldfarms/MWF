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
$setting = array(

	'general-settings' => array(

		'section_shop_label_settings'        => array(

			'type' => 'title',
			'name' => __( 'Shop Label', 'yith-woocommerce-name-your-price' ),
			'id'   => 'ywcnp_section_shop_label_general',
		),


		'suggest_price_text'                 => array(
			'name'    => __( 'Text before recommended price ', 'yith-woocommerce-name-your-price' ),
			'desc'    => __( 'This text is displayed before the suggested price', 'yith-woocommerce-name-your-price' ),
			'type'    => 'text',
			'id'      => 'ywcnp_suggest_price_label',
			'std'     => __( 'Recommended Price', 'yith-woocommerce-name-your-price' ),
			'default' => __( 'Recommended Price', 'yith-woocommerce-name-your-price' ),
		),
		'min_price_text'                     => array(
			'name'    => __( 'Text before minimum price allowed ', 'yith-woocommerce-name-your-price' ),
			'desc'    => __( 'This text is displayed before the minimum price', 'yith-woocommerce-name-your-price' ),
			'type'    => 'text',
			'id'      => 'ywcnp_min_price_label',
			'std'     => __( 'Minimum price allowed', 'yith-woocommerce-name-your-price' ),
			'default' => __( 'Minimum price allowed', 'yith-woocommerce-name-your-price' ),
		),
		'max_price_text'                     => array(

			'name'    => __( 'Text before maximum price allowed ', 'yith-woocommerce-name-your-price' ),
			'desc'    => __( 'This text is displayed before the maximum price', 'yith-woocommerce-name-your-price' ),
			'type'    => 'text',
			'id'      => 'ywcnp_max_price_label',
			'std'     => __( 'Maximum price allowed', 'yith-woocommerce-name-your-price' ),
			'default' => __( 'Maximum price allowed', 'yith-woocommerce-name-your-price' ),
		),

		'button_loop_text'                   => array(
			'name'    => __( '"Add to Cart" text in Shop page', 'yith-woocommerce-name-your-price' ),
			'desc'    => __( 'Set the text of the "Add to Cart" button in the Shop page', 'yith-woocommerce-name-your-price' ),
			'type'    => 'text',
			'id'      => 'ywcnp_button_loop_label',
			'std'     => __( 'Choose Price', 'yith-woocommerce-name-your-price' ),
			'default' => __( 'Choose Price', 'yith-woocommerce-name-your-price' ),
		),

		'button_single_text'                 => array(
			'name'    => __( '"Add to Cart" text in Single Product page', 'yith-woocommerce-name-your-price' ),
			'desc'    => __( 'Set the text of the "Add to Cart" button in the Single Product page', 'yith-woocommerce-name-your-price' ),
			'type'    => 'text',
			'id'      => 'ywcnp_button_single_label',
			'std'     => __( 'Add to Cart', 'yith-woocommerce-name-your-price' ),
			'default' => __( 'Add to Cart', 'yith-woocommerce-name-your-price' ),
		),

		'nameprice_text'                     => array(
			'name'    => __( '"Name Your Price" text', 'yith-woocommerce-name-your-price' ),
			'desc'    => __( 'This text is displayed before the price field', 'yith-woocommerce-name-your-price' ),
			'type'    => 'text',
			'id'      => 'ywcnp_name_your_price_label',
			'std'     => __( 'Name Your Price', 'yith-woocommerce-name-your-price' ),
			'default' => __( 'Name Your Price', 'yith-woocommerce-name-your-price' ),
		),

		'section_shop_label_settings_end'    => array(
			'type' => 'sectionend',
			'id'   => 'ywcnp_section_shop_label_general_end',
		),

		'section_message_label_settings'     => array(
			'name' => __( 'Message Label', 'yith-woocommerce-name-your-price' ),
			'type' => 'title',
			'id'   => 'ywcnp_section_message_label',
		),


		'negative_price'                     => array(
			'name'    => __( 'Negative Price', 'yith-woocommerce-name-your-price' ),
			'desc'    => __( 'This message will be showed when users try to add a price lower than 0', 'yith-woocommerce-name-your-price' ),
			'id'      => 'ywcnp_negative_price_label',
			'default' => __( 'Please enter a value greater or equal to 0', 'yith-woocommerce-name-your-price' ),
			'type'    => 'text',
			'class'   => 'widefat',
		),

		'invalid_price'                      => array(
			'name'    => __( 'Invalid Price', 'yith-woocommerce-name-your-price' ),
			'desc'    => __( 'This message will be showed when users try to add a price with an invalid format', 'yith-woocommerce-name-your-price' ),
			'id'      => 'ywcnp_invalid_price_label',
			'default' => __( 'Please enter a valid price', 'yith-woocommerce-name-your-price' ),
			'type'    => 'text',
			'class'   => 'widefat',
		),

		'minimum_price'                      => array(
			'name'    => __( 'Minimum Price', 'yith-woocommerce-name-your-price' ),
			/* translators: %s: Is the placeholder to show the minimum price */
			'desc'    => sprintf( __( 'This message will be showed when users try to add a price lower than the minimum price set. Use %s to show the minumum price allowed', 'yith-woocommerce-name-your-price' ), '<code>{ywcnp_minimum_price}</code>' ),
			'id'      => 'ywcnp_min_price_error_label',
			'default' => __( 'Please enter a value greater or equal to minimum', 'yith-woocommerce-name-your-price' ),
			'type'    => 'text',
			'class'   => 'widefat',
		),
		'maximum_price'                      => array(
			'name'    => __( 'Maximum Price', 'yith-woocommerce-name-your-price' ),
			/* translators: %s: Is the placeholder to show the maximum price */
			'desc'    => sprintf( __( 'This message will be showed when users try to add a price higher than the maximum price set. Use %s to show the maximum price allowed', 'yith-woocommerce-name-your-price' ), '<code>{ywcnp_maximum_price}</code>' ),
			'id'      => 'ywcnp_max_price_error_label',
			'default' => __( 'Please enter a value less or equal to maximum', 'yith-woocommerce-name-your-price' ),
			'type'    => 'text',
			'class'   => 'widefat',
		),

		'section_message_label_settings_end' => array(
			'type' => 'sectionend',
			'id'   => 'ywcnp_section_message_label_general_end',
		),
	),
);

return apply_filters( 'yith_wc_name_your_price_premium_settings', $setting );
