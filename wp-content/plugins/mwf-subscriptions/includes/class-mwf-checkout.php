<?php
/**
 * MWF Checkout Integration
 * 
 * Auto-sets delivery day based on shipping method
 */

if (!defined('ABSPATH')) {
    exit;
}

class MWF_Checkout {
    
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
        // Save delivery day automatically based on shipping method (early priority)
        add_action('woocommerce_checkout_update_order_meta', [$this, 'save_delivery_day'], 5);
        
        // Create subscription after order completion (later priority to ensure meta is saved)
        add_action('woocommerce_order_status_completed', [$this, 'create_subscription'], 20, 1);
        add_action('woocommerce_order_status_processing', [$this, 'create_subscription'], 20, 1);
        add_action('woocommerce_payment_complete', [$this, 'create_subscription'], 20, 1);
    }
    
    /**
     * Auto-set delivery day based on shipping method
     */
    public function save_delivery_day($order_id) {
        $order = wc_get_order($order_id);
        
        if (!$order) {
            return;
        }
        
        // Auto-set delivery day based on shipping method
        $shipping_method = $order->get_shipping_method();
        
        // Get delivery days from settings (configurable in WordPress admin)
        $collection_day = get_option('mwf_collection_delivery_day', 'saturday');
        $default_day = get_option('mwf_default_delivery_day', 'thursday');
        
        // Collection = configured collection day, Delivery = configured default day
        $delivery_day = (stripos($shipping_method, 'collection') !== false || stripos($shipping_method, 'collect') !== false) 
            ? $collection_day 
            : $default_day;
        
        update_post_meta($order_id, '_mwf_delivery_day', $delivery_day);
        error_log("[MWF Checkout] Auto-set delivery day to '{$delivery_day}' for order {$order_id} (shipping: {$shipping_method})");
    }
    
    /**
     * Create subscription via API after order completes
     */
    public function create_subscription($order_id) {
        error_log("=====================================");
        error_log("[MWF Checkout] create_subscription() TRIGGERED for order {$order_id}");
        error_log("[MWF Checkout] Called from hook: " . current_filter());
        error_log("=====================================");
        
        $order = wc_get_order($order_id);
        
        if (!$order) {
            error_log("[MWF Checkout] ERROR: Could not load order {$order_id}");
            return;
        }
        
        error_log("[MWF Checkout] Order loaded - Status: " . $order->get_status());
        error_log("[MWF Checkout] Order user ID: " . $order->get_user_id());
        
        // Check if this is a renewal order - don't create new subscription
        $renewal_subscription_id = get_post_meta($order_id, '_subscription_renewal', true);
        if (!empty($renewal_subscription_id)) {
            error_log("[MWF Subscriptions] Order {$order_id} is a renewal for subscription {$renewal_subscription_id} - skipping subscription creation");
            return;
        }
        
        // Check if subscription already created
        $existing_subscription_id = get_post_meta($order_id, '_mwf_subscription_id', true);
        if (!empty($existing_subscription_id)) {
            error_log("[MWF Subscriptions] Subscription #{$existing_subscription_id} already exists for order {$order_id}");
            return;
        }
        
        // Check if order contains subscription products
        if (!$this->order_contains_subscription_product($order)) {
            error_log("[MWF Checkout] Order does not contain subscription products - skipping");
            return;
        }
        
        error_log("[MWF Checkout] Order contains subscription products - proceeding...");
        
        // Get delivery day
        $delivery_day = get_post_meta($order_id, '_mwf_delivery_day', true);
        if (empty($delivery_day)) {
            error_log("[MWF Subscriptions] No delivery day meta found - auto-detecting from shipping method...");
            // Fallback: Auto-detect delivery day if not set yet
            $shipping_method = $order->get_shipping_method();
            $collection_day = get_option('mwf_collection_delivery_day', 'saturday');
            $default_day = get_option('mwf_default_delivery_day', 'thursday');
            $delivery_day = (stripos($shipping_method, 'collection') !== false || stripos($shipping_method, 'collect') !== false) 
                ? $collection_day 
                : $default_day;
            update_post_meta($order_id, '_mwf_delivery_day', $delivery_day);
            error_log("[MWF Subscriptions] Auto-detected delivery day: {$delivery_day} (shipping: {$shipping_method})");
        } else {
            error_log("[MWF Checkout] Delivery day: {$delivery_day}");
        }
        
        // Get plan ID from product meta
        $plan_id = $this->get_plan_id_from_order($order);
        if (!$plan_id) {
            error_log("[MWF Subscriptions] ERROR: No plan ID found for order {$order_id}");
            $order->add_order_note(__('Failed to create subscription: No plan ID configured on product.', 'mwf-subscriptions'));
            return;
        }
        
        error_log("[MWF Checkout] Plan ID: {$plan_id}");
        
        // Get product ID from order
        $product_id = null;
        foreach ($order->get_items() as $item) {
            $product_id = $item->get_product_id();
            if ($product_id) break;
        }
        
        // Get variation data if this is a variable subscription
        $variation_data = $this->get_variation_data_from_order($order);
        
        // Create subscription via API
        $api_data = [
            'wordpress_user_id' => $order->get_user_id(),
            'wordpress_order_id' => $order_id,
            'wc_order_id' => $order_id,
            'product_id' => $product_id,
            'plan_id' => $plan_id,
            'billing_period' => 'week',
            'billing_interval' => 1,
            'billing_amount' => $order->get_total(),
            'delivery_day' => $delivery_day,
            'customer_email' => $order->get_billing_email(),
            'delivery_address' => [
                'address_1' => $order->get_shipping_address_1(),
                'address_2' => $order->get_shipping_address_2(),
                'city' => $order->get_shipping_city(),
                'postcode' => $order->get_shipping_postcode(),
                'country' => $order->get_shipping_country(),
                'state' => $order->get_shipping_state(),
            ],
            'price' => $order->get_total(),
        ];
        
        // Add variation data if available
        if (!empty($variation_data)) {
            $api_data['variation_id'] = $variation_data['variation_id'];
            $api_data['variation_name'] = $variation_data['variation_name'];
            $api_data['variation_sku'] = $variation_data['variation_sku'] ?? '';
            $api_data['variation_attributes'] = $variation_data['attributes'] ?? [];
            $api_data['formatted_variation'] = $variation_data['formatted_name'] ?? '';
            error_log("[MWF Checkout] Added variation data to API payload");
        }
        
        error_log("[MWF Checkout] Calling API to create subscription...");
        error_log("[MWF Checkout] API data: " . json_encode($api_data));
        
        $api = MWF_API_Client::instance();
        $response = $api->create_subscription($api_data);
        
        error_log("[MWF Checkout] API response: " . json_encode($response));
        
        if ($response['success']) {
            // Store subscription ID in order meta
            $subscription_id = $response['subscription_id'] ?? null;
            if ($subscription_id) {
                update_post_meta($order_id, '_mwf_subscription_id', $subscription_id);
                $order->add_order_note(
                    sprintf(
                        __('Vegbox subscription #%d created successfully.', 'mwf-subscriptions'),
                        $subscription_id
                    )
                );
                
                error_log(sprintf(
                    "[MWF Subscriptions] ✅ SUCCESS: Created subscription #%d for order #%d",
                    $subscription_id,
                    $order_id
                ));
            } else {
                error_log("[MWF Subscriptions] ⚠️ WARNING: Success response but no subscription_id for order {$order_id}");
            }
        } else {
            error_log("[MWF Subscriptions] ❌ FAILED to create subscription for order {$order_id}: " . ($response['message'] ?? 'Unknown error'));
            $order->add_order_note(__('Failed to create vegbox subscription. Please create manually or contact support.', 'mwf-subscriptions'));
        }
    }
    
    /**
     * Check if order contains subscription product
     */
    private function order_contains_subscription_product($order) {
        $items = $order->get_items();
        error_log('[MWF Checkout] Checking ' . count($items) . ' order items for subscription products');
        
        foreach ($items as $item) {
            $product_id = $item->get_product_id();
            $is_subscription = get_post_meta($product_id, '_is_vegbox_subscription', true);
            error_log("[MWF Checkout] Order item - Product ID {$product_id}: _is_vegbox_subscription = " . var_export($is_subscription, true));
            
            if ($is_subscription === 'yes') {
                error_log("[MWF Checkout] ✅ Found subscription product in order: {$product_id}");
                return true;
            }
        }
        
        error_log('[MWF Checkout] No subscription products found in order');
        return false;
    }
    
    /**
     * Get plan ID from order
     */
    private function get_plan_id_from_order($order) {
        foreach ($order->get_items() as $item) {
            $product_id = $item->get_product_id();
            $plan_id = get_post_meta($product_id, '_vegbox_plan_id', true);
            error_log("[MWF Checkout] Product ID {$product_id}: _vegbox_plan_id = " . var_export($plan_id, true));
            
            if ($plan_id) {
                error_log("[MWF Checkout] Found plan ID: {$plan_id}");
                return intval($plan_id);
            }
        }
        
        error_log('[MWF Checkout] No plan ID found in any order items');
        return null;
    }
    
    /**
     * Get variation data from order
     */
    private function get_variation_data_from_order($order) {
        foreach ($order->get_items() as $item) {
            $product_id = $item->get_product_id();
            $is_subscription = get_post_meta($product_id, '_is_vegbox_subscription', true);
            
            if ($is_subscription !== 'yes') {
                continue;
            }
            
            $variation_id = $item->get_variation_id();
            
            if (!$variation_id) {
                error_log("[MWF Checkout] Product {$product_id} is not a variation");
                continue;
            }
            
            $variation = wc_get_product($variation_id);
            
            if (!$variation) {
                error_log("[MWF Checkout] Could not load variation {$variation_id}");
                continue;
            }
            
            // Get variation attributes from the order item
            $attributes = [];
            $item_data = $item->get_meta_data();
            foreach ($item_data as $meta) {
                $key = $meta->key;
                if (strpos($key, 'pa_') !== false || strpos($key, 'attribute_') !== false) {
                    $attributes[$key] = $meta->value;
                }
            }
            
            // Also get formatted variation attributes
            $formatted_attributes = $variation->get_variation_attributes();
            
            $variation_data = [
                'variation_id' => $variation_id,
                'variation_name' => $variation->get_name(),
                'variation_sku' => $variation->get_sku(),
                'attributes' => !empty($attributes) ? $attributes : $formatted_attributes,
                'formatted_name' => wc_get_formatted_variation($variation, true, false),
            ];
            
            error_log("[MWF Checkout] Found variation data: " . json_encode($variation_data));
            
            return $variation_data;
        }
        
        error_log('[MWF Checkout] No variation found in subscription products');
        return [];
    }
}
