<?php
// Enqueue parent theme styles
function divi_child_enqueue_styles() {
    wp_enqueue_style('parent-style', get_template_directory_uri() . '/style.css');
}
add_action('wp_enqueue_scripts', 'divi_child_enqueue_styles');

// ====================================================================================
// EMAIL VALIDATION SYSTEM - PREVENT TYPOS AT CHECKOUT
// ====================================================================================
/**
 * Enqueue email validation script on checkout and account pages
 */
add_action('wp_enqueue_scripts', 'mwf_enqueue_email_validation');
function mwf_enqueue_email_validation() {
    // Only load on checkout, account, and registration pages
    if (is_checkout() || is_account_page() || is_page('register')) {
        wp_enqueue_script(
            'mwf-email-validation',
            get_stylesheet_directory_uri() . '/assets/js/email-validation.js',
            array('jquery'),
            '1.0.0',
            true
        );
    }
}

/**
 * Server-side email validation for extra security
 */
add_action('woocommerce_checkout_process', 'mwf_validate_checkout_email');
function mwf_validate_checkout_email() {
    $email = sanitize_email($_POST['billing_email']);
    
    if (empty($email)) {
        wc_add_notice(__('Email address is required.'), 'error');
        return;
    }
    
    // Check for common typos
    $typo_corrections = array(
        'hotmai.co.uk' => 'hotmail.co.uk',
        'hotmial.co.uk' => 'hotmail.co.uk',
        'hotmail.co.k' => 'hotmail.co.uk',
        'gmai.com' => 'gmail.com',
        'gmial.com' => 'gmail.com',
        'gmail.co.uk' => 'gmail.com',
        'yahoo.co.k' => 'yahoo.co.uk'
    );
    
    foreach ($typo_corrections as $typo => $correction) {
        if (strpos($email, $typo) !== false) {
            $suggested = str_replace($typo, $correction, $email);
            wc_add_notice(
                sprintf(__('Did you mean %s? Please check your email address.'), $suggested),
                'error'
            );
            return;
        }
    }
    
    // Check for suspicious patterns
    if (preg_match('/\.\.|\s|@\.|\.\@|@.*@|^\.|\.$|@$|^@/', $email)) {
        wc_add_notice(__('Please check your email address format.'), 'error');
        return;
    }
}

/**
 * Validate email during user registration
 */
add_action('woocommerce_register_post', 'mwf_validate_registration_email', 10, 3);
function mwf_validate_registration_email($username, $email, $errors) {
    // Same validation as checkout
    if (!empty($email)) {
        $typo_corrections = array(
            'hotmai.co.uk' => 'hotmail.co.uk',
            'hotmial.co.uk' => 'hotmail.co.uk',
            'hotmail.co.k' => 'hotmail.co.uk',
            'gmai.com' => 'gmail.com',
            'gmial.com' => 'gmail.com',
            'gmail.co.uk' => 'gmail.com'
        );
        
        foreach ($typo_corrections as $typo => $correction) {
            if (strpos($email, $typo) !== false) {
                $suggested = str_replace($typo, $correction, $email);
                $errors->add(
                    'registration-error-invalid-email',
                    sprintf(__('Did you mean %s? Please check your email address.'), $suggested)
                );
                break;
            }
        }
    }
}

/**
 * Log email validation events for monitoring
 */
add_action('wp_ajax_mwf_log_email_correction', 'mwf_log_email_correction');
add_action('wp_ajax_nopriv_mwf_log_email_correction', 'mwf_log_email_correction');
function mwf_log_email_correction() {
    $original = sanitize_email($_POST['original']);
    $corrected = sanitize_email($_POST['corrected']);
    
    if ($original && $corrected) {
        error_log("MWF Email Correction: {$original} → {$corrected}");
        
        // Optionally store in database for analytics
        global $wpdb;
        $wpdb->insert(
            $wpdb->prefix . 'mwf_email_corrections',
            array(
                'original_email' => $original,
                'corrected_email' => $corrected,
                'correction_date' => current_time('mysql'),
                'user_ip' => $_SERVER['REMOTE_ADDR']
            ),
            array('%s', '%s', '%s', '%s')
        );
    }
    
    wp_die();
}

/**
 * Create table for email correction analytics
 */
register_activation_hook(__FILE__, 'mwf_create_email_corrections_table');
function mwf_create_email_corrections_table() {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'mwf_email_corrections';
    
    $charset_collate = $wpdb->get_charset_collate();
    
    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        original_email varchar(255) NOT NULL,
        corrected_email varchar(255) NOT NULL,
        correction_date datetime DEFAULT CURRENT_TIMESTAMP,
        user_ip varchar(45),
        PRIMARY KEY (id)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

// Debugging for translation loading
if (defined('MWF_DEBUG') && constant('MWF_DEBUG')) {
    add_filter('load_textdomain', function ($override, $domain, $mofile = null) {
        if ($domain === 'woocommerce' || $domain === 'advanced-coupons-for-woocommerce-free') {
            error_log("Translation loaded too early for: " . $domain);
            error_log(print_r(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10), true));
        }
        return $override;
    }, 10, 3);
}

// Change WooCommerce subscription sign-up fee text
function change_signup_fee_text($translated_text, $text, $domain) {
    if ($domain == 'woocommerce-subscriptions' && $text == 'Sign-up fee') {
        return 'Deposit';
    }
    return $translated_text;
}
add_filter('gettext', 'change_signup_fee_text', 20, 3);

// Prevent multiple Google Maps API calls
add_action('wp_enqueue_scripts', function () {
    wp_dequeue_script('address-field-autocomplete-for-woocommerce');
}, 20);

// ====================================================================================
// EMAIL FUNCTIONALITY MOVED TO PLUGIN
// ====================================================================================
/**
 * Email functionality has been moved to the MWF Email Automation plugin
 * for better organization and maintainability.
 *
 * Plugin location: /wp-content/plugins/mwf-email-automation/
 * Admin menu: WordPress Admin > Email Automation
 *
 * The plugin includes:
 * - Permission monitoring and auto-repair
 * - Subscription renewal reminders
 * - Professional email templates
 * - Comprehensive logging and analytics
 * - Admin dashboard for configuration
 */

// Define constants for IDs
define('MWF_VEGBOX_FORM_ID', 226922);
define('MWF_VEGBOX_CATEGORY_ID', 70);

// ====================================================================================
// SUBSCRIPTION PAYMENT LOGIC - ALWAYS CHECK FUNDS FIRST
// ====================================================================================
/**
 * Force funds check for ALL subscription renewals before any card charges
 * This ensures that even customers set up for card payment will use their account funds first
 */
add_action('woocommerce_scheduled_subscription_payment', 'mwf_force_funds_check_before_payment', 1, 1);
function mwf_force_funds_check_before_payment($subscription_id) {
    $subscription = wcs_get_subscription($subscription_id);
    if (!$subscription) {
        return;
    }

    $customer_id = $subscription->get_customer_id();
    $renewal_amount = floatval($subscription->get_total());
    $available_funds = mwf_get_user_available_funds($customer_id);

    // ALWAYS check funds first, regardless of payment method set
    if ($available_funds >= $renewal_amount) {
        // Sufficient funds - deduct and complete payment
        mwf_deduct_user_funds($customer_id, $renewal_amount);
        $subscription->payment_complete();
        
        // Log the successful funds payment
        error_log("MWF: Payment completed using account funds for subscription {$subscription_id}. Customer {$customer_id} paid £{$renewal_amount} from £{$available_funds} available.");
        
        // Trigger action for email plugin to clear any pending reminders
        do_action('mwf_funds_payment_complete', $subscription_id, $customer_id, $renewal_amount);
        
        // Stop other payment processing - this payment is complete
        remove_all_actions('woocommerce_scheduled_subscription_payment');
        return;
    }
    
    // Partial funds available - use what we have and let card charge the rest
    if ($available_funds > 0) {
        mwf_deduct_user_funds($customer_id, $available_funds);
        $shortfall = $renewal_amount - $available_funds;
        
        error_log("MWF: Partial payment using account funds for subscription {$subscription_id}. Customer {$customer_id} used £{$available_funds} from account, £{$shortfall} to be charged to card.");
        
        // Trigger action for email plugin (partial payment might still need notifications)
        do_action('mwf_partial_funds_payment', $subscription_id, $customer_id, $available_funds, $shortfall);
    }
}

/**
 * Get user's available account funds
 */
function mwf_get_user_available_funds($user_id) {
    // Try to use the proper Account Funds plugin API
    if (class_exists('WC_Account_Funds') && method_exists('WC_Account_Funds', 'get_user_funds')) {
        return floatval(WC_Account_Funds::get_user_funds($user_id));
    }
    
    // Try alternative Account Funds API methods
    if (function_exists('wc_account_funds_get_user_funds')) {
        return floatval(wc_account_funds_get_user_funds($user_id));
    }
    
    // Fallback to direct meta access (legacy)
    return floatval(get_user_meta($user_id, 'account_funds', true));
}

/**
 * Deduct funds from user account
 */
function mwf_deduct_user_funds($user_id, $amount) {
    // Try to use the proper Account Funds plugin API
    if (function_exists('wc_account_funds_add_user_funds')) {
        // Use negative amount to deduct
        return wc_account_funds_add_user_funds($user_id, -$amount);
    }
    
    // Fallback to direct meta access (legacy)
    $current_funds = mwf_get_user_available_funds($user_id);
    $new_funds = max(0, $current_funds - $amount);
    return update_user_meta($user_id, 'account_funds', $new_funds);
}

// ====================================================================================
// EXISTING CODE BELOW
// ====================================================================================

// Save WPForms submission ID to WooCommerce session
add_action('wpforms_process_complete', function ($fields, $entry, $form_data) {
    if (!function_exists('WC')) {
        return;
    }
    if ($form_data['id'] == MWF_VEGBOX_FORM_ID) {
        if (is_user_logged_in()) {
            WC()->session->set('wpforms_entry_id_' . get_current_user_id(), $entry['entry_id']);
        } else {
            // Handle guest users (maybe store in session only)
            WC()->session->set('wpforms_entry_id_guest', $entry['entry_id']);
        }
    }
}, 10, 3);

// Save WPForms data to WooCommerce order
add_action('woocommerce_checkout_update_order_meta', function ($order_id) {
    if (!function_exists('WC')) {
        return;
    }
    $entry_id = WC()->session->get('wpforms_entry_id_' . get_current_user_id());
    if (!empty($entry_id)) {
        update_post_meta($order_id, '_wpforms_entry_id', $entry_id);
    }
});

// Disable express checkout buttons for specific products
add_filter('woocommerce_product_supports', 'mwf_remove_express_checkout_for_vegbox', 10, 3);
function mwf_remove_express_checkout_for_vegbox($supports, $feature, $product) {
    if (in_array($feature, ['stripe_checkout', 'express_checkout', 'apple_pay'], true)) {
        if (has_term(MWF_VEGBOX_CATEGORY_ID, 'product_cat', $product->get_id())) {
            return false;
        }
        // Check for WooCommerce Name Your Price plugin
        if (function_exists('wc_nyp_is_nyp') && call_user_func('wc_nyp_is_nyp', $product)) {
            return false;
        }
        if ($product->is_type('subscription') || $product->is_type('variable-subscription')) {
            return false;
        }
    }
    return $supports;
}

// Fix JavaScript module error for Google Analytics Premium
add_filter('script_loader_tag', 'mwf_fix_ga_premium_script', 10, 3);
function mwf_fix_ga_premium_script($tag, $handle, $src) {
    if (strpos($src, 'google-analytics-premium') !== false && strpos($src, 'frontend.min.js') !== false) {
        return str_replace('<script', '<script type="module"', $tag);
    }
    if (strpos($src, 'maps.googleapis.com') !== false && strpos($tag, 'async') === false) {
        return str_replace('<script', '<script async defer', $tag);
    }
    return $tag;
}

// Fix social login icon paths
add_filter('woocommerce_social_login_icon_path', 'mwf_fix_social_login_icons', 10, 3);
function mwf_fix_social_login_icons($icon_url, $provider, $size) {
    return str_replace('staging.middleworld.farm', 'middleworldfarms.org', $icon_url);
}

/**
 * Product Category Search for WooCommerce
 * Creates a shortcode [product_category_search] to display a search form with category filtering
 */

// Add the category search shortcode
add_shortcode('product_category_search', 'mwf_product_category_search');

function mwf_product_category_search($atts) {
    // Extract shortcode attributes
    $atts = shortcode_atts(array(
        'placeholder' => 'Search products...',
        'button_text' => 'Search',
        'show_count' => 'yes',
        'dropdown_label' => 'Select Category',
        'include_children' => 'yes',
    ), $atts);
    
    // Get all product categories
    $args = array(
        'taxonomy' => 'product_cat',
        'orderby' => 'name',
        'show_count' => ($atts['show_count'] === 'yes') ? 1 : 0,
        'pad_counts' => true,
        'hierarchical' => true,
        'hide_empty' => true,
    );
    
    $all_categories = get_terms($args);
    
    // Start output buffering
    ob_start();
    
    // Get current search and category values
    $current_search = isset($_GET['s']) ? esc_attr($_GET['s']) : '';
    $current_category = isset($_GET['product_cat']) ? esc_attr($_GET['product_cat']) : '';
    
    // Generate the search form
    ?>
    <div class="mwf-product-search-container">
        <form role="search" method="get" class="mwf-product-search" action="<?php echo esc_url(home_url('/')); ?>">
            <div class="mwf-search-fields">
                <div class="mwf-search-input">
                    <input type="search" 
                           name="s" 
                           value="<?php echo $current_search; ?>" 
                           placeholder="<?php echo esc_attr($atts['placeholder']); ?>" />
                    <input type="hidden" name="post_type" value="product" />
                </div>
                
                <div class="mwf-category-dropdown">
                    <select name="product_cat" id="product-category-select">
                        <option value=""><?php echo esc_html($atts['dropdown_label']); ?></option>
                        <?php
                        if (!is_wp_error($all_categories) && !empty($all_categories)) {
                            foreach ($all_categories as $category) {
                                // Skip the uncategorized category
                                if ($category->slug === 'uncategorized') {
                                    continue;
                                }
                                
                                echo '<option value="' . esc_attr($category->slug) . '"' . 
                                     selected($current_category, $category->slug, false) . '>' . 
                                     esc_html($category->name) . 
                                     (($atts['show_count'] === 'yes') ? ' (' . $category->count . ')' : '') . 
                                     '</option>';
                            }
                        }
                        ?>
                    </select>
                </div>
                
                <div class="mwf-search-button">
                    <button type="submit"><?php echo esc_html($atts['button_text']); ?></button>
                </div>
            </div>
        </form>
    </div>
    
    <style>
        .mwf-product-search-container {
            margin: 20px 0;
            width: 100%;
        }
        .mwf-search-fields {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
        }
        .mwf-search-input {
            flex: 3;
            min-width: 200px;
        }
        .mwf-search-input input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .mwf-category-dropdown {
            flex: 2;
            min-width: 180px;
        }
        .mwf-category-dropdown select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background-color: #fff;
        }
        .mwf-search-button button {
            padding: 10px 20px;
            background-color: #2ea3f2; /* Divi primary color */
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 500;
        }
        .mwf-search-button button:hover {
            background-color: #0c71c3;
        }
        
        /* Mobile responsiveness */
        @media (max-width: 768px) {
            .mwf-search-fields {
                flex-direction: column;
            }
            .mwf-search-input, .mwf-category-dropdown {
                width: 100%;
            }
            .mwf-search-button {
                width: 100%;
                margin-top: 10px;
            }
            .mwf-search-button button {
                width: 100%;
            }
        }
    </style>
    <?php
    
    // Return the buffered content
    return ob_get_clean();
}

/**
 * Handle advanced category filtering on WooCommerce shop and search pages
 */
add_action('woocommerce_product_query', 'mwf_filter_products_by_category');

function mwf_filter_products_by_category($query) {
    if (!is_admin() && $query->is_main_query() && 
        (is_shop() || is_product_category() || is_search())) {
        
        if (isset($_GET['product_cat']) && !empty($_GET['product_cat'])) {
            $category_slug = sanitize_text_field($_GET['product_cat']);
            
            // Get existing tax query
            $tax_query = $query->get('tax_query');
            if (!is_array($tax_query)) {
                $tax_query = array();
            }
            
            // Add our category filter
            $tax_query[] = array(
                'taxonomy' => 'product_cat',
                'field' => 'slug',
                'terms' => $category_slug,
                'operator' => 'IN'
            );
            
            $query->set('tax_query', $tax_query);
        }
    }
}

/**
 * Add AJAX product search by category 
 */
add_action('wp_ajax_product_category_search', 'mwf_ajax_product_category_search');
add_action('wp_ajax_nopriv_product_category_search', 'mwf_ajax_product_category_search');

function mwf_ajax_product_category_search() {
    // Security check
    check_ajax_referer('product_search_nonce', 'security');
    
    $search_term = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';
    $category = isset($_POST['category']) ? sanitize_text_field($_POST['category']) : '';
    
    $args = array(
        'post_type' => 'product',
        'post_status' => 'publish',
        'posts_per_page' => 10,
        's' => $search_term,
    );
    
    // Add category filter if provided
    if (!empty($category)) {
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'product_cat',
                'field' => 'slug',
                'terms' => $category
            )
        );
    }
    
    $products = new WP_Query($args);
    
    $response = array();
    
    if ($products->have_posts()) {
        while ($products->have_posts()) {
            $products->the_post();
            $product_id = get_the_ID();
            $product = wc_get_product($product_id);
            
            $response[] = array(
                'id' => $product_id,
                'title' => get_the_title(),
                'permalink' => get_permalink(),
                'price' => $product->get_price_html(),
                'image' => get_the_post_thumbnail_url($product_id, 'thumbnail') ?: wc_placeholder_img_src(),
            );
        }
    }
    
    wp_reset_postdata();
    wp_send_json_success($response);
}

/**
 * Enqueue scripts for AJAX search functionality
 */
add_action('wp_enqueue_scripts', 'mwf_enqueue_category_search_scripts');

function mwf_enqueue_category_search_scripts() {
    wp_enqueue_script(
        'mwf-category-search', 
        get_stylesheet_directory_uri() . '/JS/category-search.js', 
        array('jquery'), 
        '1.0', 
        true
    );
    
    wp_localize_script(
        'mwf-category-search', 
        'mwf_search', 
        array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('product_search_nonce'),
            'no_results' => __('No products found', 'mwf')
        )
    );
}

/**
 * Remove WooCommerce product search widget
 */
function mwf_remove_product_search_widget() {
    // For standard WordPress sidebars
    unregister_widget('WC_Widget_Product_Search');
    
    // For Divi-specific widgets
    add_filter('et_builder_widgets_init', function() {
        unregister_widget('WC_Widget_Product_Search');
    });
    
    // For WooCommerce sidebar
    add_filter('woocommerce_before_widget', function($widget_output, $widget) {
        if (is_a($widget, 'WC_Widget_Product_Search')) {
            return '';
        }
        return $widget_output;
    }, 10, 2);
}
add_action('widgets_init', 'mwf_remove_product_search_widget', 99);

/**
 * User Switching Functionality
 * 
 * Note: User switching functionality has been moved to the MWF Integration plugin
 * for better organization and to provide REST API endpoints for the Laravel admin suite.
 * 
 * The plugin provides:
 * - REST API endpoints for searching users
 * - User switching with secure token-based authentication  
 * - Frontend preview mode with visual indicators
 * - Integration with admin.middleworldfarm.org
 * 
 * API Documentation: /wp-content/plugins/mwf-integration/USER_SWITCHING_API.md
 */

// BEGIN MWF CUSTOM CODE (moved from your-theme/functions.php)
// Force enable payment gateways
add_filter('woocommerce_payment_gateways', function($gateways) {
    error_log('Available payment gateways: ' . print_r(array_keys($gateways), true));
    $default_gateways = array('bacs', 'cheque', 'cod');
    foreach ($default_gateways as $gateway_id) {
        if (isset($gateways[$gateway_id])) {
            update_option('woocommerce_' . $gateway_id . '_settings', array(
                'enabled' => 'yes',
                'title' => ucfirst($gateway_id),
                'description' => 'Pay via ' . ucfirst($gateway_id),
            ));
        }
    }
    return $gateways;
}, 99);

// Generate the admin switch key for external use
function mwf_get_admin_switch_key($user_id, $redirect_to) {
    $secret = 'mwf_admin_switch_2025_secret_key';
    return hash('sha256', $user_id . $redirect_to . $secret);
}

// Add admin endpoint to generate switch URLs
add_action('wp_ajax_mwf_generate_switch_url', 'mwf_generate_switch_url');

function mwf_generate_switch_url() {
    // Only allow admin access
    if (!current_user_can('manage_options')) {
        wp_die('Insufficient permissions', 'Permission Error', array('response' => 403));
    }

    $user_id = intval($_GET['user_id'] ?? $_POST['user_id'] ?? 0);
    $redirect_to = sanitize_text_field($_GET['redirect_to'] ?? $_POST['redirect_to'] ?? '/my-account/');

    if (empty($user_id)) {
        wp_send_json_error('User ID is required');
        return;
    }

    // Verify user exists
    $user = get_user_by('ID', $user_id);
    if (!$user) {
        wp_send_json_error('User not found');
        return;
    }
    
    // Generate the switch URL
    $admin_key = mwf_get_admin_switch_key($user_id, $redirect_to);
    $switch_url = add_query_arg(array(
        'action' => 'mwf_admin_switch_user',
        'user_id' => $user_id,
        'redirect_to' => $redirect_to,
        'admin_key' => $admin_key
    ), admin_url('admin-ajax.php'));

    wp_send_json_success(array(
        'switch_url' => $switch_url,
        'user_name' => $user->display_name ?: $user->user_login,
        'redirect_to' => $redirect_to
    ));
}

// Handle admin user switching via AJAX
add_action('wp_ajax_mwf_admin_switch_user', 'mwf_handle_admin_switch_user');
add_action('wp_ajax_nopriv_mwf_admin_switch_user', 'mwf_handle_admin_switch_user');

function mwf_handle_admin_switch_user() {
    // Get parameters
    $user_id = intval($_GET['user_id'] ?? 0);
    $redirect_to = sanitize_text_field($_GET['redirect_to'] ?? '/my-account/');
    $admin_key = sanitize_text_field($_GET['admin_key'] ?? '');
    
    // Debug logging
    error_log("MWF User Switch Debug: user_id={$user_id}, redirect_to={$redirect_to}, admin_key={$admin_key}");
    
    // Verify admin key
    $expected_key = mwf_get_admin_switch_key($user_id, $redirect_to);
    if (!hash_equals($expected_key, $admin_key)) {
        error_log("MWF User Switch: Invalid admin key. Expected: {$expected_key}, Got: {$admin_key}");
        wp_die('Invalid admin key', 'Authentication Error', array('response' => 403));
    }
    
    // Validate user
    $user = get_userdata($user_id);
    if (!$user) {
        error_log("MWF User Switch: User not found: {$user_id}");
        wp_die('User not found', 'User Error', array('response' => 404));
    }
    
    error_log("MWF User Switch: Attempting to switch to user: {$user->user_login} (ID: {$user_id})");
    
    // Method 1: Completely destroy current session
    wp_destroy_current_session();
    wp_clear_auth_cookie();
    
    // Method 2: Force new user login with extended session
    wp_set_current_user($user_id, $user->user_login);
    
    // Method 3: Set auth cookie with remember me and extended time
    $remember = true;
    $secure = is_ssl();
    $expiration = time() + (14 * DAY_IN_SECONDS); // 2 weeks
    
    wp_set_auth_cookie($user_id, $remember, $secure, $expiration);
    
    error_log("MWF User Switch: Auth cookie set for user: {$user->user_login} with expiration: " . date('Y-m-d H:i:s', $expiration));
    
    // Method 4: Set additional verification cookies
    setcookie('mwf_switched_user', $user->user_login, time() + 3600, '/', '', $secure, false);
    setcookie('mwf_switch_timestamp', time(), time() + 3600, '/', '', $secure, false);
    
    // Method 5: Force session regeneration
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_regenerate_id(true);
    }
    
    error_log("MWF User Switch: All authentication methods applied for user: {$user->user_login}");
    
    error_log("MWF User Switch: Switch completed, redirecting to: {$redirect_to}");
    
    // Redirect with cache busting parameters
    $redirect_url = add_query_arg([
        'switched' => 1,
        'user' => $user->user_login,
        '_t' => time(),
        'mwf_switch' => 'success'
    ], home_url($redirect_to));
    
    error_log("MWF User Switch: Final redirect URL: {$redirect_url}");
    
    wp_redirect($redirect_url);
    exit;
}

// Test AJAX handler for debugging
add_action('wp_ajax_test_mwf', 'test_mwf_ajax');
add_action('wp_ajax_nopriv_test_mwf', 'test_mwf_ajax');

function test_mwf_ajax() {
    error_log('MWF Test AJAX: Handler called successfully');
    wp_send_json(['status' => 'success', 'message' => 'AJAX is working']);
}

// Add new endpoint to generate proper User Switching plugin URLs
add_action('wp_ajax_mwf_generate_plugin_switch_url', 'mwf_generate_plugin_switch_url');
add_action('wp_ajax_nopriv_mwf_generate_plugin_switch_url', 'mwf_generate_plugin_switch_url');

function mwf_generate_plugin_switch_url() {
    // Add initial debug log
    error_log("MWF Plugin Switch: Function called with parameters: " . print_r($_GET, true));
    
    // Get parameters
    $user_id = intval($_GET['user_id'] ?? 0);
    $redirect_to = sanitize_text_field($_GET['redirect_to'] ?? '/my-account/');
    $admin_key = sanitize_text_field($_GET['admin_key'] ?? '');
    
    error_log("MWF Plugin Switch: Parsed - user_id: $user_id, redirect_to: $redirect_to, admin_key: $admin_key");
    
    // Verify admin key
    $expected_key = hash('sha256', $user_id . $redirect_to . 'mwf_admin_switch_2025_secret_key');
    error_log("MWF Plugin Switch: Expected key: $expected_key, Got key: $admin_key");
    
    if (!hash_equals($expected_key, $admin_key)) {
        error_log("MWF Plugin Switch: Invalid admin key");
        wp_die('Invalid admin key', 'Authentication Error', array('response' => 403));
    }
    
    // Validate user
    $user = get_userdata($user_id);
    if (!$user) {
        error_log("MWF Plugin Switch: User not found: {$user_id}");
        wp_die('User not found', 'User Error', array('response' => 404));
    }
    
    error_log("MWF Plugin Switch: Creating direct auto-login for user: {$user->user_login} (ID: {$user_id})");
    
    // Create a secure auto-login token
    $auto_login_token = wp_generate_password(32, false);
    $token_expiry = time() + 300; // 5 minutes
    
    // Store the token in database temporarily
    $token_data = array(
        'user_id' => $user_id,
        'redirect_to' => $redirect_to,
        'created' => time(),
        'expires' => $token_expiry
    );
    
    update_option("mwf_auto_login_token_{$auto_login_token}", $token_data, false);
    
    // Generate auto-login URL
    $auto_login_url = add_query_arg(array(
        'mwf_auto_login' => $auto_login_token
    ), home_url('/'));
    
    error_log("MWF Plugin Switch: Generated auto-login URL: {$auto_login_url}");
    
    // Return the URL as JSON instead of redirecting
    wp_send_json_success(array(
        'switch_url' => $auto_login_url,
        'user_name' => $user->display_name ?: $user->user_login,
        'message' => 'Auto-login URL generated successfully'
    ));
}

// END MWF CUSTOM CODE

// ====================================================================================
// COLLECTION DAY PREFERENCE - MY ACCOUNT PAGE
// ====================================================================================
/**
 * Add preferred collection day field to My Account page - ONLY for collection customers
 */
add_action('woocommerce_edit_account_form', 'mwf_add_collection_day_field');
function mwf_add_collection_day_field() {
    $user_id = get_current_user_id();
    
    // Check if this customer has any COLLECTION subscriptions (shipping = 0.00)
    $has_collection_subscription = false;
    
    if (function_exists('wcs_get_users_subscriptions')) {
        $subscriptions = wcs_get_users_subscriptions($user_id);
        
        foreach ($subscriptions as $subscription) {
            // Check if subscription has zero shipping (indicates collection)
            $shipping_total = $subscription->get_shipping_total();
            
            if ($shipping_total == 0 || $shipping_total == '0.00') {
                $has_collection_subscription = true;
                break;
            }
        }
    }
    
    // Only show the field if customer has collection subscriptions
    if (!$has_collection_subscription) {
        return;
    }
    
    $current_day = get_user_meta($user_id, 'preferred_collection_day', true);
    if (empty($current_day)) {
        $current_day = 'Friday'; // Default to Friday
    }
    
    ?>
    <fieldset class="mwf-collection-day-fieldset">
        <legend><?php esc_html_e('Collection Preferences', 'woocommerce'); ?></legend>
        
        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
            <label for="preferred_collection_day"><?php esc_html_e('Preferred Collection Day', 'woocommerce'); ?> <span class="required">*</span></label>
            <select name="preferred_collection_day" id="preferred_collection_day" class="woocommerce-Input woocommerce-Input--text input-text" required>
                <option value="Friday" <?php selected($current_day, 'Friday'); ?>>Friday</option>
                <option value="Saturday" <?php selected($current_day, 'Saturday'); ?>>Saturday</option>
            </select>
            <em style="font-size: 0.9em; color: #666;">Choose which day you'd like to collect your vegetable box each week.</em>
        </p>
    </fieldset>
    
    <style>
        .mwf-collection-day-fieldset {
            border: 1px solid #ddd;
            padding: 20px;
            margin: 20px 0;
            border-radius: 4px;
            background: #f9f9f9;
        }
        .mwf-collection-day-fieldset legend {
            font-weight: bold;
            font-size: 1.1em;
            padding: 0 10px;
            color: #333;
        }
    </style>
    <?php
}

/**
 * Validate the collection day field - must be Friday or Saturday
 */
add_action('woocommerce_save_account_details_errors', 'mwf_validate_collection_day_field', 10, 1);
function mwf_validate_collection_day_field($args) {
    $user_id = get_current_user_id();
    
    // Check if this customer has collection subscriptions
    $has_collection_subscription = false;
    
    if (function_exists('wcs_get_users_subscriptions')) {
        $subscriptions = wcs_get_users_subscriptions($user_id);
        
        foreach ($subscriptions as $subscription) {
            $shipping_total = $subscription->get_shipping_total();
            
            if ($shipping_total == 0 || $shipping_total == '0.00') {
                $has_collection_subscription = true;
                break;
            }
        }
    }
    
    // Only validate if customer has collection subscriptions
    if (!$has_collection_subscription) {
        return;
    }
    
    if (isset($_POST['preferred_collection_day'])) {
        $preferred_day = sanitize_text_field($_POST['preferred_collection_day']);
        
        // Must be Friday or Saturday
        if (!in_array($preferred_day, ['Friday', 'Saturday'])) {
            $args->add('error', __('Please select either Friday or Saturday as your collection day.', 'woocommerce'));
        }
    } else {
        // Required field
        $args->add('error', __('Please select your preferred collection day.', 'woocommerce'));
    }
}

/**
 * Save the collection day preference to user meta
 */
add_action('woocommerce_save_account_details', 'mwf_save_collection_day_field', 10, 1);
function mwf_save_collection_day_field($user_id) {
    if (isset($_POST['preferred_collection_day'])) {
        $preferred_day = sanitize_text_field($_POST['preferred_collection_day']);
        
        // Only allow Friday or Saturday
        if (in_array($preferred_day, ['Friday', 'Saturday'])) {
            update_user_meta($user_id, 'preferred_collection_day', $preferred_day);
            
            // Log the change for debugging
            error_log("MWF: Updated collection day preference for user {$user_id} to {$preferred_day}");
        }
    }
}

// ====================================================================================
// DISABLE ALL WOO COMMERCE EMAIL NOTIFICATIONS
// All emailing will be handled in Laravel admin from now on
// ====================================================================================

add_action('woocommerce_init', 'mwf_disable_woocommerce_emails');
function mwf_disable_woocommerce_emails() {
    // Disable all WooCommerce email notifications
    $emails = WC()->mailer()->get_emails();
    foreach ($emails as $email) {
        $email->enabled = 'no';
    }
}
