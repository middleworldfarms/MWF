# Laravel API Response Format Reference

**For Laravel Team** - Exact JSON formats WordPress expects

---

## Endpoint 1: Create Subscription

**WordPress calls this on order completion**

```http
POST /api/subscriptions
Content-Type: application/json
X-MWF-API-Key: Ffsh8yhsuZEGySvLrP0DihCDDwhPwk4h

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

**Laravel must return:**
```json
{
  "success": true,
  "subscription_id": 456,
  "status": "active",
  "next_billing_date": "2025-12-07",
  "message": "Subscription created successfully"
}
```

**Required fields:**
- ✅ `success` (boolean) - WordPress checks this first
- ✅ `subscription_id` (integer) - Saved to order meta
- ✅ `status` (string) - "active", "paused", etc.
- ✅ `next_billing_date` (string, YYYY-MM-DD format)
- ✅ `message` (string) - For logging

---

## Endpoint 2: Get User Subscriptions

**WordPress calls this on My Account → Subscriptions page load**

```http
GET /api/subscriptions/user/123
X-MWF-API-Key: Ffsh8yhsuZEGySvLrP0DihCDDwhPwk4h
```

**Laravel must return:**
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
      "manage_url": "https://admin.middleworldfarms.org:8444/admin/subscriptions/456"
    }
  ]
}
```

**Required fields per subscription:**
- ✅ `id` (integer) - Subscription ID
- ✅ `product_name` (string) - Display name (e.g., "Large Family Vegetable Box")
- ✅ `variation_name` (string) - Can be empty string "" if no variation
- ✅ `status` (string) - "active", "paused", "cancelled", "expired"
- ✅ `billing_amount` (float) - Price as number (e.g., 25.00)
- ✅ `billing_period` (string) - "week" or "month"
- ✅ `delivery_day` (string) - "monday", "tuesday", etc.
- ✅ `next_billing_date` (string) - YYYY-MM-DD format
- ✅ `created_at` (string) - YYYY-MM-DD format
- ✅ `manage_url` (string) - Full URL to Laravel admin page

**Important:**
- Return **empty array** `[]` if user has no subscriptions (not null)
- Only return `active` and `paused` subscriptions (skip cancelled/expired)

---

## Endpoint 3: Get Single Subscription

**WordPress calls this on My Account → View Subscription page**

```http
GET /api/subscriptions/456
X-MWF-API-Key: Ffsh8yhsuZEGySvLrP0DihCDDwhPwk4h
```

**Laravel must return:**
```json
{
  "success": true,
  "subscription": {
    "id": 456,
    "user_id": 123,
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
    "manage_url": "https://admin.middleworldfarms.org:8444/admin/subscriptions/456",
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

**Required fields:**
- ✅ `id` (integer)
- ✅ `user_id` (integer) - WordPress user ID (WordPress verifies ownership)
- ✅ `status` (string)
- ✅ `product_name` (string)
- ✅ `variation_name` (string) - Can be ""
- ✅ `billing_amount` (float)
- ✅ `billing_period` (string)
- ✅ `billing_interval` (integer) - e.g., 1, 2, 3
- ✅ `delivery_day` (string)
- ✅ `next_billing_date` (string, YYYY-MM-DD)
- ✅ `last_billing_date` (string, YYYY-MM-DD) - Can be null
- ✅ `created_at` (string, YYYY-MM-DD)
- ✅ `manage_url` (string)
- ✅ `renewal_orders` (array) - Can be empty []

**renewal_orders array items:**
- ✅ `id` (integer) - Laravel subscription_order ID
- ✅ `date` (string, YYYY-MM-DD)
- ✅ `amount` (float)
- ✅ `status` (string) - "pending", "completed", "failed"
- ✅ `wordpress_order_id` (integer) - WooCommerce order ID, can be null

---

## Error Responses

**If API key invalid:**
```json
{
  "success": false,
  "message": "Invalid API key"
}
```
HTTP Status: 401

**If subscription not found:**
```json
{
  "success": false,
  "message": "Subscription not found"
}
```
HTTP Status: 404

**If server error:**
```json
{
  "success": false,
  "message": "Failed to create subscription"
}
```
HTTP Status: 500

**WordPress checks `success` field first** - if false, displays error message to user

---

## Date Format Rules

**CRITICAL:** All dates must be `YYYY-MM-DD` format (ISO 8601 date only, no time)

✅ Correct:
```json
"next_billing_date": "2025-12-07"
"created_at": "2025-11-01"
```

❌ Wrong:
```json
"next_billing_date": "2025-12-07T15:30:00Z"  // No timestamps
"created_at": "07/12/2025"  // No DD/MM/YYYY
"next_billing_date": "December 7, 2025"  // No words
```

**Why:** WordPress uses `strtotime()` and `date()` functions which expect this format.

---

## Field Type Reference

```php
// WordPress expects these exact types:
"success": true,                    // boolean
"subscription_id": 456,             // integer
"id": 456,                          // integer
"user_id": 123,                     // integer
"billing_amount": 25.00,            // float (not string "25.00")
"billing_interval": 1,              // integer
"status": "active",                 // string
"product_name": "Box Name",         // string
"variation_name": "",               // string (can be empty)
"billing_period": "week",           // string
"delivery_day": "monday",           // string
"next_billing_date": "2025-12-07",  // string (YYYY-MM-DD)
"created_at": "2025-11-01",         // string (YYYY-MM-DD)
"manage_url": "https://...",        // string (full URL)
"renewal_orders": [],               // array (can be empty)
"wordpress_order_id": 5789          // integer (or null)
```

---

## Testing Checklist

### Test 1: Create Subscription
```bash
curl -X POST https://admin.middleworldfarms.org:8444/api/subscriptions \
  -H "Content-Type: application/json" \
  -H "X-MWF-API-Key: Ffsh8yhsuZEGySvLrP0DihCDDwhPwk4h" \
  -d '{
    "wordpress_user_id": 1,
    "wordpress_order_id": 1234,
    "product_id": 226082,
    "variation_id": 226085,
    "billing_period": "week",
    "billing_interval": 1,
    "billing_amount": 25.00,
    "delivery_day": "monday",
    "customer_email": "test@example.com"
  }'
```

Expected: `{"success": true, "subscription_id": 1, ...}`

### Test 2: Get User Subscriptions
```bash
curl https://admin.middleworldfarms.org:8444/api/subscriptions/user/1 \
  -H "X-MWF-API-Key: Ffsh8yhsuZEGySvLrP0DihCDDwhPwk4h"
```

Expected: `{"success": true, "subscriptions": [...]}`

### Test 3: Get Single Subscription
```bash
curl https://admin.middleworldfarms.org:8444/api/subscriptions/1 \
  -H "X-MWF-API-Key: Ffsh8yhsuZEGySvLrP0DihCDDwhPwk4h"
```

Expected: `{"success": true, "subscription": {...}}`

---

## Common Issues & Fixes

### Issue 1: WordPress shows "Unable to load subscriptions"
**Cause:** `success` field missing or false  
**Fix:** Always include `"success": true` in response

### Issue 2: Dates show as "January 1, 1970"
**Cause:** Date format wrong  
**Fix:** Use `YYYY-MM-DD` format, not timestamps

### Issue 3: Price shows as "$0.00"
**Cause:** `billing_amount` is string not number  
**Fix:** Return `25.00` not `"25.00"`

### Issue 4: "Manage" button doesn't work
**Cause:** `manage_url` missing or malformed  
**Fix:** Return full URL: `https://admin.middleworldfarms.org:8444/admin/subscriptions/456`

### Issue 5: Renewal history empty when it shouldn't be
**Cause:** `renewal_orders` not populated  
**Fix:** Include array even if empty: `"renewal_orders": []`

---

## WordPress Code Reference

**How WordPress calls these endpoints:**

```php
// In class-mwf-api-client.php:

// Get user subscriptions
public function get_user_subscriptions($user_id) {
    $url = 'https://admin.middleworldfarms.org:8444/api/subscriptions/user/' . $user_id;
    
    $response = wp_remote_get($url, array(
        'headers' => array(
            'X-MWF-API-Key' => 'Ffsh8yhsuZEGySvLrP0DihCDDwhPwk4h',
            'Content-Type' => 'application/json'
        ),
        'timeout' => 15
    ));
    
    $body = wp_remote_retrieve_body($response);
    return json_decode($body, true); // Expects associative array
}

// Get single subscription
public function get_subscription($subscription_id) {
    $url = 'https://admin.middleworldfarms.org:8444/api/subscriptions/' . $subscription_id;
    // ... same pattern
}
```

**How WordPress uses the response:**

```php
// In class-mwf-my-account.php:

$response = $this->api_client->get_user_subscriptions($user_id);

if (!$response || !isset($response['success']) || !$response['success']) {
    echo '<p>Unable to load subscriptions.</p>';
    return;
}

$subscriptions = $response['subscriptions'] ?? array();

// Loops through $subscriptions array
foreach ($subscriptions as $subscription) {
    echo $subscription['product_name'];  // Must be string
    echo wc_price($subscription['billing_amount']);  // Must be float
    echo date('F j, Y', strtotime($subscription['next_billing_date']));  // Must be YYYY-MM-DD
}
```

---

## Quick Validation

Use this checklist before saying "API is ready":

- [ ] All 3 endpoints return HTTP 200 on success
- [ ] All responses include `"success": true`
- [ ] All dates in `YYYY-MM-DD` format (no timestamps)
- [ ] `billing_amount` is float, not string
- [ ] `manage_url` is full URL starting with https://
- [ ] Empty arrays `[]` not null for subscriptions/renewal_orders
- [ ] API key middleware checks `X-MWF-API-Key` header
- [ ] Tested with curl commands above
- [ ] Error responses return proper HTTP status codes

---

## Contact

Once you've made the tweaks, test with the curl commands above and let WordPress team know!

**WordPress is ready and waiting. Just needs these exact JSON formats.** 🚀
