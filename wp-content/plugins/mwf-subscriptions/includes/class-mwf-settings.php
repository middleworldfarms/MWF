<?php
/**
 * MWF Subscriptions Settings
 * 
 * Provides admin settings page for configuration
 */

if (!defined('ABSPATH')) {
    exit;
}

class MWF_Settings {
    
    private static $instance = null;
    
    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('admin_menu', [$this, 'add_settings_page']);
        add_action('admin_init', [$this, 'register_settings']);
    }
    
    /**
     * Add settings page to WordPress admin
     */
    public function add_settings_page() {
        add_options_page(
            __('MWF Subscriptions Settings', 'mwf-subscriptions'),
            __('MWF Subscriptions', 'mwf-subscriptions'),
            'manage_options',
            'mwf-subscriptions-settings',
            [$this, 'render_settings_page']
        );
    }
    
    /**
     * Register plugin settings
     */
    public function register_settings() {
        register_setting('mwf_subscriptions_settings', 'mwf_api_url', [
            'type' => 'string',
            'default' => 'https://admin.middleworldfarms.org:8444/api/subscriptions',
            'sanitize_callback' => 'sanitize_url'
        ]);
        
        register_setting('mwf_subscriptions_settings', 'mwf_api_key', [
            'type' => 'string',
            'default' => '',
            'sanitize_callback' => 'sanitize_text_field'
        ]);
        
        register_setting('mwf_subscriptions_settings', 'mwf_default_delivery_day', [
            'type' => 'string',
            'default' => 'thursday',
            'sanitize_callback' => 'sanitize_text_field'
        ]);
        
        register_setting('mwf_subscriptions_settings', 'mwf_collection_delivery_day', [
            'type' => 'string',
            'default' => 'saturday',
            'sanitize_callback' => 'sanitize_text_field'
        ]);
    }
    
    /**
     * Render settings page
     */
    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        // Save settings message
        if (isset($_GET['settings-updated'])) {
            add_settings_error(
                'mwf_subscriptions_messages',
                'mwf_subscriptions_message',
                __('Settings Saved', 'mwf-subscriptions'),
                'updated'
            );
        }
        
        settings_errors('mwf_subscriptions_messages');
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <form action="options.php" method="post">
                <?php
                settings_fields('mwf_subscriptions_settings');
                ?>
                
                <table class="form-table" role="presentation">
                    <tbody>
                        <tr>
                            <th scope="row">
                                <label for="mwf_api_url"><?php _e('Laravel API URL', 'mwf-subscriptions'); ?></label>
                            </th>
                            <td>
                                <input type="url" 
                                       id="mwf_api_url" 
                                       name="mwf_api_url" 
                                       value="<?php echo esc_attr(get_option('mwf_api_url', 'https://admin.middleworldfarms.org:8444/api/subscriptions')); ?>" 
                                       class="regular-text" 
                                       required>
                                <p class="description">
                                    <?php _e('Full URL to your Laravel subscription API endpoint (e.g., https://admin.yourdomain.com/api/subscriptions)', 'mwf-subscriptions'); ?>
                                </p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="mwf_api_key"><?php _e('API Key', 'mwf-subscriptions'); ?></label>
                            </th>
                            <td>
                                <input type="text" 
                                       id="mwf_api_key" 
                                       name="mwf_api_key" 
                                       value="<?php echo esc_attr(get_option('mwf_api_key')); ?>" 
                                       class="regular-text" 
                                       required>
                                <p class="description">
                                    <?php _e('API key for authenticating with Laravel backend (must match MWF_API_KEY in Laravel .env)', 'mwf-subscriptions'); ?>
                                </p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="mwf_default_delivery_day"><?php _e('Default Delivery Day', 'mwf-subscriptions'); ?></label>
                            </th>
                            <td>
                                <select id="mwf_default_delivery_day" name="mwf_default_delivery_day">
                                    <?php
                                    $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
                                    $selected = get_option('mwf_default_delivery_day', 'thursday');
                                    foreach ($days as $day) {
                                        printf(
                                            '<option value="%s" %s>%s</option>',
                                            esc_attr($day),
                                            selected($selected, $day, false),
                                            esc_html(ucfirst($day))
                                        );
                                    }
                                    ?>
                                </select>
                                <p class="description">
                                    <?php _e('Delivery day for "Delivery" shipping method', 'mwf-subscriptions'); ?>
                                </p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="mwf_collection_delivery_day"><?php _e('Collection Day', 'mwf-subscriptions'); ?></label>
                            </th>
                            <td>
                                <select id="mwf_collection_delivery_day" name="mwf_collection_delivery_day">
                                    <?php
                                    $selected = get_option('mwf_collection_delivery_day', 'saturday');
                                    foreach ($days as $day) {
                                        printf(
                                            '<option value="%s" %s>%s</option>',
                                            esc_attr($day),
                                            selected($selected, $day, false),
                                            esc_html(ucfirst($day))
                                        );
                                    }
                                    ?>
                                </select>
                                <p class="description">
                                    <?php _e('Delivery day for "Collection" shipping method', 'mwf-subscriptions'); ?>
                                </p>
                            </td>
                        </tr>
                    </tbody>
                </table>
                
                <?php submit_button(__('Save Settings', 'mwf-subscriptions')); ?>
            </form>
            
            <hr>
            
            <h2><?php _e('Setup Instructions', 'mwf-subscriptions'); ?></h2>
            <div class="notice notice-info inline">
                <h3><?php _e('Laravel Backend Setup', 'mwf-subscriptions'); ?></h3>
                <ol>
                    <li><?php _e('Install Laravel application on subdomain (e.g., admin.yourdomain.com)', 'mwf-subscriptions'); ?></li>
                    <li><?php _e('Run migrations: <code>php artisan migrate</code>', 'mwf-subscriptions'); ?></li>
                    <li><?php _e('Set API key in Laravel .env: <code>MWF_API_KEY=your-random-key-here</code>', 'mwf-subscriptions'); ?></li>
                    <li><?php _e('Configure WordPress database connection in Laravel config/database.php', 'mwf-subscriptions'); ?></li>
                    <li><?php _e('Set Stripe keys in Laravel .env', 'mwf-subscriptions'); ?></li>
                </ol>
                
                <h3><?php _e('WordPress/WooCommerce Setup', 'mwf-subscriptions'); ?></h3>
                <ol>
                    <li><?php _e('Install WooCommerce and WooCommerce Payments', 'mwf-subscriptions'); ?></li>
                    <li><?php _e('Configure Stripe in WooCommerce Payments', 'mwf-subscriptions'); ?></li>
                    <li><?php _e('Create subscription products with variations for billing frequency', 'mwf-subscriptions'); ?></li>
                    <li><?php _e('Add <code>_is_vegbox_subscription</code> and <code>_vegbox_plan_id</code> custom fields to products', 'mwf-subscriptions'); ?></li>
                    <li><?php _e('Enter API URL and Key above', 'mwf-subscriptions'); ?></li>
                </ol>
                
                <h3><?php _e('Product Configuration', 'mwf-subscriptions'); ?></h3>
                <p><?php _e('For each subscription product, add these custom fields:', 'mwf-subscriptions'); ?></p>
                <ul>
                    <li><code>_is_vegbox_subscription</code> = <code>yes</code></li>
                    <li><code>_vegbox_plan_id</code> = <code>[Laravel plan ID]</code></li>
                </ul>
            </div>
            
            <hr>
            
            <h2><?php _e('Test Connection', 'mwf-subscriptions'); ?></h2>
            <?php $this->render_connection_test(); ?>
        </div>
        <?php
    }
    
    /**
     * Render connection test section
     */
    private function render_connection_test() {
        $api_url = get_option('mwf_api_url');
        $api_key = get_option('mwf_api_key');
        
        if (empty($api_url) || empty($api_key)) {
            echo '<p class="description">' . __('Configure API URL and Key above to test connection.', 'mwf-subscriptions') . '</p>';
            return;
        }
        
        // Test API connection
        $test_url = trailingslashit($api_url) . '../health'; // Assuming health endpoint exists
        
        echo '<div id="mwf-connection-test">';
        echo '<button type="button" class="button" onclick="mwfTestConnection()">' . __('Test API Connection', 'mwf-subscriptions') . '</button>';
        echo '<span id="mwf-test-result"></span>';
        echo '</div>';
        
        ?>
        <script>
        function mwfTestConnection() {
            const resultEl = document.getElementById('mwf-test-result');
            resultEl.innerHTML = '<span class="spinner is-active" style="float:none;margin:0 10px;"></span><?php _e('Testing...', 'mwf-subscriptions'); ?>';
            
            fetch('<?php echo esc_url($api_url); ?>', {
                method: 'GET',
                headers: {
                    'X-MWF-API-Key': '<?php echo esc_js($api_key); ?>'
                }
            })
            .then(response => {
                if (response.ok) {
                    resultEl.innerHTML = '<span style="color:green;margin-left:10px;">✓ <?php _e('Connection successful!', 'mwf-subscriptions'); ?></span>';
                } else {
                    resultEl.innerHTML = '<span style="color:red;margin-left:10px;">✗ <?php _e('Connection failed:', 'mwf-subscriptions'); ?> ' + response.status + '</span>';
                }
            })
            .catch(error => {
                resultEl.innerHTML = '<span style="color:red;margin-left:10px;">✗ <?php _e('Error:', 'mwf-subscriptions'); ?> ' + error.message + '</span>';
            });
        }
        </script>
        <?php
    }
}

// Initialize settings
MWF_Settings::instance();
