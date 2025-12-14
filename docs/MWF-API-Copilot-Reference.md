# MWF API Reference for GitHub Copilot

## Quick API Reference for Middleworld Farms Integration

### Base Configuration
```php
// API Base URL
$api_base = 'https://middleworldfarms.org/wp-json/mwf/v1/';

// API Key Header
$headers = ['X-WC-API-Key' => 'Ffsh8yhsuZEGySvLrP0DihCDDwhPwk4h'];
```

### Common Laravel HTTP Patterns

#### User Search
```php
// Search for users by email/name
$response = Http::withHeaders($headers)
    ->get($api_base . 'users/search', ['search' => $query]);
$users = $response->json()['users'] ?? [];
```

#### Check User Funds
```php
// Check if user has sufficient funds
$response = Http::withHeaders($headers)
    ->post($api_base . 'funds', [
        'action' => 'check',
        'email' => $userEmail,
        'amount' => $orderTotal
    ]);
$hasFunds = $response->json()['has_funds'] ?? false;
```

#### Deduct Funds
```php
// Deduct funds from user account
$response = Http::withHeaders($headers)
    ->post($api_base . 'funds', [
        'action' => 'deduct',
        'email' => $userEmail,
        'amount' => $amount,
        'order_id' => $orderId,
        'description' => 'Purchase from admin system'
    ]);
```

#### Add Funds (Admin)
```php
// Add funds to user account
$response = Http::withHeaders($headers)
    ->post($api_base . 'funds/add', [
        'email' => $userEmail,
        'amount' => $creditAmount,
        'description' => 'Manual credit adjustment'
    ]);
```

#### User Switching
```php
// Generate user switch URL for admin
$response = Http::withHeaders($headers)
    ->post($api_base . 'users/switch', [
        'user_id' => $userId
    ]);
$switchUrl = $response->json()['switch_url'] ?? null;
```

#### Create WooCommerce Order
```php
// Create order in WooCommerce
$response = Http::withHeaders($headers)
    ->post($api_base . 'create-order', [
        'customer_email' => $email,
        'items' => [
            ['product_id' => 123, 'quantity' => 2],
            ['product_id' => 456, 'quantity' => 1, 'variation_id' => 789]
        ],
        'billing_address' => $billingData,
        'shipping_address' => $shippingData,
        'payment_method' => 'account_funds'
    ]);
```

### Error Handling Patterns

```php
// Standard error handling
$response = Http::withHeaders($headers)->post($api_base . $endpoint, $data);

if (!$response->successful()) {
    throw new Exception('API request failed: ' . $response->status());
}

$result = $response->json();
if (!($result['success'] ?? false)) {
    throw new Exception($result['error'] ?? 'Unknown API error');
}

return $result;
```

### Response Data Structures

#### User Object
```php
[
    'id' => 123,
    'username' => 'johndoe',
    'email' => 'john@example.com',
    'display_name' => 'John Doe',
    'first_name' => 'John',
    'last_name' => 'Doe',
    'account_funds' => 25.50,
    'billing_address' => [...]
]
```

#### Funds Response
```php
// Check funds
['success' => true, 'has_funds' => true, 'current_balance' => 25.50]

// Deduct funds
['success' => true, 'transaction_id' => 'txn_123', 'new_balance' => 10.00]
```

#### Order Response
```php
[
    'success' => true,
    'order_id' => 12345,
    'order_total' => 35.50,
    'order_status' => 'processing'
]
```

### Laravel Service Class Template

```php
<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Exception;

class MWFApiService
{
    private string $baseUrl = 'https://middleworldfarms.org/wp-json/mwf/v1/';
    private array $headers;

    public function __construct()
    {
        $this->headers = [
            'X-WC-API-Key' => config('services.mwf.api_key', 'Ffsh8yhsuZEGySvLrP0DihCDDwhPwk4h'),
            'Content-Type' => 'application/json'
        ];
    }

    public function searchUsers(string $query): array
    {
        return $this->makeRequest('GET', 'users/search', ['search' => $query]);
    }

    public function getUserById(int $userId): array
    {
        return $this->makeRequest('GET', "users/{$userId}");
    }

    public function checkFunds(string $email, float $amount): bool
    {
        $response = $this->makeRequest('POST', 'funds', [
            'action' => 'check',
            'email' => $email,
            'amount' => $amount
        ]);
        
        return $response['has_funds'] ?? false;
    }

    public function deductFunds(string $email, float $amount, string $orderId = null): array
    {
        return $this->makeRequest('POST', 'funds', [
            'action' => 'deduct',
            'email' => $email,
            'amount' => $amount,
            'order_id' => $orderId,
            'description' => 'Admin system purchase'
        ]);
    }

    public function addFunds(string $email, float $amount, string $description = 'Manual credit'): array
    {
        return $this->makeRequest('POST', 'funds/add', [
            'email' => $email,
            'amount' => $amount,
            'description' => $description
        ]);
    }

    public function createOrder(array $orderData): array
    {
        return $this->makeRequest('POST', 'create-order', $orderData);
    }

    public function switchToUser(int $userId): string
    {
        $response = $this->makeRequest('POST', 'users/switch', ['user_id' => $userId]);
        return $response['switch_url'] ?? '';
    }

    private function makeRequest(string $method, string $endpoint, array $data = []): array
    {
        $url = $this->baseUrl . $endpoint;
        
        $response = Http::withHeaders($this->headers)
            ->timeout(30)
            ->{strtolower($method)}($url, $data);

        if (!$response->successful()) {
            throw new Exception("MWF API request failed: HTTP {$response->status()}");
        }

        $result = $response->json();
        
        if (!($result['success'] ?? false)) {
            throw new Exception($result['error'] ?? 'Unknown MWF API error');
        }

        return $result;
    }
}
```

### Quick Testing Commands

```bash
# Test API availability
curl -I "https://middleworldfarms.org/wp-json/mwf/v1/users/search"

# Test with API key
curl -H "X-WC-API-Key: Ffsh8yhsuZEGySvLrP0DihCDDwhPwk4h" \
  "https://middleworldfarms.org/wp-json/mwf/v1/users/search?search=test"

# Test funds check
curl -H "X-WC-API-Key: Ffsh8yhsuZEGySvLrP0DihCDDwhPwk4h" \
  -H "Content-Type: application/json" \
  -d '{"action":"check","email":"test@example.com","amount":10}' \
  "https://middleworldfarms.org/wp-json/mwf/v1/funds"
```

### Common Integration Patterns

#### Customer Dashboard Integration
```php
// Get customer data for dashboard
$user = $this->mwfApi->getUserById($customerId);
$balance = $user['account_funds'];
$orders = $this->getCustomerOrders($customerId);
```

#### Order Processing Workflow
```php
// 1. Check funds availability
if (!$this->mwfApi->checkFunds($email, $total)) {
    throw new Exception('Insufficient funds');
}

// 2. Create order in WooCommerce
$order = $this->mwfApi->createOrder($orderData);

// 3. Deduct funds
$transaction = $this->mwfApi->deductFunds($email, $total, $order['order_id']);
```

#### Admin User Management
```php
// Search and switch to customer
$users = $this->mwfApi->searchUsers($searchTerm);
$switchUrl = $this->mwfApi->switchToUser($selectedUserId);
return redirect($switchUrl);
```

### Environment Configuration

```php
// .env
MWF_API_KEY=Ffsh8yhsuZEGySvLrP0DihCDDwhPwk4h
MWF_API_BASE_URL=https://middleworldfarms.org/wp-json/mwf/v1/

// config/services.php
'mwf' => [
    'api_key' => env('MWF_API_KEY'),
    'base_url' => env('MWF_API_BASE_URL'),
],
```

---

**Copilot Prompt Suggestions:**

- "Create a function to check user funds using MWF API"
- "Write error handling for MWF API responses"
- "Generate user search with MWF API integration"
- "Build order creation workflow with funds deduction"
- "Create admin user switching functionality"
