# Middleworld Farms Subscription System Architecture

**Date:** November 30, 2025  
**Goal:** Replace WooCommerce Subscriptions addon (£199/year) with custom system where Laravel handles renewals and WordPress is display-only

---

## System Overview

```
┌─────────────────────────────────────────────────────────────────┐
│                    CUSTOMER JOURNEY                              │
└─────────────────────────────────────────────────────────────────┘

1. Browse Products (WordPress/WooCommerce)
   └─> Variable products with size variations
   
2. Add to Cart → Checkout (WordPress/WooCommerce)
   └─> Select delivery day
   └─> Enter payment details
   └─> Complete initial order
   
3. Order Complete Hook (WordPress)
   └─> Send subscription data to Laravel API
   
4. Laravel Receives Subscription
   └─> Create subscription record
   └─> Schedule first renewal
   └─> Return subscription ID to WordPress
   
5. Customer Views "My Account → Subscriptions" (WordPress)
   └─> WordPress calls Laravel API
   └─> Displays subscription status (read-only)
   └─> "Manage" button → redirects to Laravel admin
   
6. ALL Renewals Handled by Laravel
   └─> Laravel processes payments
   └─> Laravel creates WooCommerce orders via API (optional)
   └─> Laravel manages subscription lifecycle
```

---

## Division of Responsibilities

### WordPress/WooCommerce (Order Capture Layer)

**DOES:**
- ✅ Display products with variations
- ✅ Handle initial checkout and payment
- ✅ Capture delivery day selection
- ✅ Send new subscription to Laravel API
- ✅ Display subscription status from Laravel (read-only)
- ✅ Show subscription list in My Account

**DOES NOT:**
- ❌ Process renewals
- ❌ Schedule future payments
- ❌ Manage subscription state
- ❌ Handle cancellations/pauses
- ❌ Calculate next billing date

### Laravel Admin (Subscription Management Engine)

**DOES:**
- ✅ Receive new subscriptions from WordPress
- ✅ Store subscription data (single source of truth)
- ✅ Schedule renewal jobs
- ✅ Process renewal payments
- ✅ Create renewal orders (in Laravel or back to WooCommerce)
- ✅ Handle cancellations/pauses/resumes
- ✅ Calculate next billing dates
- ✅ Send email notifications
- ✅ Provide subscription status to WordPress

**DOES NOT:**
- ❌ Handle initial checkout (WordPress does this)
- ❌ Display product catalog (WordPress does this)

---

## Data Model

### WordPress Product Meta (per variation)

```php
// Standard WooCommerce variable product
// Each variation has:
_regular_price: "25.00"
_stock_status: "instock"
_sku: "VEGBOX-LARGE-WEEKLY"

// Custom subscription meta:
_subscription_period: "week" | "month"
_subscription_interval: 1, 2, 3, etc.
_mwf_plan_id: 2  // Maps to Laravel subscription plan
```

### WordPress Subscription Storage (Minimal)

```php
// Post meta on order
_mwf_is_subscription: "yes"
_mwf_delivery_day: "monday"
_mwf_laravel_subscription_id: 456  // Reference to Laravel
_mwf_subscription_status: "active" // Cached from Laravel
```

### Laravel Subscription Model (Full Data)

```php
subscriptions table:
- id
- user_id (WordPress user ID)
- wordpress_order_id (initial order)
- product_id (WooCommerce product ID)
- variation_id (WooCommerce variation ID)
- status (active, paused, cancelled, expired)
- billing_period (week, month)
- billing_interval (1, 2, 3)
- billing_amount (25.00)
- delivery_day (monday, tuesday, etc.)
- next_billing_date
- last_billing_date
- payment_method_id
- created_at
- updated_at

subscription_orders table:
- id
- subscription_id
- wordpress_order_id (renewal order created in WC)
- amount
- status (pending, completed, failed)
- billing_date
- created_at
```

---

## API Specification

### WordPress → Laravel (New Subscription)

**Endpoint:** `POST /api/subscriptions`

**Request:**
```json
{
  "wordpress_user_id": 123,
  "wordpress_order_id": 5678,
  "product_id": 226082,
  "variation_id": 226085,
  "billing_period": "week",
  "billing_interval": 1,
  "billing_amount": 25.00,
  "delivery_day": "monday",
  "payment_method": "stripe",
  "payment_method_token": "pm_1234567890",
  "customer_email": "customer@example.com",
  "billing_address": {
    "first_name": "John",
    "last_name": "Doe",
    "address_1": "123 Main St",
    "city": "Anytown",
    "postcode": "AB12 3CD",
    "phone": "01234567890"
  }
}
```

**Response:**
```json
{
  "success": true,
  "subscription_id": 456,
  "status": "active",
  "next_billing_date": "2025-12-07",
  "message": "Subscription created successfully"
}
```

### WordPress → Laravel (Get User Subscriptions)

**Endpoint:** `GET /api/subscriptions/user/{wordpress_user_id}`

**Response:**
```json
{
  "success": true,
  "subscriptions": [
    {
      "id": 456,
      "product_name": "Large Family Vegetable Box",
      "variation_name": "Weekly Delivery",
      "status": "active",
      "billing_amount": 25.00,
      "billing_period": "week",
      "delivery_day": "monday",
      "next_billing_date": "2025-12-07",
      "created_at": "2025-11-01",
      "manage_url": "https://admin.middleworldfarms.org:8444/subscriptions/456"
    }
  ]
}
```

### WordPress → Laravel (Get Single Subscription)

**Endpoint:** `GET /api/subscriptions/{subscription_id}`

**Response:**
```json
{
  "success": true,
  "subscription": {
    "id": 456,
    "status": "active",
    "product_name": "Large Family Vegetable Box",
    "variation_name": "Weekly Delivery",
    "billing_amount": 25.00,
    "billing_period": "week",
    "billing_interval": 1,
    "delivery_day": "monday",
    "next_billing_date": "2025-12-07",
    "last_billing_date": "2025-11-30",
    "created_at": "2025-11-01",
    "renewal_orders": [
      {
        "id": 123,
        "date": "2025-11-23",
        "amount": 25.00,
        "status": "completed",
        "wordpress_order_id": 5789
      },
      {
        "id": 124,
        "date": "2025-11-30",
        "amount": 25.00,
        "status": "completed",
        "wordpress_order_id": 5834
      }
    ]
  }
}
```

### Laravel → WordPress (Create Renewal Order - Optional)

**Endpoint:** `POST /wp-json/mwf/v1/create-order`

**Headers:**
```
X-WC-API-Key: Ffsh8yhsuZEGySvLrP0DihCDDwhPwk4h
```

**Request:**
```json
{
  "customer_email": "customer@example.com",
  "customer_id": 123,
  "items": [
    {
      "product_id": 226082,
      "variation_id": 226085,
      "quantity": 1
    }
  ],
  "payment_method": "subscription_renewal",
  "payment_method_title": "Subscription Renewal",
  "status": "completed",
  "meta_data": [
    {
      "key": "_subscription_renewal",
      "value": "yes"
    },
    {
      "key": "_mwf_laravel_subscription_id",
      "value": "456"
    }
  ]
}
```

**Response:**
```json
{
  "success": true,
  "order_id": 5789,
  "order_number": "5789",
  "order_key": "wc_order_abc123"
}
```

---

## WordPress Implementation

### File Structure

```
wp-content/plugins/mwf-subscriptions/
├── mwf-subscriptions.php                 (main plugin file)
├── includes/
│   ├── class-mwf-checkout.php           (existing - handles initial order)
│   ├── class-mwf-api-client.php         (existing - Laravel API calls)
│   ├── class-mwf-my-account.php         (NEW - My Account page)
│   └── class-mwf-admin.php              (existing - admin interface)
└── templates/
    ├── my-account/
    │   ├── subscriptions.php            (NEW - subscription list)
    │   └── subscription-detail.php      (NEW - single subscription)
    └── emails/
        └── subscription-created.php     (optional - confirmation)
```

### WordPress Code Components

#### 1. My Account Integration (~150 lines)

**File:** `includes/class-mwf-my-account.php`

```php
<?php
class MWF_Subscriptions_My_Account {
    
    private $api_client;
    
    public function __construct() {
        $this->api_client = new MWF_API_Client();
        $this->init_hooks();
    }
    
    private function init_hooks() {
        // Add Subscriptions to My Account menu
        add_filter('woocommerce_account_menu_items', array($this, 'add_menu_item'));
        
        // Register endpoint
        add_action('init', array($this, 'add_endpoints'));
        
        // Display subscriptions page
        add_action('woocommerce_account_subscriptions_endpoint', array($this, 'subscriptions_content'));
        
        // Display single subscription page
        add_action('woocommerce_account_view-subscription_endpoint', array($this, 'view_subscription_content'));
    }
    
    public function add_menu_item($items) {
        // Add after Orders
        $new_items = array();
        foreach ($items as $key => $value) {
            $new_items[$key] = $value;
            if ($key === 'orders') {
                $new_items['subscriptions'] = __('Subscriptions', 'mwf-subscriptions');
            }
        }
        return $new_items;
    }
    
    public function add_endpoints() {
        add_rewrite_endpoint('subscriptions', EP_ROOT | EP_PAGES);
        add_rewrite_endpoint('view-subscription', EP_ROOT | EP_PAGES);
    }
    
    public function subscriptions_content() {
        $user_id = get_current_user_id();
        
        // Call Laravel API
        $response = $this->api_client->get_user_subscriptions($user_id);
        
        if (!$response || !isset($response['success']) || !$response['success']) {
            echo '<p>Unable to load subscriptions. Please try again later.</p>';
            return;
        }
        
        $subscriptions = $response['subscriptions'] ?? array();
        
        // Load template
        wc_get_template(
            'my-account/subscriptions.php',
            array('subscriptions' => $subscriptions),
            '',
            MWF_SUBS_PLUGIN_DIR . 'templates/'
        );
    }
    
    public function view_subscription_content($subscription_id) {
        $user_id = get_current_user_id();
        
        // Call Laravel API
        $response = $this->api_client->get_subscription($subscription_id);
        
        if (!$response || !isset($response['success']) || !$response['success']) {
            echo '<p>Subscription not found.</p>';
            return;
        }
        
        $subscription = $response['subscription'];
        
        // Verify ownership
        if ($subscription['user_id'] != $user_id) {
            echo '<p>Access denied.</p>';
            return;
        }
        
        // Load template
        wc_get_template(
            'my-account/subscription-detail.php',
            array('subscription' => $subscription),
            '',
            MWF_SUBS_PLUGIN_DIR . 'templates/'
        );
    }
}
```

#### 2. Template: Subscriptions List

**File:** `templates/my-account/subscriptions.php`

```php
<?php if (empty($subscriptions)): ?>
    <div class="woocommerce-Message woocommerce-Message--info">
        <p><?php esc_html_e('You have no active subscriptions.', 'mwf-subscriptions'); ?></p>
    </div>
<?php else: ?>
    <table class="shop_table shop_table_responsive my_account_subscriptions">
        <thead>
            <tr>
                <th><?php esc_html_e('Subscription', 'mwf-subscriptions'); ?></th>
                <th><?php esc_html_e('Status', 'mwf-subscriptions'); ?></th>
                <th><?php esc_html_e('Next Payment', 'mwf-subscriptions'); ?></th>
                <th><?php esc_html_e('Total', 'mwf-subscriptions'); ?></th>
                <th><?php esc_html_e('Actions', 'mwf-subscriptions'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($subscriptions as $subscription): ?>
                <tr>
                    <td data-title="<?php esc_attr_e('Subscription', 'mwf-subscriptions'); ?>">
                        <a href="<?php echo esc_url(wc_get_account_endpoint_url('view-subscription/' . $subscription['id'])); ?>">
                            #<?php echo esc_html($subscription['id']); ?> - <?php echo esc_html($subscription['product_name']); ?>
                        </a>
                        <?php if (!empty($subscription['variation_name'])): ?>
                            <br><small><?php echo esc_html($subscription['variation_name']); ?></small>
                        <?php endif; ?>
                    </td>
                    <td data-title="<?php esc_attr_e('Status', 'mwf-subscriptions'); ?>">
                        <span class="subscription-status status-<?php echo esc_attr($subscription['status']); ?>">
                            <?php echo esc_html(ucfirst($subscription['status'])); ?>
                        </span>
                    </td>
                    <td data-title="<?php esc_attr_e('Next Payment', 'mwf-subscriptions'); ?>">
                        <?php echo esc_html(date('F j, Y', strtotime($subscription['next_billing_date']))); ?>
                    </td>
                    <td data-title="<?php esc_attr_e('Total', 'mwf-subscriptions'); ?>">
                        <?php echo wc_price($subscription['billing_amount']); ?> / <?php echo esc_html($subscription['billing_period']); ?>
                    </td>
                    <td data-title="<?php esc_attr_e('Actions', 'mwf-subscriptions'); ?>">
                        <a href="<?php echo esc_url(wc_get_account_endpoint_url('view-subscription/' . $subscription['id'])); ?>" class="button view">
                            <?php esc_html_e('View', 'mwf-subscriptions'); ?>
                        </a>
                        <a href="<?php echo esc_url($subscription['manage_url']); ?>" class="button manage" target="_blank">
                            <?php esc_html_e('Manage', 'mwf-subscriptions'); ?>
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<style>
.subscription-status {
    padding: 4px 8px;
    border-radius: 3px;
    font-size: 0.9em;
    font-weight: 600;
}
.status-active { background: #c6e1c6; color: #2e4e2e; }
.status-paused { background: #fff3cd; color: #856404; }
.status-cancelled { background: #f8d7da; color: #721c24; }
.status-expired { background: #e2e3e5; color: #383d41; }
</style>
```

#### 3. Template: Subscription Detail

**File:** `templates/my-account/subscription-detail.php`

```php
<div class="woocommerce-subscription-details">
    <h2>
        <?php printf(esc_html__('Subscription #%d', 'mwf-subscriptions'), $subscription['id']); ?>
        <span class="subscription-status status-<?php echo esc_attr($subscription['status']); ?>">
            <?php echo esc_html(ucfirst($subscription['status'])); ?>
        </span>
    </h2>
    
    <table class="shop_table subscription_details">
        <tbody>
            <tr>
                <th><?php esc_html_e('Product:', 'mwf-subscriptions'); ?></th>
                <td><?php echo esc_html($subscription['product_name']); ?></td>
            </tr>
            <?php if (!empty($subscription['variation_name'])): ?>
            <tr>
                <th><?php esc_html_e('Variation:', 'mwf-subscriptions'); ?></th>
                <td><?php echo esc_html($subscription['variation_name']); ?></td>
            </tr>
            <?php endif; ?>
            <tr>
                <th><?php esc_html_e('Billing:', 'mwf-subscriptions'); ?></th>
                <td>
                    <?php echo wc_price($subscription['billing_amount']); ?> 
                    every <?php echo esc_html($subscription['billing_interval']); ?> 
                    <?php echo esc_html($subscription['billing_period']); ?>(s)
                </td>
            </tr>
            <tr>
                <th><?php esc_html_e('Delivery Day:', 'mwf-subscriptions'); ?></th>
                <td><?php echo esc_html(ucfirst($subscription['delivery_day'])); ?></td>
            </tr>
            <tr>
                <th><?php esc_html_e('Next Payment:', 'mwf-subscriptions'); ?></th>
                <td><?php echo esc_html(date('F j, Y', strtotime($subscription['next_billing_date']))); ?></td>
            </tr>
            <tr>
                <th><?php esc_html_e('Start Date:', 'mwf-subscriptions'); ?></th>
                <td><?php echo esc_html(date('F j, Y', strtotime($subscription['created_at']))); ?></td>
            </tr>
        </tbody>
    </table>
    
    <div class="subscription-actions" style="margin-top: 20px;">
        <a href="<?php echo esc_url($subscription['manage_url']); ?>" class="button" target="_blank">
            <?php esc_html_e('Manage Subscription in Admin Portal', 'mwf-subscriptions'); ?>
        </a>
    </div>
    
    <?php if (!empty($subscription['renewal_orders'])): ?>
    <h3 style="margin-top: 30px;"><?php esc_html_e('Renewal History', 'mwf-subscriptions'); ?></h3>
    <table class="shop_table shop_table_responsive">
        <thead>
            <tr>
                <th><?php esc_html_e('Date', 'mwf-subscriptions'); ?></th>
                <th><?php esc_html_e('Amount', 'mwf-subscriptions'); ?></th>
                <th><?php esc_html_e('Status', 'mwf-subscriptions'); ?></th>
                <th><?php esc_html_e('Order', 'mwf-subscriptions'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($subscription['renewal_orders'] as $order): ?>
            <tr>
                <td data-title="<?php esc_attr_e('Date', 'mwf-subscriptions'); ?>">
                    <?php echo esc_html(date('F j, Y', strtotime($order['date']))); ?>
                </td>
                <td data-title="<?php esc_attr_e('Amount', 'mwf-subscriptions'); ?>">
                    <?php echo wc_price($order['amount']); ?>
                </td>
                <td data-title="<?php esc_attr_e('Status', 'mwf-subscriptions'); ?>">
                    <?php echo esc_html(ucfirst($order['status'])); ?>
                </td>
                <td data-title="<?php esc_attr_e('Order', 'mwf-subscriptions'); ?>">
                    <?php if (!empty($order['wordpress_order_id'])): ?>
                        <a href="<?php echo esc_url(wc_get_endpoint_url('view-order', $order['wordpress_order_id'], wc_get_page_permalink('myaccount'))); ?>">
                            #<?php echo esc_html($order['wordpress_order_id']); ?>
                        </a>
                    <?php else: ?>
                        <?php esc_html_e('N/A', 'mwf-subscriptions'); ?>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>
```

#### 4. API Client Updates

**File:** `includes/class-mwf-api-client.php`

Add these methods:

```php
/**
 * Get user subscriptions
 */
public function get_user_subscriptions($user_id) {
    $url = $this->api_url . '/subscriptions/user/' . $user_id;
    
    $response = wp_remote_get($url, array(
        'headers' => array(
            'X-MWF-API-Key' => $this->api_key,
            'Content-Type' => 'application/json'
        ),
        'timeout' => 15
    ));
    
    if (is_wp_error($response)) {
        error_log('[MWF Subscriptions] API Error: ' . $response->get_error_message());
        return false;
    }
    
    $body = wp_remote_retrieve_body($response);
    return json_decode($body, true);
}

/**
 * Get single subscription
 */
public function get_subscription($subscription_id) {
    $url = $this->api_url . '/subscriptions/' . $subscription_id;
    
    $response = wp_remote_get($url, array(
        'headers' => array(
            'X-MWF-API-Key' => $this->api_key,
            'Content-Type' => 'application/json'
        ),
        'timeout' => 15
    ));
    
    if (is_wp_error($response)) {
        error_log('[MWF Subscriptions] API Error: ' . $response->get_error_message());
        return false;
    }
    
    $body = wp_remote_retrieve_body($response);
    return json_decode($body, true);
}
```

---

## Laravel Implementation

### File Structure

```
app/
├── Http/
│   └── Controllers/
│       ├── Api/
│       │   └── SubscriptionController.php
│       └── Admin/
│           └── SubscriptionController.php
├── Models/
│   ├── Subscription.php
│   └── SubscriptionOrder.php
├── Jobs/
│   └── ProcessSubscriptionRenewal.php
├── Services/
│   ├── SubscriptionService.php
│   └── WooCommerceService.php
└── Console/
    └── Commands/
        └── ProcessRenewals.php

database/migrations/
├── xxxx_create_subscriptions_table.php
└── xxxx_create_subscription_orders_table.php

routes/
├── api.php  (API routes for WordPress)
└── web.php  (Admin routes)
```

### Database Migrations

#### 1. Subscriptions Table

```php
Schema::create('subscriptions', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('wordpress_user_id');
    $table->unsignedBigInteger('wordpress_order_id'); // Initial order
    $table->unsignedBigInteger('product_id');
    $table->unsignedBigInteger('variation_id')->nullable();
    $table->string('status')->default('active'); // active, paused, cancelled, expired
    $table->string('billing_period'); // week, month
    $table->integer('billing_interval')->default(1);
    $table->decimal('billing_amount', 10, 2);
    $table->string('delivery_day'); // monday, tuesday, etc.
    $table->date('next_billing_date');
    $table->date('last_billing_date')->nullable();
    $table->string('payment_method')->nullable(); // stripe, paypal, etc.
    $table->string('payment_method_token')->nullable();
    $table->string('customer_email');
    $table->json('billing_address')->nullable();
    $table->timestamps();
    $table->softDeletes();
    
    $table->index('wordpress_user_id');
    $table->index('status');
    $table->index('next_billing_date');
});
```

#### 2. Subscription Orders Table

```php
Schema::create('subscription_orders', function (Blueprint $table) {
    $table->id();
    $table->foreignId('subscription_id')->constrained()->onDelete('cascade');
    $table->unsignedBigInteger('wordpress_order_id')->nullable();
    $table->decimal('amount', 10, 2);
    $table->string('status')->default('pending'); // pending, completed, failed
    $table->date('billing_date');
    $table->text('payment_response')->nullable();
    $table->text('error_message')->nullable();
    $table->timestamps();
    
    $table->index('subscription_id');
    $table->index('status');
    $table->index('billing_date');
});
```

### API Routes

**File:** `routes/api.php`

```php
Route::prefix('subscriptions')->middleware('mwf.api')->group(function () {
    // Create new subscription (from WordPress)
    Route::post('/', [SubscriptionController::class, 'store']);
    
    // Get user subscriptions (for My Account page)
    Route::get('/user/{wordpress_user_id}', [SubscriptionController::class, 'userIndex']);
    
    // Get single subscription
    Route::get('/{id}', [SubscriptionController::class, 'show']);
    
    // Admin actions (called from Laravel admin, not WordPress)
    Route::post('/{id}/cancel', [SubscriptionController::class, 'cancel']);
    Route::post('/{id}/pause', [SubscriptionController::class, 'pause']);
    Route::post('/{id}/resume', [SubscriptionController::class, 'resume']);
    Route::put('/{id}/update-delivery-day', [SubscriptionController::class, 'updateDeliveryDay']);
});
```

### API Middleware

**File:** `app/Http/Middleware/MwfApiAuthentication.php`

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class MwfApiAuthentication
{
    public function handle(Request $request, Closure $next)
    {
        $apiKey = $request->header('X-MWF-API-Key');
        $expectedKey = config('services.mwf.api_key');
        
        if ($apiKey !== $expectedKey) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid API key'
            ], 401);
        }
        
        return $next($request);
    }
}
```

**Config:** Add to `config/services.php`

```php
'mwf' => [
    'api_key' => env('MWF_API_KEY', 'Ffsh8yhsuZEGySvLrP0DihCDDwhPwk4h'),
],
```

### Subscription Controller (API Endpoints)

**File:** `app/Http/Controllers/Api/SubscriptionController.php`

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    protected $subscriptionService;
    
    public function __construct(SubscriptionService $subscriptionService)
    {
        $this->subscriptionService = $subscriptionService;
    }
    
    /**
     * Create new subscription from WordPress
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'wordpress_user_id' => 'required|integer',
            'wordpress_order_id' => 'required|integer',
            'product_id' => 'required|integer',
            'variation_id' => 'nullable|integer',
            'billing_period' => 'required|string|in:week,month',
            'billing_interval' => 'required|integer|min:1',
            'billing_amount' => 'required|numeric|min:0',
            'delivery_day' => 'required|string',
            'payment_method' => 'nullable|string',
            'payment_method_token' => 'nullable|string',
            'customer_email' => 'required|email',
            'billing_address' => 'nullable|array',
        ]);
        
        try {
            $subscription = $this->subscriptionService->createSubscription($validated);
            
            return response()->json([
                'success' => true,
                'subscription_id' => $subscription->id,
                'status' => $subscription->status,
                'next_billing_date' => $subscription->next_billing_date->format('Y-m-d'),
                'message' => 'Subscription created successfully'
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to create subscription: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create subscription'
            ], 500);
        }
    }
    
    /**
     * Get user subscriptions for My Account page
     */
    public function userIndex($wordpress_user_id)
    {
        $subscriptions = Subscription::where('wordpress_user_id', $wordpress_user_id)
            ->whereIn('status', ['active', 'paused'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        $formatted = $subscriptions->map(function($sub) {
            return [
                'id' => $sub->id,
                'product_name' => $sub->getProductName(),
                'variation_name' => $sub->getVariationName(),
                'status' => $sub->status,
                'billing_amount' => $sub->billing_amount,
                'billing_period' => $sub->billing_period,
                'delivery_day' => $sub->delivery_day,
                'next_billing_date' => $sub->next_billing_date->format('Y-m-d'),
                'created_at' => $sub->created_at->format('Y-m-d'),
                'manage_url' => url('/admin/subscriptions/' . $sub->id)
            ];
        });
        
        return response()->json([
            'success' => true,
            'subscriptions' => $formatted
        ]);
    }
    
    /**
     * Get single subscription details
     */
    public function show($id)
    {
        $subscription = Subscription::with('orders')->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'subscription' => [
                'id' => $subscription->id,
                'user_id' => $subscription->wordpress_user_id,
                'status' => $subscription->status,
                'product_name' => $subscription->getProductName(),
                'variation_name' => $subscription->getVariationName(),
                'billing_amount' => $subscription->billing_amount,
                'billing_period' => $subscription->billing_period,
                'billing_interval' => $subscription->billing_interval,
                'delivery_day' => $subscription->delivery_day,
                'next_billing_date' => $subscription->next_billing_date->format('Y-m-d'),
                'last_billing_date' => $subscription->last_billing_date ? $subscription->last_billing_date->format('Y-m-d') : null,
                'created_at' => $subscription->created_at->format('Y-m-d'),
                'manage_url' => url('/admin/subscriptions/' . $subscription->id),
                'renewal_orders' => $subscription->orders->map(function($order) {
                    return [
                        'id' => $order->id,
                        'date' => $order->billing_date->format('Y-m-d'),
                        'amount' => $order->amount,
                        'status' => $order->status,
                        'wordpress_order_id' => $order->wordpress_order_id
                    ];
                })
            ]
        ]);
    }
    
    /**
     * Cancel subscription
     */
    public function cancel($id)
    {
        $subscription = Subscription::findOrFail($id);
        $subscription->update(['status' => 'cancelled']);
        
        return response()->json([
            'success' => true,
            'message' => 'Subscription cancelled'
        ]);
    }
    
    /**
     * Pause subscription
     */
    public function pause($id)
    {
        $subscription = Subscription::findOrFail($id);
        $subscription->update(['status' => 'paused']);
        
        return response()->json([
            'success' => true,
            'message' => 'Subscription paused'
        ]);
    }
    
    /**
     * Resume subscription
     */
    public function resume($id)
    {
        $subscription = Subscription::findOrFail($id);
        $subscription->update([
            'status' => 'active',
            'next_billing_date' => now()->addWeeks(1) // Recalculate
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Subscription resumed'
        ]);
    }
}
```

### Subscription Service

**File:** `app/Services/SubscriptionService.php`

```php
<?php

namespace App\Services;

use App\Models\Subscription;
use Carbon\Carbon;

class SubscriptionService
{
    public function createSubscription(array $data)
    {
        // Calculate first billing date
        $nextBillingDate = $this->calculateNextBillingDate(
            now(),
            $data['billing_period'],
            $data['billing_interval']
        );
        
        $subscription = Subscription::create([
            'wordpress_user_id' => $data['wordpress_user_id'],
            'wordpress_order_id' => $data['wordpress_order_id'],
            'product_id' => $data['product_id'],
            'variation_id' => $data['variation_id'] ?? null,
            'status' => 'active',
            'billing_period' => $data['billing_period'],
            'billing_interval' => $data['billing_interval'],
            'billing_amount' => $data['billing_amount'],
            'delivery_day' => $data['delivery_day'],
            'next_billing_date' => $nextBillingDate,
            'payment_method' => $data['payment_method'] ?? null,
            'payment_method_token' => $data['payment_method_token'] ?? null,
            'customer_email' => $data['customer_email'],
            'billing_address' => $data['billing_address'] ?? null,
        ]);
        
        // Log creation
        \Log::info('Subscription created', [
            'subscription_id' => $subscription->id,
            'user_id' => $data['wordpress_user_id'],
            'next_billing' => $nextBillingDate
        ]);
        
        return $subscription;
    }
    
    public function calculateNextBillingDate(Carbon $fromDate, string $period, int $interval)
    {
        switch ($period) {
            case 'week':
                return $fromDate->copy()->addWeeks($interval);
            case 'month':
                return $fromDate->copy()->addMonths($interval);
            default:
                throw new \Exception('Invalid billing period');
        }
    }
}
```

### Renewal Processing Job

**File:** `app/Jobs/ProcessSubscriptionRenewal.php`

```php
<?php

namespace App\Jobs;

use App\Models\Subscription;
use App\Models\SubscriptionOrder;
use App\Services\WooCommerceService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessSubscriptionRenewal implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    protected $subscription;
    
    public function __construct(Subscription $subscription)
    {
        $this->subscription = $subscription;
    }
    
    public function handle(WooCommerceService $woocommerce)
    {
        \Log::info('Processing renewal for subscription ' . $this->subscription->id);
        
        // Create subscription order record
        $order = SubscriptionOrder::create([
            'subscription_id' => $this->subscription->id,
            'amount' => $this->subscription->billing_amount,
            'billing_date' => now(),
            'status' => 'pending'
        ]);
        
        try {
            // Process payment (implement your payment gateway logic here)
            // For now, we'll just mark as completed
            
            // Optionally create order in WooCommerce
            $wooOrderId = $woocommerce->createRenewalOrder($this->subscription);
            
            $order->update([
                'status' => 'completed',
                'wordpress_order_id' => $wooOrderId
            ]);
            
            // Update subscription
            $this->subscription->update([
                'last_billing_date' => now(),
                'next_billing_date' => $this->calculateNextBillingDate()
            ]);
            
            \Log::info('Renewal processed successfully', [
                'subscription_id' => $this->subscription->id,
                'order_id' => $order->id,
                'woo_order_id' => $wooOrderId
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Renewal failed', [
                'subscription_id' => $this->subscription->id,
                'error' => $e->getMessage()
            ]);
            
            $order->update([
                'status' => 'failed',
                'error_message' => $e->getMessage()
            ]);
        }
    }
    
    protected function calculateNextBillingDate()
    {
        $period = $this->subscription->billing_period;
        $interval = $this->subscription->billing_interval;
        
        switch ($period) {
            case 'week':
                return now()->addWeeks($interval);
            case 'month':
                return now()->addMonths($interval);
            default:
                return now()->addWeek();
        }
    }
}
```

### Console Command (Scheduled Renewals)

**File:** `app/Console/Commands/ProcessRenewals.php`

```php
<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use App\Jobs\ProcessSubscriptionRenewal;
use Illuminate\Console\Command;

class ProcessRenewals extends Command
{
    protected $signature = 'subscriptions:process-renewals';
    protected $description = 'Process due subscription renewals';
    
    public function handle()
    {
        $this->info('Processing subscription renewals...');
        
        $dueSubscriptions = Subscription::where('status', 'active')
            ->where('next_billing_date', '<=', now())
            ->get();
        
        $this->info('Found ' . $dueSubscriptions->count() . ' subscriptions due for renewal');
        
        foreach ($dueSubscriptions as $subscription) {
            ProcessSubscriptionRenewal::dispatch($subscription);
            $this->info('Queued renewal for subscription #' . $subscription->id);
        }
        
        $this->info('Done!');
    }
}
```

**Schedule in:** `app/Console/Kernel.php`

```php
protected function schedule(Schedule $schedule)
{
    // Run every hour to check for due renewals
    $schedule->command('subscriptions:process-renewals')->hourly();
}
```

---

## Testing Plan

### Phase 1: WordPress Setup
1. ✅ Deploy My Account class and templates
2. ✅ Test endpoint registration (`/my-account/subscriptions/`)
3. ✅ Mock API responses to test display
4. ✅ Verify styling matches WooCommerce theme

### Phase 2: Laravel API
1. ✅ Create database migrations
2. ✅ Build API endpoints
3. ✅ Test with Postman/Insomnia
4. ✅ Verify API authentication

### Phase 3: Integration
1. ✅ Complete checkout with subscription product
2. ✅ Verify data sent to Laravel
3. ✅ Check subscription appears in My Account
4. ✅ Test "Manage" button redirect

### Phase 4: Renewals
1. ✅ Manually trigger renewal command
2. ✅ Verify renewal order created
3. ✅ Check email notifications
4. ✅ Test failed payment handling

### Phase 5: Production
1. ✅ Monitor first week of renewals
2. ✅ Check customer support tickets
3. ✅ Verify billing accuracy
4. ✅ Document any issues

---

## Migration Strategy

### Current State (WooCommerce Subscriptions Active)
- Products are Variable Subscription type
- Existing subscriptions managed by addon
- My Account shows WooCommerce Subscriptions page

### Transition Plan

**Step 1: Build Parallel System**
- ✅ Build Laravel subscription system
- ✅ Build WordPress My Account display
- ✅ Test with NEW subscriptions only
- ❌ Don't touch existing subscriptions yet

**Step 2: Dual Operation (1-2 weeks)**
- New subscriptions → Laravel system
- Old subscriptions → WooCommerce Subscriptions
- Both systems run simultaneously
- Monitor for issues

**Step 3: Data Migration**
- Export existing subscriptions from WooCommerce
- Import into Laravel database
- Verify all data transferred correctly
- Test old subscription renewals in new system

**Step 4: Deactivate WooCommerce Subscriptions**
- Stop WooCommerce Subscriptions renewals
- All renewals now via Laravel
- Keep addon installed (for data reference)
- Monitor closely for 1 month

**Step 5: Complete Removal**
- Verify no issues for 30 days
- Uninstall WooCommerce Subscriptions
- Convert Variable Subscription products to standard Variable
- Clean up old data

---

## Support & Monitoring

### Logging
- WordPress: `/wp-content/debug.log`
- Laravel: `storage/logs/laravel.log`

### Key Metrics to Monitor
- Subscription creation success rate
- API call failures
- Renewal processing success rate
- Payment gateway failures
- Customer support tickets

### Alert Triggers
- API downtime > 5 minutes
- Renewal failures > 5%
- Payment processing errors
- Database connection issues

---

## Cost Analysis

### Current Annual Costs
- WooCommerce Subscriptions: £199/year
- **TOTAL: £199/year**

### New System Annual Costs
- Custom development: £0 (one-time build)
- Maintenance: Minimal (existing team)
- Server: £0 (existing infrastructure)
- **TOTAL: £0/year**

### ROI
- **Savings: £199/year**
- **Payback: Immediate** (after initial build)
- **Additional Benefits:**
  - Full control over subscription logic
  - Customizable renewal schedules
  - Better integration with Laravel admin
  - No vendor lock-in
  - Can extend features without addon limitations

---

## Next Steps

1. **Review this plan** - Confirm approach aligns with business needs
2. **Laravel API development** - Build subscription endpoints
3. **WordPress My Account page** - Build display layer
4. **Testing** - Comprehensive integration testing
5. **Soft launch** - Enable for new subscriptions only
6. **Monitor** - Watch for 2 weeks
7. **Migrate** - Move existing subscriptions
8. **Deprecate** - Remove WooCommerce Subscriptions

---

**Document Version:** 1.0  
**Last Updated:** November 30, 2025  
**Status:** Pending Approval
