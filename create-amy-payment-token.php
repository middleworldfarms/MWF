<?php
/**
 * Create payment token for Amy Eastwood
 */

require_once('wp-load.php');

$user_id = 1453; // Amy's user ID
$customer_id = 'cus_TNBB0ZZP8hvUJ8';
$payment_method_id = 'pm_1SQbIIHVCuOjVw0HpGknXFQo';

// Create WooCommerce payment token
$token = new WC_Payment_Token_CC();
$token->set_token($payment_method_id);
$token->set_gateway_id('stripe');
$token->set_user_id($user_id);
$token->set_card_type('mastercard');
$token->set_last4('9128');
$token->set_expiry_month('02');
$token->set_expiry_year('2027');
$token->set_default(true);

// Save the token
$token_id = $token->save();

if ($token_id) {
    // Add customer ID as meta
    $token->add_meta_data('customer_id', $customer_id, true);
    $token->add_meta_data('fingerprint', 'Aov480K7cos73Z3D', true);
    $token->save_meta_data();
    
    echo "Success! Payment token created with ID: " . $token_id . "\n";
    echo "User: " . $user_id . "\n";
    echo "Card: Mastercard ending in 9128\n";
    echo "Expires: 02/2027\n";
} else {
    echo "Failed to create payment token\n";
}
