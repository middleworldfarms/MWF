<?php
require_once('wp-load.php');
require_once('wp-content/plugins/mwf-subscriptions/migrate-wc-subscriptions.php');

$migrator = new MWF_Subscription_Migrator(false);
$reflection = new ReflectionClass($migrator);

// Get private method
$method = $reflection->getMethod('extract_subscription_data');
$method->setAccessible(true);

// Get private method to get subscription from DB
$get_sub_method = $reflection->getMethod('get_subscription_from_db');
$get_sub_method->setAccessible(true);

// Get Laura's subscription
$subscription = $get_sub_method->invoke($migrator, 228084);
$user = get_user_by('id', 28);

// Extract data
$data = $method->invoke($migrator, $subscription, $user);

echo "Extracted Data for Subscription #228084:\n";
echo json_encode($data, JSON_PRETTY_PRINT) . "\n";
