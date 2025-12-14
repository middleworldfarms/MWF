<?php
// Create this file at: /var/www/vhosts/middleworldfarms.org/httpdocs/wp-content/plugins/mwf-delivery-schedule/fix-fortnightly.php

// Load WordPress
require_once('../../../../wp-load.php');

// Define customers to update
$customers = [
    // Format: Customer email => [Fortnightly status, Week assignment]
    'phil.bauckham@hotmail.co.uk' => ['yes', 'B'], // Change to B if he should be on opposite week
    'anderson.ben0405@gmail.com' => ['yes', 'A'],
    'camelliacottagechildcare@gmail.com' => ['yes', 'B']
];

// Track results
$results = [];

// Process each customer
foreach ($customers as $email => $settings) {
    $user = get_user_by('email', $email);
    
    if (!$user) {
        $results[] = "❌ User with email {$email} not found";
        continue;
    }
    
    // Set fortnightly status
    update_user_meta($user->ID, 'mwf_is_fortnightly_customer', $settings[0]);
    
    // Set week assignment (A or B)
    update_user_meta($user->ID, 'mwf_fortnightly_week', $settings[1]);
    
    // Get their subscriptions
    $subscriptions = wcs_get_users_subscriptions($user->ID);
    
    foreach ($subscriptions as $subscription) {
        // Set subscription meta to record fortnightly override
        update_post_meta($subscription->get_id(), '_mwf_fortnightly', 'yes');
        
        // Record the week assignment at subscription level too for redundancy
        update_post_meta($subscription->get_id(), '_mwf_fortnightly_week', $settings[1]);
        
        $results[] = "✅ Updated subscription #{$subscription->get_id()} for {$user->display_name} to Fortnightly Week {$settings[1]}";
    }
}

// Clear the cache to ensure changes take effect
$plugin = new MWF_Delivery_Schedule_Manager();
$plugin->refresh_cache();

// Output results with some basic styling
echo '<!DOCTYPE html>
<html>
<head>
    <title>Fortnightly Fix</title>
    <style>
        body { font-family: sans-serif; line-height: 1.6; margin: 20px; }
        div { margin-bottom: 10px; }
        .success { color: green; }
        .error { color: red; }
    </style>
</head>
<body>
    <h1>Fortnightly Customer Fix Results</h1>';

foreach ($results as $result) {
    if (strpos($result, '✅') !== false) {
        echo "<div class='success'>{$result}</div>";
    } else {
        echo "<div class='error'>{$result}</div>";
    }
}

echo '<p><strong>IMPORTANT:</strong> Delete this file immediately after use for security!</p>
</body>
</html>';