# Product Variation Cleanup Guide

## Problem

WooCommerce stores deleted/trashed variations in `wp_posts` with `post_status = 'trash'`. These variations:
- Don't appear in the WooCommerce product editor UI
- Still exist in the database
- Get synced to Laravel, inflating counts
- Cause inconsistencies between WooCommerce and Laravel

## Current Issue

**Trashed Variations Found:**
- 226092, 227909, 227910, 228035, 226095, 226085

**Issues:**
- Some missing required fields (payment option, price)
- Inflate total counts in Laravel sync
- Cause confusion about actual published variations

---

## Solution 1: Clean Up Existing Trash (Recommended)

### Run the Cleanup Script

```bash
cd /var/www/vhosts/middleworldfarms.org/httpdocs
./cleanup-variations.sh
```

This script will:
1. Show all trashed variations
2. Ask for confirmation
3. Permanently delete them from the database
4. Verify cleanup was successful

### Manual Cleanup (Alternative)

```bash
# List trashed variations
wp post list --post_type=product_variation --post_status=trash --format=table --allow-root

# Delete specific variation permanently
wp post delete 226092 --force --allow-root

# Or delete all trashed variations at once
wp post delete $(wp post list --post_type=product_variation --post_status=trash --format=ids --allow-root) --force --allow-root
```

---

## Solution 2: Update Laravel Sync to Filter Trash

If you want to keep trashed variations but exclude them from sync:

### Update Laravel Sync Command

Modify your sync command to only pull published variations:

```php
// In your Laravel sync command/service

// OLD (pulls everything including trash):
$variations = DB::connection('wordpress')
    ->table('wp_posts')
    ->where('post_type', 'product_variation')
    ->get();

// NEW (only published):
$variations = DB::connection('wordpress')
    ->table('wp_posts')
    ->where('post_type', 'product_variation')
    ->where('post_status', 'publish')
    ->get();
```

### Add Status Filter to Existing Query

If you have an existing sync method, add the status filter:

```php
->where('post_status', 'publish')
```

---

## Preventing Future Issues

### 1. Always Permanently Delete Variations

In WooCommerce, when deleting variations:
- Click "Move to Trash" 
- Then go to WooCommerce > Status > Tools
- Run "Delete all WooCommerce transients"
- Or manually empty trash from Products > Variations (if visible)

### 2. Laravel Sync Best Practices

Always filter by `post_status = 'publish'` in your sync queries:

```php
// Product variations sync
$publishedVariations = DB::connection('wordpress')
    ->table('wp_posts')
    ->where('post_type', 'product_variation')
    ->where('post_status', 'publish')
    ->get();

// Products sync
$publishedProducts = DB::connection('wordpress')
    ->table('wp_posts')
    ->where('post_type', 'product')
    ->whereIn('post_status', ['publish', 'draft']) // Include drafts if needed
    ->get();
```

### 3. Add Validation to Sync Command

Check for required fields before syncing:

```php
foreach ($variations as $variation) {
    // Get meta
    $price = $this->getVariationMeta($variation->ID, '_price');
    $paymentOption = $this->getVariationMeta($variation->ID, 'payment-option');
    
    // Skip invalid variations
    if (empty($price) || empty($paymentOption)) {
        $this->warn("Skipping variation {$variation->ID}: Missing required fields");
        continue;
    }
    
    // Proceed with sync...
}
```

---

## Verification Steps

After cleanup or updating sync:

### 1. Check WooCommerce

```bash
# Count published variations
wp post list --post_type=product_variation --post_status=publish --format=count --allow-root

# Verify no trash remains
wp post list --post_type=product_variation --post_status=trash --format=count --allow-root
```

### 2. Check Laravel

Re-run your sync command and verify:
- Variation counts match WooCommerce published count
- No incomplete variations are synced
- Plan counts are accurate

### 3. Database Check

```sql
-- Count variations by status
SELECT post_status, COUNT(*) as count
FROM wp_posts
WHERE post_type = 'product_variation'
GROUP BY post_status;

-- Should show only 'publish' status after cleanup
```

---

## Recommended Approach

**For immediate fix:**
1. ✅ Run `cleanup-variations.sh` to permanently delete trash
2. ✅ Update Laravel sync to filter `post_status = 'publish'`
3. ✅ Re-run sync command to update counts

**For long-term:**
- Always filter by `post_status = 'publish'` in Laravel syncs
- Periodically run cleanup script to remove accumulated trash
- Add validation for required fields before syncing

---

## Automation (Optional)

Add to cron for automatic cleanup:

```bash
# Add to crontab
# Clean up trashed variations weekly
0 2 * * 0 /var/www/vhosts/middleworldfarms.org/httpdocs/cleanup-variations.sh

# Or create a WP-CLI command in your plugin
wp variation cleanup-trash --dry-run
wp variation cleanup-trash --force
```

---

## Impact Summary

**Before Cleanup:**
- Total variations in DB: ~25 (including 6 trashed)
- Published variations: ~19
- Trashed variations: 6
- Laravel seeing: 25 (inflated)

**After Cleanup:**
- Total variations in DB: ~19
- Published variations: ~19
- Trashed variations: 0
- Laravel seeing: 19 (accurate)

---

## Quick Commands

```bash
# Check status
wp post list --post_type=product_variation --post_status=any --format=table --allow-root --fields=ID,post_status,post_title

# Run cleanup
./cleanup-variations.sh

# Verify
wp post list --post_type=product_variation --post_status=trash --format=count --allow-root
```

---

**Ready to clean up?** Run the script and then update your Laravel sync to prevent future issues!
