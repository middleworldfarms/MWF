<?php
/**
 * MWF API Client
 * 
 * Handles all communication with Laravel backend API
 */

if (!defined('ABSPATH')) {
    exit;
}

class MWF_API_Client {
    
    private static $instance = null;
    
    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Make API request to Laravel backend
     */
    private function request($endpoint, $method = 'GET', $data = []) {
        $api_url = get_option('mwf_api_url', MWF_SUBS_API_URL);
        $url = trailingslashit($api_url) . ltrim($endpoint, '/');
        
        $args = [
            'method' => $method,
            'headers' => [
                'X-MWF-API-Key' => get_option('mwf_api_key', MWF_SUBS_API_KEY),
                'Content-Type' => 'application/json',
            ],
            'timeout' => 30,
        ];
        
        if (!empty($data) && in_array($method, ['POST', 'PUT', 'PATCH'])) {
            $args['body'] = json_encode($data);
        }
        
        // Log request for debugging
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log(sprintf(
                '[MWF API] %s %s %s',
                $method,
                $url,
                !empty($data) ? json_encode($data) : ''
            ));
        }
        
        $response = wp_remote_request($url, $args);
        
        if (is_wp_error($response)) {
            error_log('[MWF API Error] ' . $response->get_error_message());
            return [
                'success' => false,
                'message' => $response->get_error_message()
            ];
        }
        
        $body = wp_remote_retrieve_body($response);
        $decoded = json_decode($body, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log('[MWF API JSON Error] ' . json_last_error_msg());
            error_log('[MWF API Raw Body] ' . substr($body, 0, 500));
            return [
                'success' => false,
                'message' => 'Invalid API response: ' . json_last_error_msg()
            ];
        }
        
        // Log response for debugging
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('[MWF API Response] ' . wp_remote_retrieve_response_code($response) . ': ' . substr($body, 0, 500));
        }
        
        return $decoded;
    }
    
    /**
     * Get user subscriptions from Laravel API
     * Endpoint: GET /api/subscriptions/user/{wordpress_user_id}
     */
    public function get_user_subscriptions($user_id) {
        return $this->request("/user/{$user_id}", 'GET');
    }
    
    /**
     * Create subscription in Laravel
     * Endpoint: POST /api/subscriptions
     */
    public function create_subscription($data) {
        return $this->request('/create', 'POST', $data);
    }
    
    /**
     * Get subscription details from Laravel
     * Endpoint: GET /api/subscriptions/{id}
     */
    public function get_subscription($subscription_id) {
        return $this->request("/{$subscription_id}", 'GET');
    }
    
    /**
     * Cancel subscription
     * Endpoint: POST /api/subscriptions/{id}/cancel
     */
    public function cancel_subscription($subscription_id) {
        return $this->request("/{$subscription_id}/action", 'POST', [
            'action' => 'cancel'
        ]);
    }

    public function change_plan($subscription_id, $new_plan_id) {
        return $this->request("/{$subscription_id}/action", 'POST', [
            'action' => 'change_plan',
            'new_plan_id' => $new_plan_id
        ]);
    }
    
    /**
     * Pause subscription
     * Endpoint: POST /api/subscriptions/{id}/pause
     */
    public function pause_subscription($subscription_id, $pause_until = null) {
        $data = [];
        if ($pause_until) {
            $data['pause_until'] = $pause_until;
        }
        return $this->request("/{$subscription_id}/pause", 'POST', $data);
    }
    
    /**
     * Resume subscription
     * Endpoint: POST /api/subscriptions/{id}/resume
     */
    public function resume_subscription($subscription_id) {
        return $this->request("/{$subscription_id}/action", 'POST', [
            'action' => 'resume'
        ]);
    }
    
    /**
     * Update subscription address
     */
    public function update_subscription_address($subscription_id, $address_data) {
        return $this->request("/{$subscription_id}/update-address", 'POST', $address_data);
    }
    
    /**
     * Get payment history
     */
    public function get_payment_history($subscription_id) {
        return $this->request("/{$subscription_id}/payments", 'GET');
    }
}
