# MWF Custom Subscriptions Plugin - Implementation Guide

**Date:** November 17, 2025  
**Project:** Replace WooCommerce Subscriptions addon with custom solution  
**Savings:** £199/year  
**Location:** `/var/www/vhosts/middleworldfarms.org/httpdocs/wp-content/plugins/mwf-subscriptions/`

---

## 🎯 Project Overview

Replace the paid WooCommerce Subscriptions plugin with a custom solution that:
- Uses Laravel backend (admin.middleworldfarms.org:8444) for subscription logic
- Integrates seamlessly into WooCommerce My Account
- Maintains existing WooCommerce checkout flow
- Keeps all WooCommerce features except subscriptions addon

---

## 📋 Phase 1: Laravel API Endpoints (Build First in Laravel Workspace)

### Location: `/opt/sites/admin.middleworldfarms.org/`

### 1. API Routes (`routes/api.php`)

```php
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\VegboxSubscriptionApiController;

// API Authentication Middleware
Route::middleware(['api', 'verify.wc.api.token'])->prefix('subscriptions')->group(function () {
    
    // Get user's subscriptions
    Route::get('/user/{user_id}', [VegboxSubscriptionApiController::class, 'getUserSubscriptions']);
    
    // Create subscription after WooCommerce order
    Route::post('/create', [VegboxSubscriptionApiController::class, 'createSubscription']);
    
    // Subscription management (using /action endpoint to avoid ModSecurity blocking)
    Route::post('/{id}/action', [VegboxSubscriptionApiController::class, 'handleSubscriptionAction']);
    Route::post('/{id}/update-address', [VegboxSubscriptionApiController::class, 'updateAddress']);
    
    // Get subscription details
    Route::get('/{id}', [VegboxSubscriptionApiController::class, 'getSubscription']);
    
    // Get payment history
    Route::get('/{id}/payments', [VegboxSubscriptionApiController::class, 'getPayments']);
});
```

### 2. API Controller (`app/Http/Controllers/Api/VegboxSubscriptionApiController.php`)

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\VegboxSubscription;
use App\Services\VegboxPaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class VegboxSubscriptionApiController extends Controller
{
    protected VegboxPaymentService $paymentService;

    public function __construct(VegboxPaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Get all subscriptions for a WordPress user
     */
    public function getUserSubscriptions(Request $request, $user_id)
    {
        try {
            // Map WP user to Laravel user
            $laravelUser = $this->mapWpUserToLaravel($user_id);
            
            if (!$laravelUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            $subscriptions = VegboxSubscription::with(['plan'])
                ->where('subscriber_id', $laravelUser->id)
                ->where('subscriber_type', 'App\\Models\\User')
                ->get()
                ->map(function ($sub) {
                    return [
                        'id' => $sub->id,
                        'name' => $sub->name,
                        'plan' => $sub->plan->name ?? 'N/A',
                        'status' => $sub->active ? 'active' : 'inactive',
                        'price' => $sub->price,
                        'next_billing_at' => $sub->next_billing_at?->format('Y-m-d'),
                        'next_delivery_date' => $sub->next_delivery_date?->format('Y-m-d'),
                        'delivery_day' => $sub->delivery_day,
                        'is_paused' => $sub->isPaused(),
                        'pause_until' => $sub->pause_until?->format('Y-m-d'),
                    ];
                });

            return response()->json([
                'success' => true,
                'subscriptions' => $subscriptions
            ]);

        } catch (\Exception $e) {
            Log::error('API: Get user subscriptions failed', [
                'user_id' => $user_id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve subscriptions'
            ], 500);
        }
    }

    /**
     * Create subscription after WooCommerce order completes
     */
    public function createSubscription(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'wp_user_id' => 'required|integer',
            'wc_order_id' => 'required|integer',
            'plan_id' => 'required|integer',
            'delivery_day' => 'required|string',
            'delivery_address' => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Map WP user to Laravel user (create if doesn't exist)
            $laravelUser = $this->getOrCreateLaravelUser($request->wp_user_id);

            $subscription = VegboxSubscription::create([
                'subscriber_id' => $laravelUser->id,
                'subscriber_type' => 'App\\Models\\User',
                'plan_id' => $request->plan_id,
                'name' => ['en' => 'Vegbox Subscription'],
                'price' => $request->price ?? 0,
                'starts_at' => now(),
                'next_billing_at' => now()->addMonth(),
                'next_delivery_date' => $this->calculateNextDelivery($request->delivery_day),
                'delivery_day' => $request->delivery_day,
                'woo_subscription_id' => $request->wc_order_id,
                'imported_from_woo' => false,
            ]);

            // Store delivery address
            // TODO: Save to delivery_addresses table

            Log::info('API: Subscription created', [
                'subscription_id' => $subscription->id,
                'wp_user_id' => $request->wp_user_id,
                'wc_order_id' => $request->wc_order_id
            ]);

            return response()->json([
                'success' => true,
                'subscription' => [
                    'id' => $subscription->id,
                    'status' => 'active'
                ]
            ], 201);

        } catch (\Exception $e) {
            Log::error('API: Create subscription failed', [
                'wp_user_id' => $request->wp_user_id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create subscription'
            ], 500);
        }
    }

    /**
     * Handle subscription actions (pause/resume/cancel)
     * Using single endpoint to avoid ModSecurity blocking /resume and /cancel
     */
    public function handleSubscriptionAction(Request $request, $id)
    {
        $action = $request->input('action');
        
        if (!in_array($action, ['pause', 'resume', 'cancel'])) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid action'
            ], 400);
        }
        
        switch ($action) {
            case 'pause':
                return $this->pauseSubscription($request, $id);
            case 'resume':
                return $this->resumeSubscription($id);
            case 'cancel':
                return $this->cancelSubscription($id);
        }
    }

    /**
     * Pause subscription (private method)
     */
    private function pauseSubscription(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'pause_until' => 'required|date|after:today'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $subscription = VegboxSubscription::findOrFail($id);
            $subscription->pauseUntil(\Carbon\Carbon::parse($request->pause_until));

            return response()->json([
                'success' => true,
                'message' => 'Subscription paused',
                'pause_until' => $subscription->pause_until->format('Y-m-d')
            ]);

        } catch (\Exception $e) {
            Log::error('API: Pause subscription failed', [
                'subscription_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to pause subscription'
            ], 500);
        }
    }

    /**
     * Resume subscription (private method)
     */
    private function resumeSubscription($id)
    {
        try {
            $subscription = VegboxSubscription::findOrFail($id);
            $subscription->resume();

            return response()->json([
                'success' => true,
                'message' => 'Subscription resumed'
            ]);

        } catch (\Exception $e) {
            Log::error('API: Resume subscription failed', [
                'subscription_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to resume subscription'
            ], 500);
        }
    }

    /**
     * Cancel subscription (private method)
     */
    private function cancelSubscription($id)
    {
        try {
            $subscription = VegboxSubscription::findOrFail($id);
            $subscription->update([
                'canceled_at' => now(),
                'ends_at' => now()->addMonth() // Grace period
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Subscription will be cancelled at the end of the current billing period'
            ]);

        } catch (\Exception $e) {
            Log::error('API: Cancel subscription failed', [
                'subscription_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel subscription'
            ], 500);
        }
    }

    /**
     * Get single subscription details
     */
    public function getSubscription($id)
    {
        try {
            $subscription = VegboxSubscription::with(['plan'])->findOrFail($id);

            return response()->json([
                'success' => true,
                'subscription' => [
                    'id' => $subscription->id,
                    'name' => $subscription->name,
                    'plan' => $subscription->plan->name ?? 'N/A',
                    'status' => $subscription->active ? 'active' : 'inactive',
                    'price' => $subscription->price,
                    'next_billing_at' => $subscription->next_billing_at?->format('Y-m-d'),
                    'next_delivery_date' => $subscription->next_delivery_date?->format('Y-m-d'),
                    'delivery_day' => $subscription->delivery_day,
                    'is_paused' => $subscription->isPaused(),
                    'pause_until' => $subscription->pause_until?->format('Y-m-d'),
                    'started_at' => $subscription->starts_at?->format('Y-m-d'),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Subscription not found'
            ], 404);
        }
    }

    /**
     * Map WordPress user ID to Laravel user
     */
    protected function mapWpUserToLaravel($wpUserId)
    {
        // Get WP user email from WordPress database
        $wpUser = \DB::connection('wordpress')
            ->table('users')
            ->where('ID', $wpUserId)
            ->first();

        if (!$wpUser) {
            return null;
        }

        // Find or create Laravel user by email
        return \App\Models\User::firstOrCreate(
            ['email' => $wpUser->user_email],
            [
                'name' => $wpUser->display_name,
                'wp_user_id' => $wpUserId
            ]
        );
    }

    /**
     * Get or create Laravel user
     */
    protected function getOrCreateLaravelUser($wpUserId)
    {
        return $this->mapWpUserToLaravel($wpUserId);
    }

    /**
     * Calculate next delivery date based on day of week
     */
    protected function calculateNextDelivery($dayOfWeek)
    {
        $daysMap = [
            'monday' => 1,
            'tuesday' => 2,
            'wednesday' => 3,
            'thursday' => 4,
            'friday' => 5,
            'saturday' => 6,
            'sunday' => 7
        ];

        $targetDay = $daysMap[strtolower($dayOfWeek)] ?? 1;
        $today = now();
        $daysUntilTarget = ($targetDay - $today->dayOfWeek + 7) % 7;
        
        if ($daysUntilTarget === 0) {
            $daysUntilTarget = 7; // Next week if today is the delivery day
        }

        return $today->addDays($daysUntilTarget);
    }
}
```

### 3. API Authentication Middleware (`app/Http/Middleware/VerifyWooCommerceApiToken.php`)

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class VerifyWooCommerceApiToken
{
    public function handle(Request $request, Closure $next)
    {
        $apiKey = $request->header('X-MWF-API-Key');

        if ($apiKey !== config('services.mwf_api.key')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        return $next($request);
    }
}
```

### 4. Register Middleware (`app/Http/Kernel.php`)

```php
protected $middlewareAliases = [
    // ... existing middleware
    'verify.wc.api.token' => \App\Http\Middleware\VerifyWooCommerceApiToken::class,
];
```

### 5. Add API Key to `.env`

```env
# MWF Custom Subscriptions API
MWF_SUBSCRIPTIONS_API_KEY=Ffsh8yhsuZEGySvLrP0DihCDDwhPwk4h  # Use existing MWF_API_KEY
```

---

## 📦 Phase 2: WordPress Plugin Structure

### Location: `/var/www/vhosts/middleworldfarms.org/httpdocs/wp-content/plugins/mwf-subscriptions/`

### Plugin File Structure

```
mwf-subscriptions/
├── mwf-subscriptions.php          # Main plugin file
├── includes/
│   ├── class-mwf-api-client.php   # Laravel API client
│   ├── class-mwf-my-account.php   # My Account integration
│   └── class-mwf-checkout.php     # Checkout integration
├── templates/
│   ├── my-account-subscriptions.php    # Subscriptions list
│   ├── my-account-subscription-view.php # Single subscription view
│   └── checkout-subscription-fields.php # Delivery day selector
└── assets/
    ├── css/
    │   └── mwf-subscriptions.css
    └── js/
        └── mwf-subscriptions.js
```

### Main Plugin File (`mwf-subscriptions.php`)

```php
<?php
/**
 * Plugin Name: MWF Custom Subscriptions
 * Plugin URI: https://middleworldfarms.org
 * Description: Custom subscription management powered by Laravel backend. Replaces WooCommerce Subscriptions addon.
 * Version: 1.0.0
 * Author: Middle World Farms
 * Author URI: https://middleworldfarms.org
 * Requires at least: 6.0
 * Requires PHP: 8.0
 * WC requires at least: 8.0
 * WC tested up to: 9.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Define constants
define('MWF_SUBS_VERSION', '1.0.0');
define('MWF_SUBS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('MWF_SUBS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('MWF_SUBS_API_URL', 'https://admin.middleworldfarms.org:8444/api/subscriptions');
define('MWF_SUBS_API_KEY', 'Ffsh8yhsuZEGySvLrP0DihCDDwhPwk4h');

// Load dependencies
require_once MWF_SUBS_PLUGIN_DIR . 'includes/class-mwf-api-client.php';
require_once MWF_SUBS_PLUGIN_DIR . 'includes/class-mwf-my-account.php';
require_once MWF_SUBS_PLUGIN_DIR . 'includes/class-mwf-checkout.php';

/**
 * Main plugin class
 */
class MWF_Subscriptions {
    
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
        // Initialize components
        add_action('plugins_loaded', [$this, 'init'], 10);
        
        // Enqueue assets
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
    }
    
    public function init() {
        // Check if WooCommerce is active
        if (!class_exists('WooCommerce')) {
            add_action('admin_notices', [$this, 'woocommerce_missing_notice']);
            return;
        }
        
        // Initialize components
        MWF_My_Account::instance();
        MWF_Checkout::instance();
    }
    
    public function enqueue_assets() {
        if (is_account_page() || is_checkout()) {
            wp_enqueue_style(
                'mwf-subscriptions',
                MWF_SUBS_PLUGIN_URL . 'assets/css/mwf-subscriptions.css',
                [],
                MWF_SUBS_VERSION
            );
            
            wp_enqueue_script(
                'mwf-subscriptions',
                MWF_SUBS_PLUGIN_URL . 'assets/js/mwf-subscriptions.js',
                ['jquery'],
                MWF_SUBS_VERSION,
                true
            );
            
            wp_localize_script('mwf-subscriptions', 'mwfSubs', [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('mwf_subscriptions')
            ]);
        }
    }
    
    public function woocommerce_missing_notice() {
        ?>
        <div class="notice notice-error">
            <p><?php _e('MWF Custom Subscriptions requires WooCommerce to be installed and active.', 'mwf-subscriptions'); ?></p>
        </div>
        <?php
    }
}

// Initialize plugin
function MWF_Subscriptions() {
    return MWF_Subscriptions::instance();
}

MWF_Subscriptions();
```

### API Client (`includes/class-mwf-api-client.php`)

```php
<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Laravel API client
 */
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
        $url = MWF_SUBS_API_URL . $endpoint;
        
        $args = [
            'method' => $method,
            'headers' => [
                'X-MWF-API-Key' => MWF_SUBS_API_KEY,
                'Content-Type' => 'application/json',
            ],
            'timeout' => 30,
        ];
        
        if (!empty($data) && in_array($method, ['POST', 'PUT', 'PATCH'])) {
            $args['body'] = json_encode($data);
        }
        
        $response = wp_remote_request($url, $args);
        
        if (is_wp_error($response)) {
            error_log('MWF API Error: ' . $response->get_error_message());
            return [
                'success' => false,
                'message' => $response->get_error_message()
            ];
        }
        
        $body = wp_remote_retrieve_body($response);
        $decoded = json_decode($body, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log('MWF API JSON Error: ' . json_last_error_msg());
            return [
                'success' => false,
                'message' => 'Invalid API response'
            ];
        }
        
        return $decoded;
    }
    
    /**
     * Get user subscriptions
     */
    public function get_user_subscriptions($user_id) {
        return $this->request("/user/{$user_id}", 'GET');
    }
    
    /**
     * Create subscription
     */
    public function create_subscription($data) {
        return $this->request('/create', 'POST', $data);
    }
    
    /**
     * Pause subscription
     */
    public function pause_subscription($subscription_id, $pause_until) {
        return $this->request("/{$subscription_id}/pause", 'POST', [
            'pause_until' => $pause_until
        ]);
    }
    
    /**
     * Resume subscription
     */
    public function resume_subscription($subscription_id) {
        return $this->request("/{$subscription_id}/resume", 'POST');
    }
    
    /**
     * Cancel subscription
     */
    public function cancel_subscription($subscription_id) {
        return $this->request("/{$subscription_id}/cancel", 'POST');
    }
    
    /**
     * Get subscription details
     */
    public function get_subscription($subscription_id) {
        return $this->request("/{$subscription_id}", 'GET');
    }
}
```

### My Account Integration (`includes/class-mwf-my-account.php`)

```php
<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * WooCommerce My Account integration
 */
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
        
        // Register endpoint
        add_action('init', [$this, 'add_endpoints']);
        
        // Display subscriptions content
        add_action('woocommerce_account_subscriptions_endpoint', [$this, 'subscriptions_content']);
        add_action('woocommerce_account_view-subscription_endpoint', [$this, 'view_subscription_content']);
        
        // Handle subscription actions (AJAX)
        add_action('wp_ajax_mwf_pause_subscription', [$this, 'ajax_pause_subscription']);
        add_action('wp_ajax_mwf_resume_subscription', [$this, 'ajax_resume_subscription']);
        add_action('wp_ajax_mwf_cancel_subscription', [$this, 'ajax_cancel_subscription']);
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
                $new_items['subscriptions'] = __('Subscriptions', 'mwf-subscriptions');
            }
        }
        return $new_items;
    }
    
    /**
     * Register endpoints
     */
    public function add_endpoints() {
        add_rewrite_endpoint('subscriptions', EP_PAGES);
        add_rewrite_endpoint('view-subscription', EP_PAGES);
        flush_rewrite_rules(); // Only on activation
    }
    
    /**
     * Display subscriptions list
     */
    public function subscriptions_content() {
        $user_id = get_current_user_id();
        $api = MWF_API_Client::instance();
        $response = $api->get_user_subscriptions($user_id);
        
        if (!$response['success']) {
            wc_add_notice($response['message'], 'error');
            return;
        }
        
        $subscriptions = $response['subscriptions'] ?? [];
        
        include MWF_SUBS_PLUGIN_DIR . 'templates/my-account-subscriptions.php';
    }
    
    /**
     * Display single subscription view
     */
    public function view_subscription_content($subscription_id) {
        $api = MWF_API_Client::instance();
        $response = $api->get_subscription($subscription_id);
        
        if (!$response['success']) {
            wc_add_notice($response['message'], 'error');
            return;
        }
        
        $subscription = $response['subscription'];
        
        include MWF_SUBS_PLUGIN_DIR . 'templates/my-account-subscription-view.php';
    }
    
    /**
     * AJAX: Pause subscription
     */
    public function ajax_pause_subscription() {
        check_ajax_referer('mwf_subscriptions', 'nonce');
        
        $subscription_id = intval($_POST['subscription_id']);
        $pause_until = sanitize_text_field($_POST['pause_until']);
        
        $api = MWF_API_Client::instance();
        $response = $api->pause_subscription($subscription_id, $pause_until);
        
        wp_send_json($response);
    }
    
    /**
     * AJAX: Resume subscription
     */
    public function ajax_resume_subscription() {
        check_ajax_referer('mwf_subscriptions', 'nonce');
        
        $subscription_id = intval($_POST['subscription_id']);
        
        $api = MWF_API_Client::instance();
        $response = $api->resume_subscription($subscription_id);
        
        wp_send_json($response);
    }
    
    /**
     * AJAX: Cancel subscription
     */
    public function ajax_cancel_subscription() {
        check_ajax_referer('mwf_subscriptions', 'nonce');
        
        $subscription_id = intval($_POST['subscription_id']);
        
        if (!isset($_POST['confirm']) || $_POST['confirm'] !== 'yes') {
            wp_send_json([
                'success' => false,
                'message' => 'Please confirm cancellation'
            ]);
        }
        
        $api = MWF_API_Client::instance();
        $response = $api->cancel_subscription($subscription_id);
        
        wp_send_json($response);
    }
}
```

### Checkout Integration (`includes/class-mwf-checkout.php`)

```php
<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * WooCommerce Checkout integration
 */
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
        // Add delivery day selector to checkout
        add_action('woocommerce_after_order_notes', [$this, 'add_delivery_day_field']);
        
        // Validate delivery day
        add_action('woocommerce_checkout_process', [$this, 'validate_delivery_day']);
        
        // Save delivery day to order meta
        add_action('woocommerce_checkout_update_order_meta', [$this, 'save_delivery_day']);
        
        // Create subscription after order completion
        add_action('woocommerce_order_status_completed', [$this, 'create_subscription'], 10, 1);
    }
    
    /**
     * Add delivery day selector to checkout
     */
    public function add_delivery_day_field($checkout) {
        // Only show for vegbox products
        if (!$this->cart_contains_subscription_product()) {
            return;
        }
        
        echo '<div id="mwf_delivery_options">';
        echo '<h3>' . __('Delivery Options', 'mwf-subscriptions') . '</h3>';
        
        woocommerce_form_field('mwf_delivery_day', [
            'type' => 'select',
            'class' => ['form-row-wide'],
            'label' => __('Preferred Delivery Day', 'mwf-subscriptions'),
            'required' => true,
            'options' => [
                '' => __('Select a day', 'mwf-subscriptions'),
                'monday' => __('Monday', 'mwf-subscriptions'),
                'tuesday' => __('Tuesday', 'mwf-subscriptions'),
                'wednesday' => __('Wednesday', 'mwf-subscriptions'),
                'thursday' => __('Thursday', 'mwf-subscriptions'),
                'friday' => __('Friday', 'mwf-subscriptions'),
            ]
        ], $checkout->get_value('mwf_delivery_day'));
        
        echo '</div>';
    }
    
    /**
     * Validate delivery day is selected
     */
    public function validate_delivery_day() {
        if (!$this->cart_contains_subscription_product()) {
            return;
        }
        
        if (empty($_POST['mwf_delivery_day'])) {
            wc_add_notice(__('Please select a delivery day.', 'mwf-subscriptions'), 'error');
        }
    }
    
    /**
     * Save delivery day to order meta
     */
    public function save_delivery_day($order_id) {
        if (!empty($_POST['mwf_delivery_day'])) {
            update_post_meta($order_id, '_mwf_delivery_day', sanitize_text_field($_POST['mwf_delivery_day']));
        }
    }
    
    /**
     * Create subscription via API after order completes
     */
    public function create_subscription($order_id) {
        $order = wc_get_order($order_id);
        
        // Check if order contains subscription products
        if (!$this->order_contains_subscription_product($order)) {
            return;
        }
        
        // Get delivery day
        $delivery_day = get_post_meta($order_id, '_mwf_delivery_day', true);
        if (empty($delivery_day)) {
            error_log("MWF Subscriptions: No delivery day set for order {$order_id}");
            return;
        }
        
        // Get plan ID from product meta
        $plan_id = $this->get_plan_id_from_order($order);
        if (!$plan_id) {
            error_log("MWF Subscriptions: No plan ID found for order {$order_id}");
            return;
        }
        
        // Create subscription via API
        $api = MWF_API_Client::instance();
        $response = $api->create_subscription([
            'wp_user_id' => $order->get_user_id(),
            'wc_order_id' => $order_id,
            'plan_id' => $plan_id,
            'delivery_day' => $delivery_day,
            'delivery_address' => [
                'address_1' => $order->get_shipping_address_1(),
                'address_2' => $order->get_shipping_address_2(),
                'city' => $order->get_shipping_city(),
                'postcode' => $order->get_shipping_postcode(),
                'country' => $order->get_shipping_country(),
            ],
            'price' => $order->get_total(),
        ]);
        
        if ($response['success']) {
            // Store subscription ID in order meta
            update_post_meta($order_id, '_mwf_subscription_id', $response['subscription']['id']);
            $order->add_order_note(__('Vegbox subscription created successfully.', 'mwf-subscriptions'));
        } else {
            error_log("MWF Subscriptions: Failed to create subscription for order {$order_id}: " . $response['message']);
            $order->add_order_note(__('Failed to create vegbox subscription. Please create manually.', 'mwf-subscriptions'));
        }
    }
    
    /**
     * Check if cart contains subscription product
     */
    private function cart_contains_subscription_product() {
        if (!WC()->cart) {
            return false;
        }
        
        foreach (WC()->cart->get_cart() as $cart_item) {
            $product_id = $cart_item['product_id'];
            if (get_post_meta($product_id, '_is_vegbox_subscription', true) === 'yes') {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Check if order contains subscription product
     */
    private function order_contains_subscription_product($order) {
        foreach ($order->get_items() as $item) {
            $product_id = $item->get_product_id();
            if (get_post_meta($product_id, '_is_vegbox_subscription', true) === 'yes') {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Get plan ID from order
     */
    private function get_plan_id_from_order($order) {
        foreach ($order->get_items() as $item) {
            $product_id = $item->get_product_id();
            $plan_id = get_post_meta($product_id, '_vegbox_plan_id', true);
            if ($plan_id) {
                return intval($plan_id);
            }
        }
        
        return null;
    }
}
```

### Template: Subscriptions List (`templates/my-account-subscriptions.php`)

```php
<?php
/**
 * My Account > Subscriptions
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<h2><?php _e('My Subscriptions', 'mwf-subscriptions'); ?></h2>

<?php if (empty($subscriptions)): ?>
    <p><?php _e('You have no active subscriptions.', 'mwf-subscriptions'); ?></p>
<?php else: ?>
    <table class="woocommerce-orders-table woocommerce-MyAccount-subscriptions shop_table shop_table_responsive my_account_orders account-subscriptions-table">
        <thead>
            <tr>
                <th class="subscription-id"><?php _e('Subscription', 'mwf-subscriptions'); ?></th>
                <th class="subscription-status"><?php _e('Status', 'mwf-subscriptions'); ?></th>
                <th class="subscription-next-payment"><?php _e('Next Payment', 'mwf-subscriptions'); ?></th>
                <th class="subscription-total"><?php _e('Total', 'mwf-subscriptions'); ?></th>
                <th class="subscription-actions"><?php _e('Actions', 'mwf-subscriptions'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($subscriptions as $subscription): ?>
                <tr class="subscription">
                    <td class="subscription-id" data-title="<?php _e('Subscription', 'mwf-subscriptions'); ?>">
                        #<?php echo esc_html($subscription['id']); ?> - <?php echo esc_html($subscription['name']); ?>
                    </td>
                    <td class="subscription-status" data-title="<?php _e('Status', 'mwf-subscriptions'); ?>">
                        <?php if ($subscription['is_paused']): ?>
                            <span class="subscription-status-paused"><?php _e('Paused', 'mwf-subscriptions'); ?></span>
                            <br><small><?php printf(__('Until %s', 'mwf-subscriptions'), $subscription['pause_until']); ?></small>
                        <?php else: ?>
                            <span class="subscription-status-<?php echo esc_attr($subscription['status']); ?>">
                                <?php echo esc_html(ucfirst($subscription['status'])); ?>
                            </span>
                        <?php endif; ?>
                    </td>
                    <td class="subscription-next-payment" data-title="<?php _e('Next Payment', 'mwf-subscriptions'); ?>">
                        <?php echo $subscription['next_billing_at'] ? esc_html(date_i18n(wc_date_format(), strtotime($subscription['next_billing_at']))) : '-'; ?>
                    </td>
                    <td class="subscription-total" data-title="<?php _e('Total', 'mwf-subscriptions'); ?>">
                        <?php echo wc_price($subscription['price']); ?>
                    </td>
                    <td class="subscription-actions" data-title="<?php _e('Actions', 'mwf-subscriptions'); ?>">
                        <a href="<?php echo esc_url(wc_get_endpoint_url('view-subscription', $subscription['id'], wc_get_page_permalink('myaccount'))); ?>" class="button view">
                            <?php _e('View', 'mwf-subscriptions'); ?>
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
```

### Template: Subscription View (`templates/my-account-subscription-view.php`)

```php
<?php
/**
 * My Account > View Subscription
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<h2><?php printf(__('Subscription #%d', 'mwf-subscriptions'), $subscription['id']); ?></h2>

<table class="woocommerce-table shop_table subscription_details">
    <tbody>
        <tr>
            <th><?php _e('Status:', 'mwf-subscriptions'); ?></th>
            <td>
                <?php if ($subscription['is_paused']): ?>
                    <span class="subscription-status-paused"><?php _e('Paused', 'mwf-subscriptions'); ?></span>
                    <br><?php printf(__('Paused until %s', 'mwf-subscriptions'), date_i18n(wc_date_format(), strtotime($subscription['pause_until']))); ?>
                <?php else: ?>
                    <?php echo esc_html(ucfirst($subscription['status'])); ?>
                <?php endif; ?>
            </td>
        </tr>
        <tr>
            <th><?php _e('Plan:', 'mwf-subscriptions'); ?></th>
            <td><?php echo esc_html($subscription['plan']); ?></td>
        </tr>
        <tr>
            <th><?php _e('Price:', 'mwf-subscriptions'); ?></th>
            <td><?php echo wc_price($subscription['price']); ?> / month</td>
        </tr>
        <tr>
            <th><?php _e('Next Payment:', 'mwf-subscriptions'); ?></th>
            <td><?php echo $subscription['next_billing_at'] ? date_i18n(wc_date_format(), strtotime($subscription['next_billing_at'])) : '-'; ?></td>
        </tr>
        <tr>
            <th><?php _e('Next Delivery:', 'mwf-subscriptions'); ?></th>
            <td><?php echo $subscription['next_delivery_date'] ? date_i18n(wc_date_format(), strtotime($subscription['next_delivery_date'])) : '-'; ?></td>
        </tr>
        <tr>
            <th><?php _e('Delivery Day:', 'mwf-subscriptions'); ?></th>
            <td><?php echo esc_html(ucfirst($subscription['delivery_day'])); ?></td>
        </tr>
        <tr>
            <th><?php _e('Started:', 'mwf-subscriptions'); ?></th>
            <td><?php echo date_i18n(wc_date_format(), strtotime($subscription['started_at'])); ?></td>
        </tr>
    </tbody>
</table>

<div class="subscription-actions">
    <?php if ($subscription['is_paused']): ?>
        <button class="button" id="mwf-resume-subscription" data-subscription-id="<?php echo esc_attr($subscription['id']); ?>">
            <?php _e('Resume Subscription', 'mwf-subscriptions'); ?>
        </button>
    <?php else: ?>
        <button class="button" id="mwf-pause-subscription" data-subscription-id="<?php echo esc_attr($subscription['id']); ?>">
            <?php _e('Pause Subscription', 'mwf-subscriptions'); ?>
        </button>
    <?php endif; ?>
    
    <button class="button alt" id="mwf-cancel-subscription" data-subscription-id="<?php echo esc_attr($subscription['id']); ?>">
        <?php _e('Cancel Subscription', 'mwf-subscriptions'); ?>
    </button>
</div>

<!-- Pause form (hidden by default) -->
<div id="mwf-pause-form" style="display:none; margin-top:20px;">
    <h3><?php _e('Pause Subscription', 'mwf-subscriptions'); ?></h3>
    <p><?php _e('Select the date to resume your subscription:', 'mwf-subscriptions'); ?></p>
    <input type="date" id="mwf-pause-until" min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" />
    <button class="button" id="mwf-confirm-pause"><?php _e('Confirm Pause', 'mwf-subscriptions'); ?></button>
    <button class="button" id="mwf-cancel-pause-form"><?php _e('Cancel', 'mwf-subscriptions'); ?></button>
</div>
```

### JavaScript (`assets/js/mwf-subscriptions.js`)

```javascript
jQuery(document).ready(function($) {
    
    // Pause subscription - show form
    $('#mwf-pause-subscription').on('click', function() {
        $('#mwf-pause-form').slideDown();
    });
    
    // Cancel pause form
    $('#mwf-cancel-pause-form').on('click', function() {
        $('#mwf-pause-form').slideUp();
    });
    
    // Confirm pause
    $('#mwf-confirm-pause').on('click', function() {
        var subscriptionId = $('#mwf-pause-subscription').data('subscription-id');
        var pauseUntil = $('#mwf-pause-until').val();
        
        if (!pauseUntil) {
            alert('Please select a date');
            return;
        }
        
        $.ajax({
            url: mwfSubs.ajax_url,
            type: 'POST',
            data: {
                action: 'mwf_pause_subscription',
                nonce: mwfSubs.nonce,
                subscription_id: subscriptionId,
                pause_until: pauseUntil
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(response.message || 'Failed to pause subscription');
                }
            }
        });
    });
    
    // Resume subscription
    $('#mwf-resume-subscription').on('click', function() {
        var subscriptionId = $(this).data('subscription-id');
        
        if (!confirm('Resume your subscription?')) {
            return;
        }
        
        $.ajax({
            url: mwfSubs.ajax_url,
            type: 'POST',
            data: {
                action: 'mwf_resume_subscription',
                nonce: mwfSubs.nonce,
                subscription_id: subscriptionId
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(response.message || 'Failed to resume subscription');
                }
            }
        });
    });
    
    // Cancel subscription
    $('#mwf-cancel-subscription').on('click', function() {
        var subscriptionId = $(this).data('subscription-id');
        
        if (!confirm('Are you sure you want to cancel your subscription? This will take effect at the end of your current billing period.')) {
            return;
        }
        
        $.ajax({
            url: mwfSubs.ajax_url,
            type: 'POST',
            data: {
                action: 'mwf_cancel_subscription',
                nonce: mwfSubs.nonce,
                subscription_id: subscriptionId,
                confirm: 'yes'
            },
            success: function(response) {
                if (response.success) {
                    alert(response.message);
                    location.reload();
                } else {
                    alert(response.message || 'Failed to cancel subscription');
                }
            }
        });
    });
});
```

### CSS (`assets/css/mwf-subscriptions.css`)

```css
/* Subscription Status Badges */
.subscription-status-active {
    color: #7ad03a;
    font-weight: bold;
}

.subscription-status-paused {
    color: #ffba00;
    font-weight: bold;
}

.subscription-status-cancelled {
    color: #a00;
    font-weight: bold;
}

/* Subscription Actions */
.subscription-actions {
    margin-top: 20px;
}

.subscription-actions .button {
    margin-right: 10px;
}

/* Pause Form */
#mwf-pause-form {
    background: #f8f8f8;
    padding: 20px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

#mwf-pause-form input[type="date"] {
    padding: 10px;
    width: 100%;
    max-width: 300px;
    margin-bottom: 10px;
}

/* Delivery Options (Checkout) */
#mwf_delivery_options {
    margin-top: 20px;
    padding: 20px;
    background: #f8f8f8;
    border: 1px solid #ddd;
}

#mwf_delivery_options h3 {
    margin-top: 0;
}
```

---

## 📝 Phase 3: Product Setup

### Mark Vegbox Products as Subscriptions

In WordPress admin, for each vegbox product:

1. Go to **Products** > Edit vegbox product
2. Add custom fields (use plugin like "Advanced Custom Fields" or add manually):
   ```
   _is_vegbox_subscription = yes
   _vegbox_plan_id = 1  (matches VegboxPlan ID in Laravel)
   ```
3. Save product

---

## 🧪 Phase 4: Testing Checklist

### Laravel API Testing

```bash
# Test API endpoint (from terminal)
curl -X GET "https://admin.middleworldfarms.org:8444/api/subscriptions/user/1" \
  -H "X-MWF-API-Key: Ffsh8yhsuZEGySvLrP0DihCDDwhPwk4h"
```

### WordPress Plugin Testing

1. **Install Plugin**
   - Upload to `/wp-content/plugins/mwf-subscriptions/`
   - Activate in WordPress admin

2. **Test Checkout**
   - Add vegbox product to cart
   - Complete checkout
   - Verify delivery day field appears
   - Complete order
   - Check if subscription created in Laravel

3. **Test My Account**
   - Go to My Account > Subscriptions
   - Verify subscriptions list appears
   - Click "View" on a subscription
   - Test pause/resume/cancel buttons

4. **Test API Communication**
   - Check WordPress error logs: `/wp-content/debug.log`
   - Check Laravel logs: `/opt/sites/admin.middleworldfarms.org/storage/logs/laravel.log`

---

## 🚀 Deployment Steps

### Step 1: Deploy Laravel API (Current Workspace)
1. Add API routes to `routes/api.php`
2. Create `VegboxSubscriptionApiController`
3. Create `VerifyWooCommerceApiToken` middleware
4. Register middleware in `Kernel.php`
5. Test API endpoints

### Step 2: Deploy WordPress Plugin (WooCommerce Workspace)
1. Create plugin directory structure
2. Add all plugin files
3. Activate plugin in WordPress admin
4. Configure products as subscriptions

### Step 3: Test Integration
1. Place test order
2. Verify subscription creation
3. Test My Account features
4. Monitor logs for errors

---

## 📞 Support & Troubleshooting

### Common Issues

**API returns 401 Unauthorized:**
- Check API key matches in both systems
- Verify middleware is registered

**Subscription not created after order:**
- Check product has `_is_vegbox_subscription` meta
- Check Laravel logs for errors
- Verify order status is "completed"

**My Account tab not appearing:**
- Flush rewrite rules: Settings > Permalinks > Save
- Check WooCommerce is active

### Log Locations

**WordPress:**
```
/var/www/vhosts/middleworldfarms.org/httpdocs/wp-content/debug.log
```

**Laravel:**
```
/opt/sites/admin.middleworldfarms.org/storage/logs/laravel.log
```

---

## ✅ Success Criteria

- [ ] Laravel API endpoints responding
- [ ] WordPress plugin activated
- [ ] Subscriptions tab visible in My Account
- [ ] Checkout creates subscription via API
- [ ] Customer can pause/resume/cancel subscription
- [ ] Logs show no errors
- [ ] WooCommerce Subscriptions addon can be deactivated
- [ ] **£199/year saved!** 🎉

---

## 📅 Next Steps After Basic Implementation

1. **Email Notifications**
   - Renewal reminders
   - Payment success/failure
   - Subscription status changes

2. **Admin Features**
   - View all subscriptions in admin panel
   - Manual renewal processing
   - Customer management

3. **Advanced Features**
   - Multiple delivery addresses
   - Skip deliveries
   - Product swapping
   - Subscription pausing calendar

---

**Ready to build? Start with Phase 1 (Laravel API) in the current workspace, then move to Phase 2 (WordPress Plugin) in the WooCommerce workspace!** 🚀
