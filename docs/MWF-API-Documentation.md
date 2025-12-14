# MWF Integration REST API Documentation

## Overview

The Middleworld Farms Integration plugin provides custom REST API endpoints for seamless integration between the WordPress WooCommerce shop and the Laravel admin system. This API handles user management, funds operations, order creation, and subscription management.

## Base URL
```
https://middleworldfarms.org/wp-json/mwf/v1/
```

## Authentication

All admin endpoints require API key authentication via the `X-WC-API-Key` header.

**Default API Key:** `Ffsh8yhsuZEGySvLrP0DihCDDwhPwk4h`

```http
X-WC-API-Key: Ffsh8yhsuZEGySvLrP0DihCDDwhPwk4h
```

## Endpoints

### 1. User Management

#### Search Users
```http
GET /users/search?search={query}
```

**Parameters:**
- `search` (required): Search term for username, email, or display name

**Response:**
```json
{
  "success": true,
  "users": [
    {
      "id": 123,
      "username": "johndoe",
      "email": "john@example.com",
      "display_name": "John Doe",
      "first_name": "John",
      "last_name": "Doe",
      "account_funds": 25.50
    }
  ]
}
```

#### Get User by ID
```http
GET /users/{id}
```

**Response:**
```json
{
  "success": true,
  "user": {
    "id": 123,
    "username": "johndoe",
    "email": "john@example.com",
    "display_name": "John Doe",
    "first_name": "John",
    "last_name": "Doe",
    "account_funds": 25.50,
    "billing_address": {
      "first_name": "John",
      "last_name": "Doe",
      "address_1": "123 Main St",
      "city": "Anytown",
      "postcode": "12345",
      "phone": "555-1234"
    }
  }
}
```

#### Switch User (User Switching)
```http
POST /users/switch
```

**Request Body:**
```json
{
  "user_id": 123
}
```

**Response:**
```json
{
  "success": true,
  "message": "Switched to user successfully",
  "switch_url": "https://middleworldfarms.org?switch_to=123&switch_token=abc123"
}
```

#### Get Recent Users
```http
GET /users/recent
```

**Response:**
```json
{
  "success": true,
  "users": [
    {
      "id": 123,
      "display_name": "John Doe",
      "email": "john@example.com",
      "last_login": "2025-06-05 14:30:00"
    }
  ]
}
```

### 2. Account Funds Management

#### Check/Deduct Funds
```http
POST /funds
```

**Request Body:**
```json
{
  "action": "check", // or "deduct"
  "email": "customer@example.com",
  "amount": 15.50,
  "order_id": "12345", // optional
  "description": "Self-serve shop purchase" // optional
}
```

**Response (Check):**
```json
{
  "success": true,
  "has_funds": true,
  "current_balance": 25.50
}
```

**Response (Deduct):**
```json
{
  "success": true,
  "transaction_id": "txn_123456",
  "new_balance": 10.00
}
```

#### Add Funds (Admin Only)
```http
POST /funds/add
```

**Request Body:**
```json
{
  "email": "customer@example.com",
  "amount": 50.00,
  "order_id": "12345", // optional
  "description": "Manual credit" // optional
}
```

**Response:**
```json
{
  "success": true,
  "transaction_id": "txn_123456",
  "previous_balance": 25.50,
  "new_balance": 75.50
}
```

### 3. Order Management

#### Create Order
```http
POST /create-order
```

**Request Body:**
```json
{
  "customer_email": "customer@example.com",
  "items": [
    {
      "product_id": 123,
      "quantity": 2,
      "variation_id": 456 // optional for variable products
    }
  ],
  "billing_address": {
    "first_name": "John",
    "last_name": "Doe",
    "address_1": "123 Main St",
    "city": "Anytown",
    "postcode": "12345",
    "phone": "555-1234"
  },
  "shipping_address": {
    // same format as billing_address
  },
  "payment_method": "account_funds", // or other payment methods
  "order_notes": "Special delivery instructions"
}
```

**Response:**
```json
{
  "success": true,
  "order_id": 12345,
  "order_total": 35.50,
  "order_status": "processing"
}
```

### 4. Subscription Management

#### Get Subscription Payment Status
```http
GET /subscription-payment-status?subscription_id={id}
```

**Response:**
```json
{
  "success": true,
  "subscription_id": 123,
  "status": "active",
  "next_payment_date": "2025-07-05",
  "payment_method": "stripe",
  "total": 25.00
}
```

#### Process Subscription Payment
```http
POST /subscription-payment/process
```

**Request Body:**
```json
{
  "subscription_id": 123,
  "payment_method": "account_funds",
  "amount": 25.00
}
```

**Response:**
```json
{
  "success": true,
  "payment_id": "pay_123456",
  "status": "completed",
  "next_payment_date": "2025-08-05"
}
```

## Error Handling

All endpoints return consistent error formats:

```json
{
  "success": false,
  "error": "Error message here",
  "code": "error_code" // optional
}
```

**Common Error Codes:**
- `invalid_api_key`: API key is missing or invalid
- `user_not_found`: Specified user does not exist
- `insufficient_funds`: Not enough account funds for transaction
- `invalid_parameters`: Required parameters missing or invalid
- `order_creation_failed`: Failed to create WooCommerce order

## Rate Limiting

- No specific rate limits implemented
- Server timeout: 30 seconds per request
- Recommended: Implement client-side request throttling

## Testing

### Test Endpoint Availability
```bash
curl -I "https://middleworldfarms.org/wp-json/mwf/v1/users/search"
# Should return HTTP 401 (requires API key)
```

### Test with API Key
```bash
curl -H "X-WC-API-Key: Ffsh8yhsuZEGySvLrP0DihCDDwhPwk4h" \
     "https://middleworldfarms.org/wp-json/mwf/v1/users/search?search=test"
```

## Integration Examples

### Laravel HTTP Client Example
```php
use Illuminate\Support\Facades\Http;

class MWFApiClient
{
    private $baseUrl = 'https://middleworldfarms.org/wp-json/mwf/v1/';
    private $apiKey = 'Ffsh8yhsuZEGySvLrP0DihCDDwhPwk4h';

    public function searchUsers($query)
    {
        $response = Http::withHeaders([
            'X-WC-API-Key' => $this->apiKey
        ])->get($this->baseUrl . 'users/search', [
            'search' => $query
        ]);

        return $response->json();
    }

    public function checkUserFunds($email, $amount)
    {
        $response = Http::withHeaders([
            'X-WC-API-Key' => $this->apiKey
        ])->post($this->baseUrl . 'funds', [
            'action' => 'check',
            'email' => $email,
            'amount' => $amount
        ]);

        return $response->json();
    }
}
```

### JavaScript/Fetch Example
```javascript
class MWFApiClient {
    constructor() {
        this.baseUrl = 'https://middleworldfarms.org/wp-json/mwf/v1/';
        this.apiKey = 'Ffsh8yhsuZEGySvLrP0DihCDDwhPwk4h';
    }

    async searchUsers(query) {
        const response = await fetch(`${this.baseUrl}users/search?search=${query}`, {
            headers: {
                'X-WC-API-Key': this.apiKey
            }
        });
        return response.json();
    }

    async deductFunds(email, amount, orderId) {
        const response = await fetch(`${this.baseUrl}funds`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WC-API-Key': this.apiKey
            },
            body: JSON.stringify({
                action: 'deduct',
                email: email,
                amount: amount,
                order_id: orderId
            })
        });
        return response.json();
    }
}
```

## Security Notes

1. **API Key Security**: Store API keys securely, never expose in client-side code
2. **HTTPS Only**: All requests must use HTTPS
3. **Input Validation**: All inputs are sanitized and validated
4. **Permission Checks**: Admin endpoints require proper authentication
5. **Error Logging**: Failed requests are logged for monitoring

## Support

- **Plugin File**: `/wp-content/plugins/mwf-integration/mwf-integration.php`
- **Debug Logs**: Check `/wp-content/debug.log` for errors
- **WordPress Admin**: WooCommerce → Settings → API → Keys

## Changelog

### Version 1.0
- Initial release with core API endpoints
- User management and switching functionality
- Account funds operations
- Order creation and management
- Subscription payment processing

---

*Last Updated: June 5, 2025*
