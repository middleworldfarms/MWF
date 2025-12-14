# MWF Integration: WordPress User, Funds, and Activity Endpoints for Laravel Admin

This documentation describes the robust REST API endpoints exposed by the `mwf-integration` WordPress plugin for user management, funds, and activity tracking. These endpoints are designed for seamless integration with a Laravel admin panel.

---

## Endpoints Overview

### 1. List/Filter Users
**GET** `/wp-json/mwf/v1/users/list`

**Query Parameters:**
- `q` (string, optional): Search term (name, email, username)
- `role` (string, optional): User role (e.g., `customer`, `subscriber`)
- `page` (int, optional): Page number (default: 1)
- `per_page` (int, optional): Results per page (default: 25)

**Response:**
```json
{
  "success": true,
  "users": [
    {
      "id": 123,
      "display_name": "John Doe",
      "email": "john@example.com",
      "username": "johnny",
      "avatar_url": "...",
      "roles": ["customer"],
      "registration_date": "2024-01-01 12:00:00",
      "wc_data": {
        "total_spent": "100.00",
        "account_funds": "25.00",
        "order_count": 5,
        "billing_first_name": "John",
        "billing_last_name": "Doe",
        "billing_city": "New York"
      },
      "subscriptions": [
        {
          "id": 456,
          "status": "active",
          "next_payment": "2024-08-01 00:00:00",
          "total": "10.00"
        }
      ],
      "last_login": "2024-07-10 09:00:00"
    }
  ],
  "page": 1,
  "per_page": 25,
  "total_found": 1,
  "total_users": 100
}
```

---

### 2. View User Funds
**GET** `/wp-json/mwf/v1/users/{id}/funds`

**Response:**
```json
{
  "success": true,
  "user_id": 123,
  "balance": "25.00"
}
```

---

### 3. Edit User Funds
**POST** `/wp-json/mwf/v1/users/{id}/funds`

**Body:**
```json
{
  "amount": 10.00,
  "action": "add" // "set" | "add" | "subtract"
}
```

**Response:**
```json
{
  "success": true,
  "user_id": 123,
  "old_balance": "25.00",
  "new_balance": "35.00",
  "action": "add",
  "amount": 10.00
}
```

---

### 4. Get User Details
**GET** `/wp-json/mwf/v1/users/{id}`

**Response:**
```json
{
  "success": true,
  "user": {
    "id": 123,
    "display_name": "John Doe",
    "email": "john@example.com",
    "username": "johnny",
    "avatar_url": "...",
    "roles": ["customer"],
    "registration_date": "2024-01-01 12:00:00",
    "wc_data": {
      "total_spent": "100.00",
      "order_count": 5,
      "billing_first_name": "John",
      "billing_last_name": "Doe",
      "billing_company": "Acme Inc.",
      "billing_address_1": "123 Main St",
      "billing_address_2": "Apt 4B",
      "billing_city": "New York",
      "billing_state": "NY",
      "billing_postcode": "10001",
      "billing_country": "US",
      "billing_phone": "555-1234",
      "account_funds": "35.00"
    },
    "recent_orders": [
      {
        "id": 789,
        "date": "2024-07-01 10:00:00",
        "status": "completed",
        "total": "50.00",
        "items_count": 2
      }
    ],
    "subscriptions": [
      {
        "id": 456,
        "status": "active",
        "next_payment": "2024-08-01 00:00:00",
        "total": "10.00"
      }
    ],
    "last_login": "2024-07-10 09:00:00"
  }
}
```

---

### 5. Get Recent Users
**GET** `/wp-json/mwf/v1/users/recent?limit={limit}`

Returns the most recently active users (login activity), with fallback to all customers/subscribers if no activity found.

**Response:**
```json
{
  "success": true,
  "users": [ ... ],
  "total_found": 100
}
```

---

### 6. Get User Activity
**GET** `/wp-json/mwf/v1/users/{id}/activity?limit=10`

**Response:**
```json
{
  "success": true,
  "activities": [
    {
      "id": 1,
      "type": "login",
      "description": "User logged in",
      "date": "2024-07-10 09:00:00",
      "metadata": { "ip_address": "1.2.3.4" },
      "ip_address": "1.2.3.4"
    }
  ],
  "count": 1
}
```

---

### 7. Legacy User Activity
**GET** `/wp-json/mwf/v1/users/{id}/activity-legacy`

Returns last active (WooCommerce), last login (session tokens), and recent login sessions.

**Response:**
```json
{
  "success": true,
  "user_id": 123,
  "last_active": {
    "timestamp": 1721040000,
    "date": "2024-07-15 09:00:00"
  },
  "last_login": {
    "timestamp": 1721040000,
    "date": "2024-07-15 09:00:00"
  },
  "recent_sessions": [ ... ]
}
```

---

## Laravel Admin Integration Example

### Fetch Users (with search, pagination)
```php
$response = Http::get('https://yourwp.com/wp-json/mwf/v1/users/list', [
    'q' => 'john',
    'page' => 1,
    'per_page' => 25
]);
$users = $response->json('users');
```

### View/Edit User Funds
```php
// View funds
$response = Http::get('https://yourwp.com/wp-json/mwf/v1/users/123/funds');
$balance = $response->json('balance');

// Edit funds
$response = Http::post('https://yourwp.com/wp-json/mwf/v1/users/123/funds', [
    'amount' => 10.00,
    'action' => 'add'
]);
$new_balance = $response->json('new_balance');
```

### Get User Activity
```php
$response = Http::get('https://yourwp.com/wp-json/mwf/v1/users/123/activity', [
    'limit' => 10
]);
$activities = $response->json('activities');
```

---

## Notes
- All endpoints return JSON and are authenticated via API key or admin session.
- For WooCommerce/Subscriptions data, ensure relevant plugins are active on WordPress.
- Use the provided code examples in Laravel controllers/services for easy integration.

---

## Changelog
- Added `/users/list`, `/users/{id}/funds` (GET/POST), `/users/recent`, `/users/{id}/activity`, `/users/{id}/activity-legacy` endpoints.
- All endpoints return robust, consistent data structures for Laravel admin.
- Documentation and code examples provided for easy highlighting and copy-paste.

---

## Contact
For support or questions, contact the MWF Integration team.
