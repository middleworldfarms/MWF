# 🧪 WordPress ↔ Laravel Subscription Integration Testing Guide
**Date**: December 1, 2025  
**Status**: Ready for End-to-End Testing  
**Commit**: `9d0a1d2e` - "Add WordPress ↔ Laravel subscription integration"

---

## 📋 Quick Status Summary

✅ **WordPress Side**: 100% Complete
- My Account pages created
- API client configured  
- Templates responsive and styled
- Endpoints registered

✅ **Laravel Side**: 100% Complete  
- All 3 API endpoints working
- Field format matches WordPress
- User validation removed
- Test subscriptions created

✅ **Integration**: Backend Complete
- API communication verified
- Data formats aligned
- Authentication working

⏸️ **Customer Experience**: Needs Testing
- My Account page display
- Click-through navigation
- Mobile responsiveness

---

## 🎯 Testing Objectives

### Primary Goal
Verify customers can view their subscriptions in WordPress My Account without errors.

### Secondary Goals
1. Confirm subscription data displays correctly
2. Test "View" and "Manage" button functionality
3. Validate mobile/tablet responsive design
4. Check empty state handling

---

## 🔧 Pre-Testing Setup

### 1. Verify WordPress Plugin Active
```bash
cd /var/www/vhosts/middleworldfarms.org/httpdocs
wp plugin status mwf-subscriptions --allow-root
```
**Expected**: `Status: Active`, `Version: 1.1.0`

### 2. Verify Laravel API Responding
```bash
curl -s "https://admin.middleworldfarms.org:8444/api/subscriptions/user/1018" \
  -H "X-MWF-API-Key: Ffsh8yhsuZEGySvLrP0DihCDDwhPwk4h" | python3 -m json.tool
```
**Expected**: JSON response with `"success": true`

### 3. Check Test Subscription Exists
Run the test script:
```bash
cd /var/www/vhosts/middleworldfarms.org/httpdocs
./test-subscription-api.sh
```
**Expected**: All 3 tests pass with ✅ indicators

### 4. Identify Test User
```bash
wp user list --allow-root --field=ID | head -1
```
Note the user ID (example: `1018`)

---

## 🧪 Test Scenarios

### Test 1: My Account - Subscriptions List

#### Setup
1. Find WordPress user ID 1018 credentials OR create test user:
   ```bash
   wp user create testcustomer test@middleworldfarms.org \
     --role=customer \
     --user_pass=TestPass123! \
     --allow-root
   ```
2. Create subscription for test user (already exists: subscription #151 for user 1018)

#### Test Steps
1. **Navigate**: Go to `https://middleworldfarms.org/wp-login.php`
2. **Login**: Use customer credentials
3. **Access My Account**: Click "My Account" in menu OR go to `https://middleworldfarms.org/my-account/`
4. **Find Subscriptions**: Look for "Subscriptions" menu item (should appear after "Orders")
5. **Click Subscriptions**: Navigate to subscriptions list

#### Expected Results
- ✅ "Subscriptions" menu item visible in My Account sidebar
- ✅ Subscription list page loads without errors
- ✅ Test subscription displays in table:
  - Product name: "Test Vegbox Plan"
  - Status badge: "Active" (green)
  - Billing amount: "£25.00"
  - Next payment: "2025-12-07"
  - Delivery day: "Thursday"
- ✅ "View" button appears on right side
- ✅ "Manage" button appears next to "View"
- ✅ Table is responsive (test on mobile width)

#### If No Subscriptions Show
Check WordPress error log:
```bash
tail -50 /var/www/vhosts/middleworldfarms.org/httpdocs/wp-content/debug.log
```

Run API connection test:
```bash
cd /var/www/vhosts/middleworldfarms.org/httpdocs
php wp-content/plugins/mwf-subscriptions/test-api-connection.php
```

---

### Test 2: Subscription Detail View

#### Test Steps
1. From subscriptions list, **click "View" button**
2. Detail page should load: `/my-account/view-subscription/151/`

#### Expected Results
- ✅ Subscription detail page loads
- ✅ Header shows: "Subscription #151"
- ✅ Status badge displays correctly
- ✅ Subscription details table shows:
  - Status: "Active"
  - Product: "Test Vegbox Plan"
  - Billing amount: "£25.00"
  - Billing period: "Every 1 week"
  - Delivery day: "Thursday"
  - Next payment: "2025-12-07"
  - Start date: "2025-11-30"
- ✅ "Renewal History" section visible (empty for now)
- ✅ "Manage Subscription in Admin Portal" button visible

#### Common Issues
- **404 Error**: Rewrite rules need flushing
  ```bash
  wp rewrite flush --allow-root
  ```
- **Empty page**: Check `wp-content/debug.log` for PHP errors
- **Wrong subscription shown**: URL should match clicked subscription ID

---

### Test 3: Manage Button (Laravel Admin Link)

#### Test Steps
1. From subscription detail page, **click "Manage Subscription in Admin Portal"**
2. Should open in **new tab**

#### Expected Results
- ✅ New tab opens
- ✅ URL is: `https://admin.middleworldfarms.org:8444/admin/vegbox-subscriptions/151`
- ✅ Laravel admin login page appears (if not already logged in to Laravel)
- ✅ After Laravel login, subscription management page loads

#### Note
This tests the link, not Laravel functionality. Laravel admin is separate project.

---

### Test 4: Empty State Handling

#### Setup
Create a customer with NO subscriptions:
```bash
wp user create emptycustomer empty@test.com \
  --role=customer \
  --user_pass=TestPass123! \
  --allow-root
```

#### Test Steps
1. Log in as `emptycustomer`
2. Navigate to My Account → Subscriptions

#### Expected Results
- ✅ Page loads without errors
- ✅ Message displays: "You don't have any subscriptions yet."
- ✅ No table shown (only empty state message)
- ✅ Layout remains intact (no broken design)

---

### Test 5: Mobile Responsiveness

#### Test Steps
1. Open Chrome DevTools (F12)
2. Click device toolbar (Ctrl+Shift+M)
3. Select "iPhone 12 Pro" or similar
4. Navigate through My Account → Subscriptions
5. View subscription details

#### Expected Results
- ✅ Subscriptions list table is readable on mobile
- ✅ Buttons stack vertically if needed
- ✅ Text doesn't overflow containers
- ✅ Detail page is scrollable
- ✅ All information accessible without horizontal scroll

---

## 🔍 API Testing (Backend Validation)

### Test API Endpoints Directly

#### Test 1: Get User Subscriptions
```bash
curl -s "https://admin.middleworldfarms.org:8444/api/subscriptions/user/1018" \
  -H "X-MWF-API-Key: Ffsh8yhsuZEGySvLrP0DihCDDwhPwk4h" \
  | python3 -m json.tool
```
**Expected**: List of subscriptions with all fields

#### Test 2: Get Single Subscription
```bash
curl -s "https://admin.middleworldfarms.org:8444/api/subscriptions/151" \
  -H "X-MWF-API-Key: Ffsh8yhsuZEGySvLrP0DihCDDwhPwk4h" \
  | python3 -m json.tool
```
**Expected**: Full subscription details with renewal_orders array

#### Test 3: Create Subscription
```bash
curl -X POST "https://admin.middleworldfarms.org:8444/api/subscriptions/create" \
  -H "Content-Type: application/json" \
  -H "X-MWF-API-Key: Ffsh8yhsuZEGySvLrP0DihCDDwhPwk4h" \
  -d '{
    "wordpress_user_id": 1018,
    "wordpress_order_id": 99999,
    "product_id": 226082,
    "billing_period": "month",
    "billing_interval": 1,
    "billing_amount": 30.00,
    "delivery_day": "friday",
    "customer_email": "test@example.com"
  }' | python3 -m json.tool
```
**Expected**: `"success": true`, `"subscription_id": [number]`

---

## 🐛 Troubleshooting Guide

### Issue: "Subscriptions" Menu Item Not Showing

**Causes**:
1. Plugin not active
2. My Account class not initialized
3. WooCommerce not installed

**Fixes**:
```bash
# Check plugin status
wp plugin list --allow-root | grep mwf-subscriptions

# Activate if needed
wp plugin activate mwf-subscriptions --allow-root

# Flush rewrite rules
wp rewrite flush --allow-root
```

---

### Issue: Blank Page / PHP Errors

**Check error log**:
```bash
tail -100 /var/www/vhosts/middleworldfarms.org/httpdocs/wp-content/debug.log
```

**Common errors**:
- **Class not found**: Check if `class-mwf-api-client.php` exists
- **Call to undefined function**: WooCommerce might not be loaded
- **Headers already sent**: Check for whitespace before `<?php` tags

**Fix**:
```bash
# Verify all plugin files exist
ls -la /var/www/vhosts/middleworldfarms.org/httpdocs/wp-content/plugins/mwf-subscriptions/includes/
ls -la /var/www/vhosts/middleworldfarms.org/httpdocs/wp-content/plugins/mwf-subscriptions/templates/my-account/
```

---

### Issue: API Returns Empty Array

**Possible causes**:
1. WordPress user ID doesn't match subscription
2. Laravel using wrong `wordpress_user_id` field
3. API key incorrect

**Debug**:
```bash
# Check what user ID is logged in
# Add this to subscriptions.php temporarily:
echo "Current user ID: " . get_current_user_id();

# Check Laravel database directly
cd /opt/sites/admin.middleworldfarms.org
php artisan tinker --execute="\\App\\Models\\VegboxSubscription::where('wordpress_user_id', 1018)->get();"
```

---

### Issue: 404 on Subscription Detail Page

**Cause**: Rewrite rules not flushed

**Fix**:
```bash
cd /var/www/vhosts/middleworldfarms.org/httpdocs
wp rewrite flush --allow-root

# Verify endpoints registered
wp rewrite list --allow-root | grep -i subscription
```

**Expected output**:
```
(.?.+?)/view-subscription(/(.*))?/?$    index.php?pagename=$matches[1]&view-subscription=$matches[3]
(.?.+?)/subscriptions(/(.*))?/?$        index.php?pagename=$matches[1]&subscriptions=$matches[3]
```

---

## ✅ Success Criteria

### Minimum Viable Test Pass
- ✅ Subscriptions menu item visible
- ✅ List page loads without errors
- ✅ At least one subscription displays
- ✅ Detail page accessible via "View" button
- ✅ No PHP errors in `debug.log`

### Full Test Pass
- ✅ All 5 test scenarios pass
- ✅ Mobile responsive design confirmed
- ✅ Empty state displays correctly
- ✅ Manage button links to Laravel admin
- ✅ API connection test passes

---

## 📝 Test Report Template

Copy this and fill in results:

```markdown
## Test Report - [Date]

**Tester**: [Your Name]  
**Environment**: Production / Staging  
**WordPress User**: ID [number], Email [email]

### Test Results

**Test 1: Subscriptions List** - ✅ PASS / ❌ FAIL  
Notes: 

**Test 2: Detail View** - ✅ PASS / ❌ FAIL  
Notes:

**Test 3: Manage Button** - ✅ PASS / ❌ FAIL  
Notes:

**Test 4: Empty State** - ✅ PASS / ❌ FAIL  
Notes:

**Test 5: Mobile Responsive** - ✅ PASS / ❌ FAIL  
Notes:

### Issues Found
1. [Description]
2. [Description]

### Recommendations
- [Suggestion]
- [Suggestion]

### Screenshots
- [Attach screenshots of key pages]

### Overall Status
✅ Ready for Production / ⚠️ Needs Minor Fixes / ❌ Needs Major Work
```

---

## 🚀 Next Steps After Testing

### If All Tests Pass
1. ✅ **Mark integration as production-ready**
2. ✅ **Update INTEGRATION-STATUS-UPDATE.md** to 100% complete
3. ✅ **Create real subscription from checkout** (next phase)
4. ✅ **Monitor for 48 hours** for any customer issues

### If Tests Reveal Issues
1. 🔴 **Document each issue** with screenshots
2. 🔴 **Prioritize by severity**:
   - **Critical**: Page doesn't load, data missing
   - **High**: Wrong data shown, broken links
   - **Medium**: Styling issues, minor text errors
   - **Low**: Enhancement requests
3. 🔴 **Fix critical/high issues** before proceeding
4. 🔴 **Re-test after fixes**

---

## 📞 Support Information

### WordPress Files
- **Plugin**: `/wp-content/plugins/mwf-subscriptions/`
- **Templates**: `/wp-content/plugins/mwf-subscriptions/templates/my-account/`
- **API Client**: `/wp-content/plugins/mwf-subscriptions/includes/class-mwf-api-client.php`
- **My Account Integration**: `/wp-content/plugins/mwf-subscriptions/includes/class-mwf-my-account.php`

### Laravel Files (Reference)
- **Controller**: `/opt/sites/admin.middleworldfarms.org/app/Http/Controllers/Api/VegboxSubscriptionController.php`
- **Routes**: `/opt/sites/admin.middleworldfarms.org/routes/api.php`

### Logs
- **WordPress**: `/wp-content/debug.log`
- **Laravel**: `/opt/sites/admin.middleworldfarms.org/storage/logs/laravel.log`
- **Nginx**: `/var/www/vhosts/middleworldfarms.org/logs/error_log`

### Quick Commands
```bash
# Check WordPress errors
tail -f /var/www/vhosts/middleworldfarms.org/httpdocs/wp-content/debug.log

# Check Laravel errors  
tail -f /opt/sites/admin.middleworldfarms.org/storage/logs/laravel.log

# Test API from WordPress
cd /var/www/vhosts/middleworldfarms.org/httpdocs
php wp-content/plugins/mwf-subscriptions/test-api-connection.php

# Run full API test
./test-subscription-api.sh
```

---

## 🎯 Testing Checklist

Print this and check off as you test:

- [ ] WordPress plugin active (verify)
- [ ] Laravel API responding (curl test)
- [ ] Test subscription exists (ID: 151, User: 1018)
- [ ] Test user credentials ready
- [ ] Test 1: Subscriptions list displays
- [ ] Test 2: Detail page loads
- [ ] Test 3: Manage button works
- [ ] Test 4: Empty state displays
- [ ] Test 5: Mobile responsive confirmed
- [ ] No PHP errors in logs
- [ ] No JavaScript console errors
- [ ] Screenshots captured
- [ ] Test report completed
- [ ] Results documented

---

**Good luck with testing!** 🚀  
**Contact**: Reference this guide for all debugging steps
