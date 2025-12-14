<?php
/**
 * MWF My Account Integration
 * 
 * Adds subscriptions management to WooCommerce My Account
 */

if (!defined('ABSPATH')) {
    exit;
}

class MWF_My_Account {
    
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
        // Add "Subscriptions" tab to My Account
        add_filter('woocommerce_account_menu_items', [$this, 'add_subscriptions_menu_item'], 40);
        
        // Register endpoints using WooCommerce filter
        add_filter('woocommerce_get_query_vars', [$this, 'add_wc_query_vars']);
        
        // Display subscriptions content
        add_action('woocommerce_account_mwf-subscriptions_endpoint', [$this, 'subscriptions_content']);
        add_action('woocommerce_account_mwf-view-subscription_endpoint', [$this, 'view_subscription_content']);
        
        // Handle subscription actions (AJAX)
        add_action('wp_ajax_mwf_pause_subscription', [$this, 'ajax_pause_subscription']);
        add_action('wp_ajax_mwf_resume_subscription', [$this, 'ajax_resume_subscription']);
        add_action('wp_ajax_mwf_cancel_subscription', [$this, 'ajax_cancel_subscription']);
        add_action('wp_ajax_mwf_change_plan', [$this, 'ajax_change_plan']);
    }
    
    /**
     * Add "Subscriptions" menu item
     */
    public function add_subscriptions_menu_item($items) {
        // Insert after "Orders"
        $new_items = [];
        foreach ($items as $key => $value) {
            $new_items[$key] = $value;
            if ($key === 'orders') {
                $new_items['mwf-subscriptions'] = __('Subscriptions', 'mwf-subscriptions');
            }
        }
        return $new_items;
    }
    
    /**
     * Add query vars to WooCommerce (WooCommerce way)
     */
    public function add_wc_query_vars($query_vars) {
        $query_vars['mwf-subscriptions'] = 'mwf-subscriptions';
        $query_vars['mwf-view-subscription'] = 'mwf-view-subscription';
        return $query_vars;
    }
    
    /**
     * Display subscriptions list
     */
    public function subscriptions_content() {
        $user_id = get_current_user_id();
        $api = MWF_API_Client::instance();
        $response = $api->get_user_subscriptions($user_id);
        
        if (!$response['success']) {
            wc_add_notice($response['message'] ?? __('Unable to load subscriptions.', 'mwf-subscriptions'), 'error');
            $subscriptions = [];
        } else {
            $subscriptions = $response['subscriptions'] ?? [];
        }
        
        include MWF_SUBS_PLUGIN_DIR . 'templates/my-account/subscriptions.php';
    }
    
    /**
     * Display single subscription view
     */
    public function view_subscription_content($subscription_id) {
        if (empty($subscription_id)) {
            wc_add_notice(__('Invalid subscription ID.', 'mwf-subscriptions'), 'error');
            return;
        }
        
        $api = MWF_API_Client::instance();
        $response = $api->get_subscription($subscription_id);
        
        if (!$response['success']) {
            wc_add_notice($response['message'] ?? __('Subscription not found.', 'mwf-subscriptions'), 'error');
            return;
        }
        
        $subscription = $response['subscription'];
        
        include MWF_SUBS_PLUGIN_DIR . 'templates/my-account/subscription-detail.php';
    }
    
    /**
     * AJAX: Pause subscription
     */
    public function ajax_pause_subscription() {
        check_ajax_referer('mwf_subscriptions', 'nonce');
        
        if (!is_user_logged_in()) {
            wp_send_json([
                'success' => false,
                'message' => __('You must be logged in.', 'mwf-subscriptions')
            ]);
        }
        
        $subscription_id = isset($_POST['subscription_id']) ? intval($_POST['subscription_id']) : 0;
        $pause_until = isset($_POST['pause_until']) ? sanitize_text_field($_POST['pause_until']) : '';
        
        if (empty($subscription_id) || empty($pause_until)) {
            wp_send_json([
                'success' => false,
                'message' => __('Invalid parameters.', 'mwf-subscriptions')
            ]);
        }
        
        // Validate date format
        $date = DateTime::createFromFormat('Y-m-d', $pause_until);
        if (!$date || $date->format('Y-m-d') !== $pause_until) {
            wp_send_json([
                'success' => false,
                'message' => __('Invalid date format.', 'mwf-subscriptions')
            ]);
        }
        
        $api = MWF_API_Client::instance();
        $response = $api->pause_subscription($subscription_id, $pause_until);
        
        wp_send_json($response);
    }
    
    /**
     * AJAX: Resume subscription
     */
    public function ajax_resume_subscription() {
        check_ajax_referer('mwf_subscriptions', 'nonce');
        
        if (!is_user_logged_in()) {
            wp_send_json([
                'success' => false,
                'message' => __('You must be logged in.', 'mwf-subscriptions')
            ]);
        }
        
        $subscription_id = isset($_POST['subscription_id']) ? intval($_POST['subscription_id']) : 0;
        
        if (empty($subscription_id)) {
            wp_send_json([
                'success' => false,
                'message' => __('Invalid subscription ID.', 'mwf-subscriptions')
            ]);
        }
        
        $api = MWF_API_Client::instance();
        $response = $api->resume_subscription($subscription_id);
        
        wp_send_json($response);
    }
    
    /**
     * AJAX: Cancel subscription
     */
    public function ajax_cancel_subscription() {
        check_ajax_referer('mwf_subscriptions', 'nonce');
        
        if (!is_user_logged_in()) {
            wp_send_json([
                'success' => false,
                'message' => __('You must be logged in.', 'mwf-subscriptions')
            ]);
        }
        
        $subscription_id = isset($_POST['subscription_id']) ? intval($_POST['subscription_id']) : 0;
        $confirm = isset($_POST['confirm']) ? sanitize_text_field($_POST['confirm']) : '';
        
        if (empty($subscription_id) || $confirm !== 'yes') {
            wp_send_json([
                'success' => false,
                'message' => __('Please confirm cancellation.', 'mwf-subscriptions')
            ]);
        }
        
        $api = MWF_API_Client::instance();
        $response = $api->cancel_subscription($subscription_id);
        
        wp_send_json($response);
    }
    
    /**
     * AJAX: Change subscription plan (upgrade/downgrade)
     */
    public function ajax_change_plan() {
        check_ajax_referer('mwf_subscriptions', 'nonce');
        
        if (!is_user_logged_in()) {
            wp_send_json([
                'success' => false,
                'message' => __('You must be logged in.', 'mwf-subscriptions')
            ]);
        }
        
        $subscription_id = isset($_POST['subscription_id']) ? intval($_POST['subscription_id']) : 0;
        $new_plan_id = isset($_POST['new_plan_id']) ? intval($_POST['new_plan_id']) : 0;
        
        if (empty($subscription_id) || empty($new_plan_id)) {
            wp_send_json([
                'success' => false,
                'message' => __('Invalid subscription or plan ID.', 'mwf-subscriptions')
            ]);
        }
        
        $api = MWF_API_Client::instance();
        $response = $api->change_plan($subscription_id, $new_plan_id);
        
        wp_send_json($response);
    }
}
