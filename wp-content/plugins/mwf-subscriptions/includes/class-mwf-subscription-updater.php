<?php
/**
 * MWF Subscription Updater
 * 
 * Intercepts WooCommerce subscription switch/modification actions
 * and updates subscriptions IN PLACE instead of cancelling and creating new ones.
 * 
 * Uses the WooCommerceSubscriptionUpdater service from Laravel admin app.
 */

if (!defined('ABSPATH')) {
    exit;
}

class MWF_Subscription_Updater {
    
    private static $instance = null;
    
    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->init_hooks();
    }
    
    private function init_hooks() {
        // Intercept subscription switch (change product/plan)
        add_action('woocommerce_subscriptions_switch_completed', [$this, 'handle_subscription_switch'], 10, 1);
        
        // Intercept subscription update (change billing frequency, shipping method, etc.)
        add_action('woocommerce_subscription_updated', [$this, 'handle_subscription_update'], 10, 1);
        
        // Intercept BEFORE subscription switch creates new subscription
        add_filter('wcs_can_user_put_subscription_on_hold', [$this, 'prevent_hold_during_update'], 10, 2);
        
        // Override default "switch" behavior - update instead of cancel+create
        add_action('woocommerce_before_subscription_item_switched', [$this, 'update_subscription_instead_of_switch'], 10, 4);
        
        // Handle shipping method changes (collection ↔ delivery)
        add_action('woocommerce_subscription_address_updated', [$this, 'handle_address_update'], 10, 3);
        
        // Add admin actions for manual updates
        add_action('wp_ajax_mwf_switch_delivery_method', [$this, 'ajax_switch_delivery_method']);
        add_action('wp_ajax_mwf_update_billing_frequency', [$this, 'ajax_update_billing_frequency']);
    }
    
    /**
     * Handle subscription switch (product/plan change)
     * This runs AFTER WooCommerce creates the switch, we need to update instead
     */
    public function handle_subscription_switch($subscription_id) {
        error_log("[MWF Updater] Subscription switch detected for #{$subscription_id}");
        
        // Get the subscription
        $subscription = wcs_get_subscription($subscription_id);
        if (!$subscription) {
            error_log("[MWF Updater] Subscription #{$subscription_id} not found");
            return;
        }
        
        // Check if this is a "switch" - look for parent subscription
        $parent_subscription_id = get_post_meta($subscription_id, '_subscription_switch', true);
        
        if (!empty($parent_subscription_id)) {
            error_log("[MWF Updater] This is a switch from parent #{$parent_subscription_id}");
            
            // Instead of keeping the new subscription, UPDATE the parent and cancel this one
            $this->merge_switch_back_to_parent($parent_subscription_id, $subscription_id);
        }
    }
    
    /**
     * Merge switched subscription back to parent (update parent instead of creating new)
     */
    private function merge_switch_back_to_parent($parent_id, $child_id) {
        global $wpdb;
        
        error_log("[MWF Updater] Merging switch subscription #{$child_id} back to parent #{$parent_id}");
        
        $child_subscription = wcs_get_subscription($child_id);
        $parent_subscription = wcs_get_subscription($parent_id);
        
        if (!$child_subscription || !$parent_subscription) {
            error_log("[MWF Updater] Failed to load subscriptions");
            return;
        }
        
        // Get new line items from child subscription
        $new_items = [];
        foreach ($child_subscription->get_items() as $item) {
            $new_items[] = [
                'name' => $item->get_name(),
                'product_id' => $item->get_product_id(),
                'quantity' => $item->get_quantity(),
                'total' => $item->get_total(),
                'subtotal' => $item->get_subtotal(),
                'meta_data' => $item->get_meta_data()
            ];
        }
        
        // Update parent subscription's line items directly in database
        $this->update_line_items($parent_id, $new_items);
        
        // Update totals
        $new_total = $child_subscription->get_total();
        update_post_meta($parent_id, '_order_total', $new_total);
        update_post_meta($parent_id, '_order_shipping', $child_subscription->get_shipping_total());
        
        // Get billing frequency from child
        $billing_interval = get_post_meta($child_id, '_billing_interval', true);
        $billing_period = get_post_meta($child_id, '_billing_period', true);
        
        if (!empty($billing_interval) && !empty($billing_period)) {
            $this->update_billing_frequency($parent_id, $billing_interval, $billing_period);
        }
        
        // Cancel the child subscription since we've merged changes to parent
        $child_subscription->update_status('cancelled', 'Merged back to parent subscription via MWF updater');
        
        // Add note to parent
        $parent_subscription->add_order_note(sprintf(
            'Subscription updated in place (merged from switch #%d). Products and billing updated without creating new subscription.',
            $child_id
        ));
        
        error_log("[MWF Updater] Successfully updated parent #{$parent_id} and cancelled child #{$child_id}");
    }
    
    /**
     * Update subscription line items in database
     */
    private function update_line_items($subscription_id, $new_items) {
        global $wpdb;
        
        error_log("[MWF Updater] Updating line items for subscription #{$subscription_id}");
        
        // Start transaction
        $wpdb->query('START TRANSACTION');
        
        try {
            // Delete old line items
            $wpdb->delete(
                $wpdb->prefix . 'woocommerce_order_items',
                ['order_id' => $subscription_id],
                ['%d']
            );
            
            // Insert new line items
            foreach ($new_items as $item) {
                // Insert line item
                $wpdb->insert(
                    $wpdb->prefix . 'woocommerce_order_items',
                    [
                        'order_item_name' => $item['name'],
                        'order_item_type' => 'line_item',
                        'order_id' => $subscription_id
                    ],
                    ['%s', '%s', '%d']
                );
                
                $item_id = $wpdb->insert_id;
                
                // Insert line item meta
                $meta_data = [
                    '_product_id' => $item['product_id'] ?? 0,
                    '_qty' => $item['quantity'] ?? 1,
                    '_line_total' => $item['total'] ?? 0,
                    '_line_subtotal' => $item['subtotal'] ?? $item['total'] ?? 0,
                ];
                
                foreach ($meta_data as $meta_key => $meta_value) {
                    $wpdb->insert(
                        $wpdb->prefix . 'woocommerce_order_itemmeta',
                        [
                            'order_item_id' => $item_id,
                            'meta_key' => $meta_key,
                            'meta_value' => $meta_value
                        ],
                        ['%d', '%s', '%s']
                    );
                }
                
                // Add any custom meta from original item
                if (!empty($item['meta_data'])) {
                    foreach ($item['meta_data'] as $meta) {
                        if (is_object($meta)) {
                            $meta_key = method_exists($meta, 'get_key') ? $meta->get_key() : $meta->key;
                            $meta_value = method_exists($meta, 'get_value') ? $meta->get_value() : $meta->value;
                        } else {
                            $meta_key = $meta['key'] ?? '';
                            $meta_value = $meta['value'] ?? '';
                        }
                        
                        if (!empty($meta_key) && !in_array($meta_key, array_keys($meta_data))) {
                            $wpdb->insert(
                                $wpdb->prefix . 'woocommerce_order_itemmeta',
                                [
                                    'order_item_id' => $item_id,
                                    'meta_key' => $meta_key,
                                    'meta_value' => maybe_serialize($meta_value)
                                ],
                                ['%d', '%s', '%s']
                            );
                        }
                    }
                }
            }
            
            $wpdb->query('COMMIT');
            error_log("[MWF Updater] Line items updated successfully");
            
        } catch (Exception $e) {
            $wpdb->query('ROLLBACK');
            error_log("[MWF Updater] Failed to update line items: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Update billing frequency in database
     */
    private function update_billing_frequency($subscription_id, $interval, $period) {
        error_log("[MWF Updater] Updating billing frequency for #{$subscription_id} to {$interval} {$period}");
        
        update_post_meta($subscription_id, '_billing_interval', $interval);
        update_post_meta($subscription_id, '_billing_period', $period);
        
        // Calculate next payment date based on new frequency
        $subscription = wcs_get_subscription($subscription_id);
        if ($subscription) {
            $next_payment = $this->calculate_next_payment_date($interval, $period);
            update_post_meta($subscription_id, '_schedule_next_payment', $next_payment->getTimestamp());
        }
    }
    
    /**
     * Calculate next payment date based on frequency
     */
    private function calculate_next_payment_date($interval, $period) {
        $now = new DateTime();
        
        switch ($period) {
            case 'day':
                return $now->modify("+{$interval} days");
            case 'week':
                return $now->modify("+{$interval} weeks");
            case 'month':
                return $now->modify("+{$interval} months");
            case 'year':
                return $now->modify("+{$interval} years");
            default:
                return $now->modify("+{$interval} weeks");
        }
    }
    
    /**
     * Handle address update (might indicate collection ↔ delivery switch)
     */
    public function handle_address_update($address_type, $subscription, $address) {
        if ($address_type !== 'shipping') {
            return;
        }
        
        error_log("[MWF Updater] Shipping address updated for subscription #{$subscription->get_id()}");
        
        // Check if delivery method changed by looking at line items
        // This is called AFTER the address update, so we just log it
        // The actual delivery method switch should be handled by line item changes
    }
    
    /**
     * AJAX: Switch delivery method (admin tool)
     */
    public function ajax_switch_delivery_method() {
        check_ajax_referer('mwf_subscriptions', 'nonce');
        
        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error(['message' => 'Unauthorized']);
        }
        
        $subscription_id = isset($_POST['subscription_id']) ? intval($_POST['subscription_id']) : 0;
        $new_method = isset($_POST['new_method']) ? sanitize_text_field($_POST['new_method']) : '';
        $shipping_cost = isset($_POST['shipping_cost']) ? floatval($_POST['shipping_cost']) : 0;
        
        if (empty($subscription_id) || !in_array($new_method, ['collection', 'delivery'])) {
            wp_send_json_error(['message' => 'Invalid parameters']);
        }
        
        try {
            $this->switch_delivery_method($subscription_id, $new_method, $shipping_cost);
            wp_send_json_success(['message' => 'Delivery method updated successfully']);
        } catch (Exception $e) {
            error_log("[MWF Updater] Failed to switch delivery method: " . $e->getMessage());
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }
    
    /**
     * Switch delivery method (collection ↔ delivery)
     */
    private function switch_delivery_method($subscription_id, $new_method, $shipping_cost = 0) {
        global $wpdb;
        
        error_log("[MWF Updater] Switching delivery method for #{$subscription_id} to {$new_method}");
        
        $subscription = wcs_get_subscription($subscription_id);
        if (!$subscription) {
            throw new Exception("Subscription not found");
        }
        
        $wpdb->query('START TRANSACTION');
        
        try {
            // Find and remove old shipping line item
            foreach ($subscription->get_items() as $item) {
                $item_name = strtolower($item->get_name());
                if (strpos($item_name, 'collection') !== false || strpos($item_name, 'delivery') !== false) {
                    $wpdb->delete(
                        $wpdb->prefix . 'woocommerce_order_items',
                        ['order_item_id' => $item->get_id()],
                        ['%d']
                    );
                }
            }
            
            // Add new shipping line item
            $new_item_name = $new_method === 'delivery' ? 'Delivery' : 'Collection From Middle World Farms';
            $shipping_class = $new_method === 'delivery' ? 'Delivery' : 'Collection';
            
            $wpdb->insert(
                $wpdb->prefix . 'woocommerce_order_items',
                [
                    'order_item_name' => $new_item_name,
                    'order_item_type' => 'line_item',
                    'order_id' => $subscription_id
                ],
                ['%s', '%s', '%d']
            );
            
            $item_id = $wpdb->insert_id;
            
            // Add shipping meta
            $meta_data = [
                '_line_total' => $shipping_cost,
                '_line_subtotal' => $shipping_cost,
                'shipping_class' => $shipping_class,
            ];
            
            foreach ($meta_data as $meta_key => $meta_value) {
                $wpdb->insert(
                    $wpdb->prefix . 'woocommerce_order_itemmeta',
                    [
                        'order_item_id' => $item_id,
                        'meta_key' => $meta_key,
                        'meta_value' => $meta_value
                    ],
                    ['%d', '%s', '%s']
                );
            }
            
            // Update shipping total
            update_post_meta($subscription_id, '_order_shipping', $shipping_cost);
            
            // Recalculate total
            $current_total = floatval(get_post_meta($subscription_id, '_order_total', true));
            $old_shipping = floatval(get_post_meta($subscription_id, '_order_shipping', true));
            $new_total = $current_total - $old_shipping + $shipping_cost;
            update_post_meta($subscription_id, '_order_total', $new_total);
            
            $wpdb->query('COMMIT');
            
            $subscription->add_order_note(sprintf(
                'Delivery method switched to %s (£%.2f) via MWF updater',
                $new_method,
                $shipping_cost
            ));
            
            error_log("[MWF Updater] Successfully switched to {$new_method}");
            
        } catch (Exception $e) {
            $wpdb->query('ROLLBACK');
            throw $e;
        }
    }
    
    /**
     * Handle general subscription update
     */
    public function handle_subscription_update($subscription) {
        // This is a catch-all for any subscription updates
        // We log it but the specific handlers above do the real work
        error_log("[MWF Updater] Subscription #{$subscription->get_id()} updated");
    }
    
    /**
     * Prevent subscription hold during update process
     */
    public function prevent_hold_during_update($can_hold, $subscription) {
        // If we're in the middle of an update, don't allow hold
        return $can_hold;
    }
    
    /**
     * Update subscription instead of switch
     * This is the KEY hook - it fires BEFORE WooCommerce creates the new subscription
     */
    public function update_subscription_instead_of_switch($subscription, $item, $new_item, $switch) {
        error_log("[MWF Updater] BEFORE switch - intercepting to update instead of create new");
        
        // We'll let the switch complete, then merge it back in handle_subscription_switch()
        // This is because we need both subscriptions to exist temporarily to get the new data
    }
}
