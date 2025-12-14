# MWF Subscriptions - Variable Product Management Feature

## Objective
Add settings page to MWF-subscriptions plugin that allows managing subscription product variations WITHOUT requiring WooCommerce Subscriptions plugin (£199/year).

## Current Situation
- 4 Variable Subscription products exist with WCS active
- Each has 6 variations (payment/delivery frequency combinations)
- Laravel handles all subscription logic (renewals, plan changes, billing)
- WCS only used for product type and admin editing - wasteful!

## Solution
Add to `/wp-content/plugins/mwf-integration/mwf-integration.php`:

### 1. Register `variable-subscription` Product Type
```php
add_filter('product_type_selector', 'mwf_add_variable_subscription_type');
function mwf_add_variable_subscription_type($types) {
    $types['variable-subscription'] = __('Variable subscription', 'mwf');
    return $types;
}

add_filter('woocommerce_product_class', 'mwf_load_variable_subscription_class', 10, 4);
function mwf_load_variable_subscription_class($classname, $product_type, $post_type, $product_id) {
    if ($product_type === 'variable-subscription') {
        require_once plugin_dir_path(__FILE__) . 'includes/class-wc-product-variable-subscription.php';
        $classname = 'WC_Product_Variable_Subscription';
    }
    return $classname;
}
```

### 2. Create Product Class
File: `/wp-content/plugins/mwf-integration/includes/class-wc-product-variable-subscription.php`

```php
<?php
class WC_Product_Variable_Subscription extends WC_Product_Variable {
    public function get_type() {
        return 'variable-subscription';
    }
    
    public function single_add_to_cart_text() {
        return $this->is_purchasable() && $this->is_in_stock() 
            ? __('Sign up now', 'mwf')
            : parent::add_to_cart_text();
    }
    
    public function add_to_cart_text() {
        return $this->is_purchasable() && $this->is_in_stock()
            ? __('Select options', 'mwf')
            : parent::add_to_cart_text();
    }
}
```

### 3. Template Override
```php
add_filter('woocommerce_locate_template', 'mwf_locate_variable_subscription_template', 10, 3);
function mwf_locate_variable_subscription_template($template, $template_name, $template_path) {
    if ($template_name === 'single-product/add-to-cart/variable-subscription.php') {
        // Use standard WooCommerce variable template
        return WC()->plugin_path() . '/templates/single-product/add-to-cart/variable.php';
    }
    return $template;
}
```

### 4. Settings Page (Admin Menu)
Add submenu under WooCommerce:

```php
add_action('admin_menu', 'mwf_subscription_products_menu');
function mwf_subscription_products_menu() {
    add_submenu_page(
        'woocommerce',
        'Subscription Products',
        'Subscription Products',
        'manage_woocommerce',
        'mwf-subscription-products',
        'mwf_subscription_products_page'
    );
}
```

### 5. Settings Page UI
Display current products and sync button:

```php
function mwf_subscription_products_page() {
    ?>
    <div class="wrap">
        <h1>MWF Subscription Products</h1>
        <p>Manage variable subscription products independently of WooCommerce Subscriptions.</p>
        
        <h2>Current Products</h2>
        <table class="wp-list-table widefat">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Variations</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $products = [226084, 226083, 226081, 226082]; // Product IDs
                foreach ($products as $product_id) {
                    $product = wc_get_product($product_id);
                    if (!$product) continue;
                    $variations = count($product->get_children());
                    ?>
                    <tr>
                        <td><?php echo $product->get_name(); ?></td>
                        <td><?php echo $variations; ?> variations</td>
                        <td><?php echo $product->get_type(); ?></td>
                        <td>
                            <a href="<?php echo admin_url('post.php?post=' . $product_id . '&action=edit'); ?>">Edit</a> |
                            <button class="button mwf-sync-product" data-product-id="<?php echo $product_id; ?>">Sync</button>
                        </td>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
        </table>
        
        <p class="submit">
            <button class="button button-primary" id="mwf-sync-all">Sync All Products</button>
        </p>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        $('.mwf-sync-product').on('click', function() {
            var productId = $(this).data('product-id');
            var button = $(this);
            
            button.prop('disabled', true).text('Syncing...');
            
            $.post(ajaxurl, {
                action: 'mwf_sync_subscription_products',
                product_id: productId,
                nonce: '<?php echo wp_create_nonce('mwf_sync_products'); ?>'
            }, function(response) {
                if (response.success) {
                    button.text('Synced!');
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    alert('Sync failed: ' + response.data.message);
                    button.prop('disabled', false).text('Sync');
                }
            });
        });
        
        $('#mwf-sync-all').on('click', function() {
            var button = $(this);
            button.prop('disabled', true).text('Syncing all products...');
            
            $.post(ajaxurl, {
                action: 'mwf_sync_all_subscription_products',
                nonce: '<?php echo wp_create_nonce('mwf_sync_products'); ?>'
            }, function(response) {
                if (response.success) {
                    alert('All products synced successfully!');
                    location.reload();
                } else {
                    alert('Sync failed');
                    button.prop('disabled', false).text('Sync All Products');
                }
            });
        });
    });
    </script>
    <?php
}
```

### 6. Sync Functions
Ensure products have correct variations and _children meta:

```php
add_action('wp_ajax_mwf_sync_subscription_products', 'mwf_sync_subscription_products');
function mwf_sync_subscription_products() {
    check_ajax_referer('mwf_sync_products', 'nonce');
    
    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    
    if ($product_id) {
        $product = wc_get_product($product_id);
        if ($product && $product instanceof WC_Product_Variable) {
            // Get variations from database
            $children = get_posts([
                'post_parent' => $product_id,
                'post_type' => 'product_variation',
                'post_status' => 'publish',
                'numberposts' => -1,
                'fields' => 'ids'
            ]);
            
            // Update _children meta
            update_post_meta($product_id, '_children', $children);
            
            // Clear caches
            wp_cache_delete('product-' . $product_id, 'products');
            delete_transient('wc_product_children_' . $product_id);
            delete_transient('wc_var_prices_' . $product_id);
            
            // Sync WooCommerce data
            WC_Product_Variable::sync($product);
            
            wp_send_json_success(['variations' => count($children)]);
        }
    }
    
    wp_send_json_error(['message' => 'Invalid product']);
}

add_action('wp_ajax_mwf_sync_all_subscription_products', 'mwf_sync_all_subscription_products');
function mwf_sync_all_subscription_products() {
    check_ajax_referer('mwf_sync_products', 'nonce');
    
    $products = [226084, 226083, 226081, 226082];
    $synced = 0;
    
    foreach ($products as $product_id) {
        $product = wc_get_product($product_id);
        if ($product && $product instanceof WC_Product_Variable) {
            $children = get_posts([
                'post_parent' => $product_id,
                'post_type' => 'product_variation',
                'post_status' => 'publish',
                'numberposts' => -1,
                'fields' => 'ids'
            ]);
            
            update_post_meta($product_id, '_children', $children);
            
            wp_cache_delete('product-' . $product_id, 'products');
            delete_transient('wc_product_children_' . $product_id);
            delete_transient('wc_var_prices_' . $product_id);
            
            WC_Product_Variable::sync($product);
            $synced++;
        }
    }
    
    wp_send_json_success(['synced' => $synced]);
}
```

### 7. Activation Hook (One-time setup)
```php
register_activation_hook(__FILE__, 'mwf_subscription_products_activate');
function mwf_subscription_products_activate() {
    // Ensure all products have correct _children meta on activation
    $products = [226084, 226083, 226081, 226082];
    
    foreach ($products as $product_id) {
        $children = get_posts([
            'post_parent' => $product_id,
            'post_type' => 'product_variation',
            'post_status' => 'publish',
            'numberposts' => -1,
            'fields' => 'ids'
        ]);
        
        update_post_meta($product_id, '_children', $children);
    }
}
```

## Implementation Steps

1. **Add code to mwf-integration plugin**
   - Add functions 1-7 to main plugin file
   - Create `includes/class-wc-product-variable-subscription.php`

2. **Test with WCS active**
   - Verify new menu appears under WooCommerce
   - Test sync functions
   - Verify products still work

3. **Deactivate WCS**
   - Check products still display
   - Test variations dropdown
   - Test add to cart
   - Test checkout flow

4. **Remove WCS**
   - If everything works, delete WCS plugin
   - Save £199/year

## Testing Checklist
- [ ] Admin menu appears under WooCommerce > Subscription Products
- [ ] Settings page shows all 4 products with 6 variations each
- [ ] Sync button works for individual products
- [ ] Sync All button works
- [ ] Deactivate WooCommerce Subscriptions
- [ ] Variable-subscription products still display on frontend
- [ ] Variations dropdown works on product pages
- [ ] Prices update when selecting variations
- [ ] "Add to basket" button works
- [ ] Checkout creates Laravel subscription correctly
- [ ] Product sync repairs broken products
- [ ] Admin can edit products in WooCommerce admin

## Benefits
✅ Save £199/year (WCS license)  
✅ Full control over product structure  
✅ Easy to maintain/repair products via sync button  
✅ No dependency on paid plugin  
✅ Works with existing Laravel subscription system  
✅ Standard WooCommerce variable product UX  
✅ Products work exactly like they did with WCS  

## Current Product IDs
- **Single Person**: 226084 (6 variations)
- **Couple's**: 226083 (6 variations)
- **Small Family**: 226081 (6 variations)
- **Large Family**: 226082 (6 variations)

All products have these 6 variations:
1. Weekly payments • Weekly deliveries
2. Fortnightly payments • Fortnightly deliveries
3. Monthly payments • Weekly deliveries
4. Monthly payments • Fortnightly deliveries
5. Annual payments • Weekly deliveries
6. Annual payments • Fortnightly deliveries

## Troubleshooting

**If variations don't show after WCS removal:**
1. Go to WooCommerce > Subscription Products
2. Click "Sync All Products"
3. Clear all caches (WordPress, browser, CDN)
4. Check product page again

**If sync doesn't work:**
- Check product_variation posts exist in database
- Verify _children meta is populated
- Check WooCommerce logs for errors
- Ensure product type is 'variable-subscription'

**If products become unpurchasable:**
- Sync the product
- Check variation stock status
- Verify at least one variation has a price
- Check product is published

## Notes
- The custom product class extends `WC_Product_Variable`, so it inherits all standard variable product functionality
- Template override uses WooCommerce's native variable.php template
- Sync function repairs the `_children` meta which WooCommerce uses to track variations
- This approach is cleaner than maintaining 27 separate simple products
