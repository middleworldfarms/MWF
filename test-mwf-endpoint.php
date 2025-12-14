<?php
/**
 * Test MWF subscriptions endpoint
 */
require_once(dirname(__FILE__) . '/wp-load.php');

wp_set_current_user(1018);

// Check if endpoint is registered
global $wp_rewrite;
echo '<h1>Endpoint Registration Test</h1>';
echo '<h2>Rewrite Endpoints:</h2>';
echo '<pre>';
print_r($wp_rewrite->endpoints);
echo '</pre>';

echo '<h2>WooCommerce My Account Menu Items:</h2>';
echo '<pre>';
$menu_items = apply_filters('woocommerce_account_menu_items', array());
print_r($menu_items);
echo '</pre>';

echo '<h2>Registered Actions:</h2>';
$actions = [
    'woocommerce_account_mwf-subscriptions_endpoint',
    'woocommerce_account_mwf-view-subscription_endpoint'
];

foreach ($actions as $action) {
    echo '<h3>' . $action . '</h3>';
    if (has_action($action)) {
        $callbacks = $GLOBALS['wp_filter'][$action] ?? [];
        echo '<pre>';
        foreach ($callbacks as $priority => $callbacks_at_priority) {
            echo "Priority $priority:\n";
            foreach ($callbacks_at_priority as $callback) {
                if (is_array($callback['function'])) {
                    $class = is_object($callback['function'][0]) ? get_class($callback['function'][0]) : $callback['function'][0];
                    $method = $callback['function'][1];
                    echo "  - {$class}::{$method}\n";
                } else {
                    echo "  - " . $callback['function'] . "\n";
                }
            }
        }
        echo '</pre>';
    } else {
        echo '<p style="color:red">No callbacks registered!</p>';
    }
}

// Test direct URL
echo '<h2>Test URLs:</h2>';
echo '<ul>';
echo '<li><a href="/info/my-account/mwf-subscriptions/">MWF Subscriptions</a></li>';
echo '<li><a href="/info/my-account/mwf-view-subscription/151/">MWF View Subscription 151</a></li>';
echo '</ul>';
