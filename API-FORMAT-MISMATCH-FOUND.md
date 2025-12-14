# ⚠️ API FORMAT MISMATCH DISCOVERED

**Date**: November 30, 2025  
**Status**: 🔴 **BLOCKING** - Prevents WordPress ↔ Laravel integration

## Problem

Integration testing revealed that **WordPress and Laravel are using different field names** for the subscription creation API.

## Test Results

### WordPress is sending:
```json
{
  "wordpress_user_id": 1,
  "wordpress_order_id": 99999,
  "product_id": 226082,
  "variation_id": 226085,
  "billing_period": "week",
  "billing_interval": 1,
  "billing_amount": 25.00,
  "delivery_day": "monday",
  "payment_method": "stripe",
  "customer_email": "test@middleworldfarms.org"
}
```

### Laravel expects:
```json
{
  "wp_user_id": ???,           // NOT "wordpress_user_id"
  "wc_order_id": ???,          // NOT "wordpress_order_id"  
  "plan_id": ???,              // NOT "product_id"
  "delivery_address": {        // NEW REQUIREMENT (not documented)
    "address_1": "???",
    "city": "???",
    "postcode": "???"
  }
  // Other fields unknown
}
```

### Laravel validation errors:
```json
{
  "success": false,
  "errors": {
    "wp_user_id": ["The wp user id field is required."],
    "wc_order_id": ["The wc order id field is required."],
    "plan_id": ["The plan id field is required."],
    "delivery_address": ["The delivery address field is required."],
    "delivery_address.address_1": ["required"],
    "delivery_address.city": ["required"],
    "delivery_address.postcode": ["required"]
  }
}
```

## Root Cause

The **LARAVEL-API-FORMAT-REFERENCE.md** document specified one format, but Laravel was already implemented with different field names. The Laravel team either:

1. Didn't see the format reference document
2. Saw it but didn't update their existing code
3. Has a different understanding of the field naming convention

## Impact

- ❌ WordPress My Account cannot display subscriptions (API calls fail)
- ❌ Subscription creation from WooCommerce orders blocked
- ❌ Customer-facing features 100% non-functional
- ⚠️ Blocks all testing and deployment

## Resolution Options

### Option A: Update WordPress to match Laravel ✅ **RECOMMENDED**
**Why**: Laravel has more complex business logic already built. Changing field names in WordPress is simpler.

**Changes needed**:
- Update `class-mwf-api-client.php` to use `wp_user_id`, `wc_order_id`, `plan_id`
- Add delivery address handling
- Update subscription creation calls from checkout

**Pros**:
- Faster (1 file to change vs. Laravel controllers, services, validation rules)
- Preserves existing Laravel database schema
- Laravel team doesn't need to do anything

**Cons**:
- Documented format in LARAVEL-API-FORMAT-REFERENCE.md becomes incorrect

### Option B: Update Laravel to match WordPress documentation
**Why**: Stick to the documented format we all agreed on.

**Changes needed in Laravel**:
- Update controller validation rules
- Update service layer field names
- Update database column names (if needed)
- Test all endpoints

**Pros**:
- Documentation stays correct
- WordPress team already built to this spec

**Cons**:
- More work for Laravel team
- Risk of breaking existing Laravel admin functionality

### Option C: Compromise - Field Name Mapping Layer
Create a translation layer that converts between formats.

**Pros**:
- Both sides stay as-is
- Can be done in middleware

**Cons**:
- Added complexity
- Performance overhead
- Hard to maintain

## Immediate Next Steps

1. **Laravel team**: Share the EXACT API format your create endpoint expects (all fields, all nested objects)
2. **WordPress team**: Update API client to match Laravel's format (Option A)
3. **Test again**: Re-run `test-subscription-api.sh` after changes
4. **Update docs**: Document the ACTUAL working format in LARAVEL-API-FORMAT-REFERENCE.md

## Additional Missing Fields

Laravel requires `delivery_address` object - this was NOT in our original plan. Questions:

1. Should WordPress pass the WooCommerce order's shipping address?
2. Should WordPress fetch this from user meta?
3. Does Laravel need billing address too?
4. What other fields are missing from the documented format?

## Communication Needed

**To Laravel Team**:
> "Integration test discovered field name mismatches. Can you provide the EXACT JSON format your POST /api/subscriptions/create endpoint expects? Include all required fields, optional fields, and nested objects. We'll update WordPress to match."

**Example request needed from Laravel team**:
```json
{
  // PASTE EXACT WORKING JSON HERE
}
```

---

**Status**: 🔴 Waiting for Laravel API format clarification  
**Blocked by**: Need exact field names from Laravel team  
**Next action**: Update WordPress API client once Laravel format confirmed
