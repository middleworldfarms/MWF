<?php
/**
 * Variable Subscription Product Class
 * 
 * Extends WooCommerce variable product to add subscription capabilities
 * without requiring the WooCommerce Subscriptions plugin.
 *
 * @package MWF_Subscriptions
 * @since 1.1.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Only define class if it doesn't already exist (avoid conflicts with other plugins)
if (!class_exists('WC_Product_Variable_Subscription')) {
    
    /**
     * Custom product class for variable subscription products
     * 
     * This class allows products to be marked as subscriptions while using
     * WooCommerce's standard variable product functionality. The actual
     * subscription management is handled by the Laravel backend.
     */
    class WC_Product_Variable_Subscription extends WC_Product_Variable {
    
    /**
     * Return the product type
     *
     * @return string
     */
    public function get_type() {
        return 'variable-subscription';
    }
    
    /**
     * Get the add to cart button text for the single product page
     *
     * @return string
     */
    public function single_add_to_cart_text() {
        if ($this->is_purchasable() && $this->is_in_stock()) {
            return __('Sign up now', 'mwf-subscriptions');
        }
        
        return parent::add_to_cart_text();
    }
    
    /**
     * Get the add to cart button text for product archives
     *
     * @return string
     */
    public function add_to_cart_text() {
        if ($this->is_purchasable() && $this->is_in_stock()) {
            return __('Select options', 'mwf-subscriptions');
        }
        
        return parent::add_to_cart_text();
    }
    
    /**
     * Returns whether or not the product is a subscription
     * 
     * Used by checkout and cart to determine if special handling is needed
     *
     * @return bool
     */
    public function is_subscription() {
        return true;
    }
    
    /**
     * Override to ensure variable subscriptions are virtual by default
     * This prevents shipping calculations unless explicitly enabled
     *
     * @return bool
     */
    public function is_virtual() {
        return get_post_meta($this->get_id(), '_virtual', true) === 'yes';
    }
    
    /**
     * Get the price suffix with billing period
     * 
     * Shows prices like "£10.00 / week" instead of just "£10.00"
     *
     * @return string
     */
    public function get_price_suffix($price = '', $qty = 1) {
        $suffix = parent::get_price_suffix($price, $qty);
        
        // Add billing period to suffix
        $billing_period = get_post_meta($this->get_id(), '_mwf_billing_period', true);
        
        if (empty($billing_period)) {
            // Default to 'week' for vegbox subscriptions
            $billing_period = 'week';
        }
        
        $period_text = $this->get_billing_period_text($billing_period);
        
        if ($period_text) {
            $suffix .= ' <span class="mwf-billing-period">/ ' . esc_html($period_text) . '</span>';
        }
        
        return $suffix;
    }
    
    /**
     * Get the price HTML with billing period
     * 
     * Overrides the default price display to include subscription period
     *
     * @param string $price The price HTML
     * @return string
     */
    public function get_price_html($price = '') {
        $price_html = parent::get_price_html($price);
        
        if (empty($price_html)) {
            return $price_html;
        }
        
        // Get billing period
        $billing_period = get_post_meta($this->get_id(), '_mwf_billing_period', true);
        
        if (empty($billing_period)) {
            // Default to 'week' for vegbox subscriptions
            $billing_period = 'week';
        }
        
        $period_text = $this->get_billing_period_text($billing_period);
        
        if ($period_text) {
            // Add period suffix to price HTML
            $price_html .= ' <span class="mwf-subscription-period">/ ' . esc_html($period_text) . '</span>';
        }
        
        return $price_html;
    }
    
    /**
     * Convert billing period key to display text
     *
     * @param string $period The billing period key (day, week, month, year)
     * @return string The display text
     */
    private function get_billing_period_text($period) {
        $periods = [
            'day'   => __('day', 'mwf-subscriptions'),
            'week'  => __('week', 'mwf-subscriptions'),
            'month' => __('month', 'mwf-subscriptions'),
            'year'  => __('year', 'mwf-subscriptions'),
        ];
        
        return isset($periods[$period]) ? $periods[$period] : '';
    }
}

} // End if class_exists check
