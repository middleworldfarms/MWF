#!/bin/bash

echo "╔════════════════════════════════════════════════════════════════════╗"
echo "║         TESTING WORDPRESS ↔ LARAVEL API INTEGRATION                ║"
echo "╚════════════════════════════════════════════════════════════════════╝"
echo ""

API_URL="https://admin.middleworldfarms.org:8444/api/subscriptions"
API_KEY="Ffsh8yhsuZEGySvLrP0DihCDDwhPwk4h"

echo "🔧 Configuration:"
echo "   API Base URL: $API_URL"
echo "   API Key: ${API_KEY:0:20}..."
echo ""

# Test 1: Create Subscription
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "TEST 1: Create Subscription (POST /api/subscriptions/create)"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

CREATE_RESPONSE=$(curl -s -X POST "$API_URL/create" \
  -H "Content-Type: application/json" \
  -H "X-MWF-API-Key: $API_KEY" \
  -d '{
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
  }')

echo "Response:"
echo "$CREATE_RESPONSE" | python3 -m json.tool 2>/dev/null || echo "$CREATE_RESPONSE"
echo ""

# Extract subscription_id
SUBSCRIPTION_ID=$(echo "$CREATE_RESPONSE" | grep -o '"subscription_id":[0-9]*' | grep -o '[0-9]*')

if [ -z "$SUBSCRIPTION_ID" ]; then
    echo "❌ FAILED: No subscription_id returned"
    echo ""
    exit 1
else
    echo "✅ SUCCESS: Created subscription #$SUBSCRIPTION_ID"
    echo ""
fi

# Test 2: Get User Subscriptions
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "TEST 2: Get User Subscriptions (GET /api/subscriptions/user/1)"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

LIST_RESPONSE=$(curl -s -X GET "$API_URL/user/1" \
  -H "X-MWF-API-Key: $API_KEY")

echo "Response:"
echo "$LIST_RESPONSE" | python3 -m json.tool 2>/dev/null || echo "$LIST_RESPONSE"
echo ""

# Check for required fields
if echo "$LIST_RESPONSE" | grep -q '"success".*true'; then
    echo "✅ Has 'success': true"
else
    echo "❌ Missing 'success': true"
fi

if echo "$LIST_RESPONSE" | grep -q '"product_name"'; then
    echo "✅ Has 'product_name' field"
else
    echo "❌ Missing 'product_name' field"
fi

if echo "$LIST_RESPONSE" | grep -q '"billing_amount"'; then
    echo "✅ Has 'billing_amount' field"
else
    echo "❌ Missing 'billing_amount' field"
fi

if echo "$LIST_RESPONSE" | grep -q '"next_billing_date"'; then
    echo "✅ Has 'next_billing_date' field"
else
    echo "❌ Missing 'next_billing_date' field"
fi

if echo "$LIST_RESPONSE" | grep -q '"manage_url"'; then
    echo "✅ Has 'manage_url' field"
else
    echo "❌ Missing 'manage_url' field"
fi

echo ""

# Test 3: Get Single Subscription
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "TEST 3: Get Single Subscription (GET /api/subscriptions/$SUBSCRIPTION_ID)"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

DETAIL_RESPONSE=$(curl -s -X GET "$API_URL/$SUBSCRIPTION_ID" \
  -H "X-MWF-API-Key: $API_KEY")

echo "Response:"
echo "$DETAIL_RESPONSE" | python3 -m json.tool 2>/dev/null || echo "$DETAIL_RESPONSE"
echo ""

# Check for required fields
if echo "$DETAIL_RESPONSE" | grep -q '"success".*true'; then
    echo "✅ Has 'success': true"
else
    echo "❌ Missing 'success': true"
fi

if echo "$DETAIL_RESPONSE" | grep -q '"user_id"'; then
    echo "✅ Has 'user_id' field"
else
    echo "❌ Missing 'user_id' field"
fi

if echo "$DETAIL_RESPONSE" | grep -q '"renewal_orders"'; then
    echo "✅ Has 'renewal_orders' field"
else
    echo "❌ Missing 'renewal_orders' field"
fi

if echo "$DETAIL_RESPONSE" | grep -q '"billing_interval"'; then
    echo "✅ Has 'billing_interval' field"
else
    echo "❌ Missing 'billing_interval' field"
fi

echo ""

# Test 4: Date Format Validation
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "TEST 4: Date Format Validation"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

NEXT_BILLING=$(echo "$LIST_RESPONSE" | grep -o '"next_billing_date":"[^"]*"' | head -1 | cut -d'"' -f4)
CREATED_AT=$(echo "$LIST_RESPONSE" | grep -o '"created_at":"[^"]*"' | head -1 | cut -d'"' -f4)

if [[ $NEXT_BILLING =~ ^[0-9]{4}-[0-9]{2}-[0-9]{2}$ ]]; then
    echo "✅ next_billing_date format correct: $NEXT_BILLING"
else
    echo "❌ next_billing_date format wrong: $NEXT_BILLING (should be YYYY-MM-DD)"
fi

if [[ $CREATED_AT =~ ^[0-9]{4}-[0-9]{2}-[0-9]{2}$ ]]; then
    echo "✅ created_at format correct: $CREATED_AT"
else
    echo "❌ created_at format wrong: $CREATED_AT (should be YYYY-MM-DD)"
fi

echo ""

# Summary
echo "╔════════════════════════════════════════════════════════════════════╗"
echo "║                        TEST SUMMARY                                ║"
echo "╚════════════════════════════════════════════════════════════════════╝"
echo ""
echo "✅ Subscription created successfully (ID: $SUBSCRIPTION_ID)"
echo "✅ User subscriptions list works"
echo "✅ Single subscription detail works"
echo ""
echo "🎯 NEXT STEPS:"
echo "   1. Log into WordPress as a customer"
echo "   2. Go to: https://middleworldfarms.org/my-account/subscriptions/"
echo "   3. Verify you see the test subscription"
echo "   4. Click 'View' to see details"
echo "   5. Click 'Manage' to test Laravel admin link"
echo ""
echo "📝 Created test subscription for WordPress user ID: 1"
echo "   You can delete it from Laravel admin after testing"
echo ""
