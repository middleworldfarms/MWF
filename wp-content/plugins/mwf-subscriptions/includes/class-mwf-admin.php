<?php
/**
 * MWF Subscriptions Admin Interface
 * 
 * Adds product meta boxes, admin menu, and toolbar shortcuts
 */

if (!defined('ABSPATH')) {
    exit;
}

class MWF_Subscriptions_Admin {
    
    private static $instance = null;
    
    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->init_hooks();
    }
    
    private function init_hooks() {
        // Admin menu
        add_action('admin_menu', [$this, 'add_admin_menu']);
        
        // Admin toolbar
        add_action('admin_bar_menu', [$this, 'add_toolbar_menu'], 100);
        
        // Product edit page meta boxes
        add_action('add_meta_boxes', [$this, 'add_product_meta_boxes']);
        add_action('save_post_product', [$this, 'save_product_meta'], 10, 1);
        
        // Admin scripts
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);
        
        // Product list columns
        add_filter('manage_edit-product_columns', [$this, 'add_product_columns']);
        add_action('manage_product_posts_custom_column', [$this, 'render_product_columns'], 10, 2);
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            'MWF Subscriptions',
            'Subscriptions',
            'manage_woocommerce',
            'mwf-subscriptions',
            [$this, 'render_admin_page'],
            'dashicons-update',
            56
        );
        
        add_submenu_page(
            'mwf-subscriptions',
            'Subscription Products',
            'Products',
            'manage_woocommerce',
            'mwf-subscription-products',
            [$this, 'render_products_page']
        );
        
        add_submenu_page(
            'mwf-subscriptions',
            'Settings',
            'Settings',
            'manage_options',
            'mwf-subscriptions-settings',
            [$this, 'render_settings_page']
        );
    }
    
    /**
     * Add toolbar menu
     */
    public function add_toolbar_menu($wp_admin_bar) {
        if (!current_user_can('manage_woocommerce')) {
            return;
        }
        
        // Count subscription products
        $count = $this->count_subscription_products();
        
        $wp_admin_bar->add_node([
            'id' => 'mwf-subscriptions',
            'title' => '🔄 Subscriptions (' . $count . ')',
            'href' => admin_url('admin.php?page=mwf-subscriptions'),
            'meta' => [
                'class' => 'mwf-subscriptions-toolbar'
            ]
        ]);
        
        $wp_admin_bar->add_node([
            'id' => 'mwf-subscriptions-products',
            'parent' => 'mwf-subscriptions',
            'title' => 'Subscription Products',
            'href' => admin_url('admin.php?page=mwf-subscription-products')
        ]);
        
        $wp_admin_bar->add_node([
            'id' => 'mwf-subscriptions-settings',
            'parent' => 'mwf-subscriptions',
            'title' => 'Settings',
            'href' => admin_url('admin.php?page=mwf-subscriptions-settings')
        ]);
        
        $wp_admin_bar->add_node([
            'id' => 'mwf-subscriptions-laravel',
            'parent' => 'mwf-subscriptions',
            'title' => 'Laravel Admin →',
            'href' => 'https://admin.middleworldfarms.org:8444/subscriptions',
            'meta' => ['target' => '_blank']
        ]);
    }
    
    /**
     * Add product meta boxes
     */
    public function add_product_meta_boxes() {
        add_meta_box(
            'mwf_subscription_options',
            '🔄 Subscription Options',
            [$this, 'render_product_meta_box'],
            'product',
            'side',
            'high'
        );
    }
    
    /**
     * Render product meta box
     */
    public function render_product_meta_box($post) {
        wp_nonce_field('mwf_subscription_meta', 'mwf_subscription_nonce');
        
        $is_subscription = get_post_meta($post->ID, '_is_vegbox_subscription', true);
        $plan_id = get_post_meta($post->ID, '_vegbox_plan_id', true);
        
        ?>
        <div class="mwf-subscription-meta">
            <p>
                <label>
                    <input type="checkbox" name="mwf_is_subscription" value="yes" <?php checked($is_subscription, 'yes'); ?>>
                    <strong>This is a subscription product</strong>
                </label>
            </p>
            
            <p>
                <label for="mwf_plan_id">
                    <strong>Laravel Plan ID:</strong><br>
                    <input type="number" id="mwf_plan_id" name="mwf_plan_id" value="<?php echo esc_attr($plan_id); ?>" min="1" step="1" class="small-text">
                </label>
                <br>
                <small>Must match plan ID in Laravel admin</small>
            </p>
            
            <?php if ($is_subscription === 'yes' && $plan_id): ?>
                <div class="mwf-subscription-status" style="background: #d4edda; padding: 10px; border-left: 4px solid #28a745; margin-top: 10px;">
                    <strong>✅ Configured</strong><br>
                    <small>Customers will see delivery day selector at checkout</small>
                </div>
            <?php else: ?>
                <div class="mwf-subscription-status" style="background: #fff3cd; padding: 10px; border-left: 4px solid #ffc107; margin-top: 10px;">
                    <strong>⚠️ Not Configured</strong><br>
                    <small>Enable subscription and set plan ID</small>
                </div>
            <?php endif; ?>
            
            <p style="margin-top: 15px;">
                <a href="https://admin.middleworldfarms.org:8444/subscriptions/plans" target="_blank" class="button button-secondary">
                    View Laravel Plans →
                </a>
            </p>
        </div>
        
        <style>
            .mwf-subscription-meta label { display: block; margin-bottom: 5px; }
            .mwf-subscription-meta input[type="number"] { width: 100%; max-width: 150px; }
            .mwf-subscription-status { border-radius: 4px; font-size: 13px; }
        </style>
        <?php
    }
    
    /**
     * Save product meta
     */
    public function save_product_meta($post_id) {
        // Check nonce
        if (!isset($_POST['mwf_subscription_nonce']) || !wp_verify_nonce($_POST['mwf_subscription_nonce'], 'mwf_subscription_meta')) {
            return;
        }
        
        // Check autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        // Check permissions
        if (!current_user_can('edit_product', $post_id)) {
            return;
        }
        
        // Save subscription flag
        $is_subscription = isset($_POST['mwf_is_subscription']) ? 'yes' : 'no';
        update_post_meta($post_id, '_is_vegbox_subscription', $is_subscription);
        
        // Save plan ID
        if (isset($_POST['mwf_plan_id']) && !empty($_POST['mwf_plan_id'])) {
            update_post_meta($post_id, '_vegbox_plan_id', intval($_POST['mwf_plan_id']));
        } else {
            delete_post_meta($post_id, '_vegbox_plan_id');
        }
        
        error_log("[MWF Subscriptions Admin] Product {$post_id} saved - Subscription: {$is_subscription}, Plan ID: " . intval($_POST['mwf_plan_id'] ?? 0));
    }
    
    /**
     * Add product list columns
     */
    public function add_product_columns($columns) {
        $new_columns = [];
        foreach ($columns as $key => $value) {
            $new_columns[$key] = $value;
            if ($key === 'name') {
                $new_columns['mwf_subscription'] = '🔄 Subscription';
            }
        }
        return $new_columns;
    }
    
    /**
     * Render product columns
     */
    public function render_product_columns($column, $post_id) {
        if ($column === 'mwf_subscription') {
            $is_subscription = get_post_meta($post_id, '_is_vegbox_subscription', true);
            $plan_id = get_post_meta($post_id, '_vegbox_plan_id', true);
            
            if ($is_subscription === 'yes' && $plan_id) {
                echo '<span style="color: #28a745; font-weight: bold;">✅ Plan ' . esc_html($plan_id) . '</span>';
            } elseif ($is_subscription === 'yes') {
                echo '<span style="color: #ffc107;">⚠️ No Plan</span>';
            } else {
                echo '<span style="color: #999;">—</span>';
            }
        }
    }
    
    /**
     * Count subscription products
     */
    private function count_subscription_products() {
        $args = [
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'meta_query' => [
                [
                    'key' => '_is_vegbox_subscription',
                    'value' => 'yes'
                ]
            ],
            'fields' => 'ids'
        ];
        
        $query = new WP_Query($args);
        return $query->found_posts;
    }
    
    /**
     * Enqueue admin scripts
     */
    public function enqueue_admin_scripts($hook) {
        if (strpos($hook, 'mwf-subscriptions') !== false || $hook === 'post.php' || $hook === 'post-new.php') {
            wp_enqueue_style('mwf-subscriptions-admin', MWF_SUBS_PLUGIN_URL . 'assets/css/admin.css', [], MWF_SUBS_VERSION);
        }
    }
    
    /**
     * Render main admin page
     */
    public function render_admin_page() {
        $subscription_products = $this->get_subscription_products();
        ?>
        <div class="wrap">
            <h1>🔄 MWF Subscriptions</h1>
            
            <div class="mwf-admin-dashboard">
                <div class="mwf-admin-card">
                    <h2>Subscription Products</h2>
                    <p class="mwf-stat"><?php echo count($subscription_products); ?></p>
                    <p>Products configured for subscriptions</p>
                    <a href="<?php echo admin_url('admin.php?page=mwf-subscription-products'); ?>" class="button button-primary">Manage Products</a>
                </div>
                
                <div class="mwf-admin-card">
                    <h2>Laravel Backend</h2>
                    <p class="mwf-stat">✓</p>
                    <p>Connected to admin.middleworldfarms.org</p>
                    <a href="https://admin.middleworldfarms.org:8444/subscriptions" target="_blank" class="button">Open Laravel Admin</a>
                </div>
                
                <div class="mwf-admin-card">
                    <h2>Plugin Status</h2>
                    <p class="mwf-stat">v<?php echo MWF_SUBS_VERSION; ?></p>
                    <p>Active and running</p>
                    <a href="<?php echo admin_url('admin.php?page=mwf-subscriptions-settings'); ?>" class="button">Settings</a>
                </div>
            </div>
            
            <style>
                .mwf-admin-dashboard { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-top: 20px; }
                .mwf-admin-card { background: #fff; padding: 20px; border: 1px solid #ccd0d4; box-shadow: 0 1px 1px rgba(0,0,0,.04); }
                .mwf-admin-card h2 { margin-top: 0; }
                .mwf-stat { font-size: 48px; font-weight: bold; margin: 10px 0; color: #2271b1; }
            </style>
        </div>
        <?php
    }
    
    /**
     * Render products page
     */
    public function render_products_page() {
        $subscription_products = $this->get_subscription_products();
        ?>
        <div class="wrap">
            <h1>Subscription Products</h1>
            
            <p>Products marked as subscriptions will show delivery day selector at checkout and create subscriptions via Laravel API.</p>
            
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Plan ID</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($subscription_products)): ?>
                        <tr>
                            <td colspan="4">
                                <em>No subscription products configured. Edit a product and enable subscription in the sidebar.</em>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($subscription_products as $product_id): ?>
                            <?php 
                            $product = wc_get_product($product_id);
                            $plan_id = get_post_meta($product_id, '_vegbox_plan_id', true);
                            ?>
                            <tr>
                                <td><strong><?php echo esc_html($product->get_name()); ?></strong></td>
                                <td><?php echo $plan_id ? 'Plan ' . esc_html($plan_id) : '<span style="color: #ffc107;">Not set</span>'; ?></td>
                                <td><?php echo $plan_id ? '<span style="color: #28a745;">✅ Active</span>' : '<span style="color: #ffc107;">⚠️ Incomplete</span>'; ?></td>
                                <td>
                                    <a href="<?php echo get_edit_post_link($product_id); ?>" class="button button-small">Edit</a>
                                    <a href="<?php echo get_permalink($product_id); ?>" target="_blank" class="button button-small">View</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }
    
    /**
     * Render settings page
     */
    public function render_settings_page() {
        ?>
        <div class="wrap">
            <h1>MWF Subscriptions Settings</h1>
            
            <form method="post" action="options.php">
                <?php settings_fields('mwf_subscriptions_settings'); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">Laravel API URL</th>
                        <td>
                            <input type="url" value="<?php echo esc_attr(MWF_SUBS_API_URL); ?>" class="regular-text" disabled>
                            <p class="description">Configured in plugin constants</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">API Key</th>
                        <td>
                            <input type="text" value="<?php echo esc_attr(substr(MWF_SUBS_API_KEY, 0, 10)); ?>..." class="regular-text" disabled>
                            <p class="description">Configured in plugin constants (hidden for security)</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Debug Mode</th>
                        <td>
                            <label>
                                <input type="checkbox" <?php checked(defined('WP_DEBUG') && WP_DEBUG); ?> disabled>
                                WordPress debug logging <?php echo (defined('WP_DEBUG') && WP_DEBUG) ? '<span style="color: #28a745;">✓ Enabled</span>' : '<span style="color: #999;">Disabled</span>'; ?>
                            </label>
                            <p class="description">Logs saved to: <code>/wp-content/debug.log</code></p>
                        </td>
                    </tr>
                </table>
                
                <h2>Quick Actions</h2>
                <p>
                    <a href="<?php echo admin_url('edit.php?post_type=product'); ?>" class="button">Manage All Products</a>
                    <a href="https://admin.middleworldfarms.org:8444/subscriptions" target="_blank" class="button">Laravel Admin</a>
                    <a href="<?php echo admin_url('admin.php?page=mwf-subscription-products'); ?>" class="button">View Subscription Products</a>
                </p>
            </form>
        </div>
        <?php
    }
    
    /**
     * Get subscription products
     */
    private function get_subscription_products() {
        $args = [
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'meta_query' => [
                [
                    'key' => '_is_vegbox_subscription',
                    'value' => 'yes'
                ]
            ],
            'fields' => 'ids'
        ];
        
        $query = new WP_Query($args);
        return $query->posts;
    }
}
