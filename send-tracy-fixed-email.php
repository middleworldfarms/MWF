<?php
/**
 * Send Tracy Goree the corrected payment email with proper link
 * Run once, then delete this file
 */

require_once 'wp-load.php';

$order_id = 228071;
$order = wc_get_order($order_id);

if (!$order) {
    die('Order not found');
}

$customer_email = $order->get_billing_email();
$customer_name = $order->get_billing_first_name();
$order_total = $order->get_total();

// Get the smart payment URL (redirects to payment methods page)
$payment_url = $order->get_checkout_payment_url();

// Build the email
$to = 'middleworldfarms@gmail.com'; // SEND TO YOU FIRST FOR REVIEW
$subject = 'DRAFT: Action Required: Update Payment Method for Your Subscription (FOR TRACY GOREE)';
$headers = array(
    'Content-Type: text/html; charset=UTF-8',
    'From: Middleworld Farms <noreply@middleworldfarms.org>'
);

$message = '
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #96588a; color: white; padding: 20px; text-align: center; }
        .content { background: #f9f9f9; padding: 20px; border: 1px solid #ddd; }
        .button { display: inline-block; padding: 12px 24px; background: #96588a; color: white; text-decoration: none; border-radius: 4px; margin: 10px 0; font-weight: bold; }
        .footer { text-align: center; padding: 20px; font-size: 12px; color: #666; }
        ul, ol { margin: 15px 0; padding-left: 25px; }
        .important { background: #fff3cd; border-left: 4px solid #ffc107; padding: 10px; margin: 15px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Middleworld Farms</h1>
        </div>
        
        <div class="content">
            <p>Hi ' . esc_html($customer_name) . ',</p>
            
            <p><strong>We sincerely apologize for the confusion and frustration you experienced when trying to pay for your subscription renewal.</strong></p>
            
            <p>We\'ve identified the issue: our previous email sent you to the wrong page, which is why you couldn\'t complete your payment. This was entirely our fault, and we\'re sorry for wasting your time.</p>
            
            <div class="important">
                <strong>The actual issue:</strong> Your subscription renewal payment could not be processed because we don\'t have a valid payment card on file.
            </div>
            
            <p>This can happen if:</p>
            <ul>
                <li>Your previous card has expired</li>
                <li>Your card was removed or declined by your bank</li>
                <li>This is a new subscription without a saved payment method</li>
            </ul>
            
            <p><strong>Here is the link to view your order and add a payment method:</strong></p>
            <ol>
                <li><a href="' . esc_url($payment_url) . '" class="button" style="color: white; text-decoration: none;">View Your Order</a></li>
                <li>On the order page, go to your Account → Payment Methods</li>
                <li>Add your card details securely</li>
                <li>Return to the order and click "Pay"</li>
            </ol>
            
            <p><strong>Or directly go to:</strong> <a href="https://middleworldfarms.org/info/my-account/payment-methods/">Payment Methods Page</a> to add your card first.</p>
            
            <p><strong>We truly appreciate your patience and understanding.</strong> As a thank you for dealing with our mistake, we\'d like to offer you a gesture of goodwill on your next order. Just mention this email when you contact us.</p>
            
            <p><strong>Order Number:</strong> #' . esc_html($order_id) . '<br>
            <strong>Amount Due:</strong> £' . esc_html($order_total) . '</p>
            
            <p>If you need any help, please reply to this email or contact us directly.</p>
            
            <p>Thank you for your patience!<br>
            <strong>Middleworld Farms Team</strong></p>
        </div>
        
        <div class="footer">
            <p>This email was sent to ' . esc_html($customer_email) . '<br>
            Middleworld Farms | <a href="https://middleworldfarms.org">middleworldfarms.org</a></p>
        </div>
    </div>
</body>
</html>
';

// Send the email
$sent = wp_mail($to, $subject, $message, $headers);

if ($sent) {
    echo "✅ DRAFT email sent to YOU (middleworldfarms@gmail.com) for review\n";
    echo "📧 This was NOT sent to Tracy yet - review it first!\n\n";
    echo "📧 Subject: " . $subject . "\n";
    echo "👤 Will be sent to: Tracy Goree (" . $customer_email . ")\n";
    echo "🔗 Payment URL in email: " . $payment_url . "\n\n";
    echo "After reviewing, if you want to send to Tracy:\n";
    echo "1. Edit this file and change line 21 to: \$to = '" . $customer_email . "';\n";
    echo "2. Change line 22 subject to remove 'DRAFT:'\n";
    echo "3. Run: php send-tracy-fixed-email.php\n\n";
    echo "✅ Delete this file after use: rm " . __FILE__ . "\n";
} else {
    echo "❌ Failed to send email\n";
    echo "Check your WordPress email settings\n";
}
