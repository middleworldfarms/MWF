<?php
/**
 * MWF Email Validation & Verification System
 * Prevents duplicate accounts and ensures valid email addresses
 */

// Add email validation JavaScript to checkout and registration pages
add_action('wp_enqueue_scripts', 'mwf_enqueue_email_validation_scripts');
function mwf_enqueue_email_validation_scripts() {
    // Only load on checkout, registration, and account pages
    if (is_checkout() || is_account_page() || is_page('register')) {
        wp_enqueue_script(
            'mwf-email-validator',
            get_stylesheet_directory_uri() . '/js/email-validator.js',
            array('jquery'),
            '1.0.0',
            true
        );
        
        wp_localize_script('mwf-email-validator', 'mwfEmailValidator', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('mwf_email_validation'),
            'messages' => array(
                'typo_detected' => 'Did you mean: {suggestion}?',
                'invalid_email' => 'Please enter a valid email address',
                'email_exists' => 'An account with this email already exists. Would you like to log in instead?',
                'verification_sent' => 'Verification email sent! Please check your inbox.',
                'verification_failed' => 'Email verification failed. Please check the address and try again.'
            )
        ));
    }
}

// Real-time email validation AJAX handler
add_action('wp_ajax_mwf_validate_email', 'mwf_validate_email_ajax');
add_action('wp_ajax_nopriv_mwf_validate_email', 'mwf_validate_email_ajax');
function mwf_validate_email_ajax() {
    check_ajax_referer('mwf_email_validation', 'nonce');
    
    $email = sanitize_email($_POST['email']);
    $response = array();
    
    // 1. Basic email format validation
    if (!is_email($email)) {
        $response['valid'] = false;
        $response['message'] = 'Invalid email format';
        wp_send_json($response);
    }
    
    // 2. Check for common typos
    $suggestion = mwf_check_email_typos($email);
    if ($suggestion && $suggestion !== $email) {
        $response['typo_detected'] = true;
        $response['suggestion'] = $suggestion;
        $response['message'] = "Did you mean: {$suggestion}?";
    }
    
    // 3. Check if email already exists
    $existing_user = get_user_by('email', $email);
    if ($existing_user) {
        $response['email_exists'] = true;
        $response['existing_user_id'] = $existing_user->ID;
        $response['message'] = 'An account with this email already exists';
    }
    
    // 4. Attempt to verify email deliverability (if new email)
    if (!$existing_user) {
        $deliverable = mwf_check_email_deliverability($email);
        $response['deliverable'] = $deliverable;
        if (!$deliverable) {
            $response['message'] = 'This email address appears to be invalid or undeliverable';
        }
    }
    
    $response['valid'] = true;
    wp_send_json($response);
}

// Check for common email typos
function mwf_check_email_typos($email) {
    $domain_corrections = array(
        // Common UK email typos
        'hotmai.co.uk' => 'hotmail.co.uk',
        'hotmial.co.uk' => 'hotmail.co.uk',
        'hotmil.co.uk' => 'hotmail.co.uk',
        'hotmal.co.uk' => 'hotmail.co.uk',
        'hotmails.co.uk' => 'hotmail.co.uk',
        'hotmial.com' => 'hotmail.com',
        'hotmai.com' => 'hotmail.com',
        
        // Gmail typos
        'gmail.co.uk' => 'gmail.com',
        'gmai.com' => 'gmail.com',
        'gmial.com' => 'gmail.com',
        'gmail.co' => 'gmail.com',
        'gmails.com' => 'gmail.com',
        
        // Yahoo typos
        'yahoo.co.uk' => 'yahoo.com',
        'yahooo.com' => 'yahoo.com',
        'yaho.com' => 'yahoo.com',
        'yahoo.co' => 'yahoo.com',
        
        // Outlook typos
        'outlook.co.uk' => 'outlook.com',
        'outlok.com' => 'outlook.com',
        'outlook.co' => 'outlook.com',
        
        // Other common UK domains
        'btinternet.co.uk' => 'btinternet.com',
        'talktalk.co.uk' => 'talktalk.net',
    );
    
    // Check for missing characters in username
    $username_corrections = array(
        // Common username typos (missing characters)
        'westendgir' => 'westendgirl',
        'westendgil' => 'westendgirl',
        'westendgrl' => 'westendgirl',
    );
    
    $corrected_email = $email;
    
    // Check domain corrections
    foreach ($domain_corrections as $typo => $correction) {
        if (strpos($email, $typo) !== false) {
            $corrected_email = str_replace($typo, $correction, $email);
            break;
        }
    }
    
    // Check username corrections
    list($username, $domain) = explode('@', $email, 2);
    foreach ($username_corrections as $typo => $correction) {
        if (strpos($username, $typo) !== false) {
            $corrected_username = str_replace($typo, $correction, $username);
            $corrected_email = $corrected_username . '@' . $domain;
            break;
        }
    }
    
    return $corrected_email;
}

// Basic email deliverability check
function mwf_check_email_deliverability($email) {
    list($username, $domain) = explode('@', $email, 2);
    
    // Check if domain has MX record
    if (!checkdnsrr($domain, 'MX')) {
        return false;
    }
    
    // Additional checks could be added here:
    // - Disposable email detection
    // - Role-based email detection (admin@, info@, etc.)
    // - Integration with email validation services
    
    return true;
}

// Send verification email before account creation
add_action('wp_ajax_mwf_send_verification_email', 'mwf_send_verification_email_ajax');
add_action('wp_ajax_nopriv_mwf_send_verification_email', 'mwf_send_verification_email_ajax');
function mwf_send_verification_email_ajax() {
    check_ajax_referer('mwf_email_validation', 'nonce');
    
    $email = sanitize_email($_POST['email']);
    
    if (!is_email($email)) {
        wp_send_json_error('Invalid email address');
    }
    
    // Generate verification code
    $verification_code = wp_generate_password(6, false, false);
    
    // Store verification code in session/transient
    set_transient('mwf_email_verification_' . md5($email), $verification_code, 15 * MINUTE_IN_SECONDS);
    
    // Send verification email
    $subject = '[Middle World Farms] Verify Your Email Address';
    $message = "
        <h2>Email Verification Required</h2>
        <p>Hello,</p>
        <p>Please enter this verification code to confirm your email address:</p>
        <h3 style='background: #f0f0f0; padding: 10px; text-align: center; font-size: 24px; letter-spacing: 2px;'>{$verification_code}</h3>
        <p>This code will expire in 15 minutes.</p>
        <p>If you didn't request this verification, please ignore this email.</p>
        <p>Best regards,<br>Middle World Farms</p>
    ";
    
    $headers = array('Content-Type: text/html; charset=UTF-8');
    $sent = wp_mail($email, $subject, $message, $headers);
    
    if ($sent) {
        wp_send_json_success('Verification email sent successfully');
    } else {
        wp_send_json_error('Failed to send verification email');
    }
}

// Verify email code
add_action('wp_ajax_mwf_verify_email_code', 'mwf_verify_email_code_ajax');
add_action('wp_ajax_nopriv_mwf_verify_email_code', 'mwf_verify_email_code_ajax');
function mwf_verify_email_code_ajax() {
    check_ajax_referer('mwf_email_validation', 'nonce');
    
    $email = sanitize_email($_POST['email']);
    $code = sanitize_text_field($_POST['code']);
    
    $stored_code = get_transient('mwf_email_verification_' . md5($email));
    
    if ($stored_code && $stored_code === $code) {
        // Mark email as verified
        set_transient('mwf_email_verified_' . md5($email), true, HOUR_IN_SECONDS);
        delete_transient('mwf_email_verification_' . md5($email));
        wp_send_json_success('Email verified successfully');
    } else {
        wp_send_json_error('Invalid or expired verification code');
    }
}

// Prevent registration/checkout without verified email
add_action('woocommerce_register_form_start', 'mwf_add_email_verification_fields');
function mwf_add_email_verification_fields() {
    ?>
    <div id="mwf-email-verification-container" style="display: none;">
        <p>
            <label for="mwf_verification_code"><?php _e('Email Verification Code', 'woocommerce'); ?> <span class="required">*</span></label>
            <input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="mwf_verification_code" id="mwf_verification_code" placeholder="Enter 6-digit code" maxlength="6" />
        </p>
        <p>
            <button type="button" id="mwf-resend-verification" class="button">Resend Code</button>
            <span id="mwf-verification-status"></span>
        </p>
    </div>
    <?php
}

// Validate email verification before processing registration
add_action('woocommerce_register_post', 'mwf_validate_email_verification', 10, 3);
function mwf_validate_email_verification($username, $email, $validation_errors) {
    // Check if email is verified
    $is_verified = get_transient('mwf_email_verified_' . md5($email));
    
    if (!$is_verified) {
        $validation_errors->add('email_not_verified', 'Please verify your email address before creating an account.');
    }
    
    return $validation_errors;
}
