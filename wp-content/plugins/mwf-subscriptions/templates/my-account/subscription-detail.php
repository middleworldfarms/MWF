<?php
/**
 * Single Subscription Detail View
 * 
 * @var array $subscription
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="woocommerce-subscription-details">
    <h2>
        <?php printf(esc_html__('Subscription #%d', 'mwf-subscriptions'), $subscription['id']); ?>
        <span class="subscription-status-badge status-<?php echo esc_attr($subscription['status']); ?>">
            <?php echo esc_html(ucfirst($subscription['status'])); ?>
        </span>
    </h2>
    
    <table class="shop_table subscription_details">
        <tbody>
            <tr>
                <th><?php esc_html_e('Product:', 'mwf-subscriptions'); ?></th>
                <td><?php echo esc_html($subscription['product_name']); ?></td>
            </tr>
            <?php if (!empty($subscription['variation_name'])): ?>
            <tr>
                <th><?php esc_html_e('Variation:', 'mwf-subscriptions'); ?></th>
                <td><?php echo esc_html($subscription['variation_name']); ?></td>
            </tr>
            <?php endif; ?>
            <tr>
                <th><?php esc_html_e('Billing:', 'mwf-subscriptions'); ?></th>
                <td>
                    <strong><?php echo wc_price($subscription['billing_amount']); ?></strong>
                    <?php
                    printf(
                        esc_html__('every %d %s', 'mwf-subscriptions'),
                        $subscription['billing_interval'],
                        $subscription['billing_period'] . ($subscription['billing_interval'] > 1 ? 's' : '')
                    );
                    ?>
                </td>
            </tr>
            <tr>
                <th><?php esc_html_e('Delivery Day:', 'mwf-subscriptions'); ?></th>
                <td><?php echo esc_html(ucfirst($subscription['delivery_day'])); ?></td>
            </tr>
            <tr>
                <th><?php esc_html_e('Next Payment:', 'mwf-subscriptions'); ?></th>
                <td>
                    <time datetime="<?php echo esc_attr($subscription['next_billing_date']); ?>">
                        <?php echo esc_html(date_i18n(wc_date_format(), strtotime($subscription['next_billing_date']))); ?>
                    </time>
                </td>
            </tr>
            <?php if (!empty($subscription['last_billing_date'])): ?>
            <tr>
                <th><?php esc_html_e('Last Payment:', 'mwf-subscriptions'); ?></th>
                <td>
                    <time datetime="<?php echo esc_attr($subscription['last_billing_date']); ?>">
                        <?php echo esc_html(date_i18n(wc_date_format(), strtotime($subscription['last_billing_date']))); ?>
                    </time>
                </td>
            </tr>
            <?php endif; ?>
            <tr>
                <th><?php esc_html_e('Start Date:', 'mwf-subscriptions'); ?></th>
                <td>
                    <time datetime="<?php echo esc_attr($subscription['created_at']); ?>">
                        <?php echo esc_html(date_i18n(wc_date_format(), strtotime($subscription['created_at']))); ?>
                    </time>
                </td>
            </tr>
        </tbody>
    </table>
    
    <div class="subscription-actions" style="margin-top: 20px;">
        <?php 
        // Check if payment is due or failed
        $payment_due = !empty($subscription['payment_due']) && $subscription['payment_due'] === true;
        $next_payment_soon = !empty($subscription['next_billing_date']) && 
                            strtotime($subscription['next_billing_date']) <= strtotime('+3 days');
        ?>
        
        <?php if ($payment_due || $subscription['status'] === 'on-hold'): ?>
            <a href="<?php echo esc_url(wc_get_checkout_url() . '?pay_for_subscription=' . $subscription['id']); ?>" class="woocommerce-button button alt" style="background-color: #96588a; border-color: #96588a;">
                <?php esc_html_e('💳 Pay Now', 'mwf-subscriptions'); ?>
            </a>
        <?php endif; ?>
        
        <?php if ($subscription['status'] === 'active' || $subscription['status'] === 'paused'): ?>
            <?php if ($subscription['status'] === 'active'): ?>
                <button type="button" class="woocommerce-button button" id="pause-subscription-btn" data-subscription-id="<?php echo esc_attr($subscription['id']); ?>">
                    <?php esc_html_e('⏸ Pause Subscription', 'mwf-subscriptions'); ?>
                </button>
            <?php else: ?>
                <button type="button" class="woocommerce-button button alt" id="resume-subscription-btn" data-subscription-id="<?php echo esc_attr($subscription['id']); ?>">
                    <?php esc_html_e('▶ Resume Subscription', 'mwf-subscriptions'); ?>
                </button>
            <?php endif; ?>
            
            <button type="button" class="woocommerce-button button" id="change-plan-btn" data-subscription-id="<?php echo esc_attr($subscription['id']); ?>">
                <?php esc_html_e('📦 Change Box Size', 'mwf-subscriptions'); ?>
            </button>
            
            <button type="button" class="woocommerce-button button" id="change-frequency-btn" data-subscription-id="<?php echo esc_attr($subscription['id']); ?>">
                <?php esc_html_e('📅 Change Billing Frequency', 'mwf-subscriptions'); ?>
            </button>
            
            <button type="button" class="woocommerce-button button" id="change-delivery-btn" data-subscription-id="<?php echo esc_attr($subscription['id']); ?>">
                <?php esc_html_e('🚚 Change Delivery Method', 'mwf-subscriptions'); ?>
            </button>
            
            <button type="button" class="woocommerce-button button" id="cancel-subscription-btn" data-subscription-id="<?php echo esc_attr($subscription['id']); ?>" style="background-color: #a00; border-color: #a00;">
                <?php esc_html_e('✕ Cancel Subscription', 'mwf-subscriptions'); ?>
            </button>
        <?php endif; ?>
        
        <a href="<?php echo esc_url(wc_get_account_endpoint_url('mwf-subscriptions')); ?>" class="woocommerce-button button">
            <?php esc_html_e('← Back to Subscriptions', 'mwf-subscriptions'); ?>
        </a>
    </div>
    
    <!-- Pause Modal -->
    <div id="pause-modal" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center;">
        <div style="background:white; padding:30px; border-radius:5px; max-width:400px; width:90%;">
            <h3><?php esc_html_e('Pause Subscription', 'mwf-subscriptions'); ?></h3>
            <p><?php esc_html_e('When would you like to resume your subscription?', 'mwf-subscriptions'); ?></p>
            <input type="date" id="pause-until-date" min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" style="width:100%; padding:10px; margin:10px 0; border:1px solid #ddd;" />
            <div style="margin-top:20px; text-align:right;">
                <button type="button" class="button" id="pause-modal-cancel"><?php esc_html_e('Cancel', 'mwf-subscriptions'); ?></button>
                <button type="button" class="button alt" id="pause-modal-confirm"><?php esc_html_e('Pause Subscription', 'mwf-subscriptions'); ?></button>
            </div>
        </div>
    </div>
    
    <!-- Change Plan Modal -->
    <div id="change-plan-modal" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.5); z-index:9999; align-items:flex-start; justify-content:center; padding:10px; padding-top:200px; overflow-y:auto;">
        <div style="background:white; padding:15px; border-radius:5px; max-width:450px; width:100%; max-height:85vh; overflow-y:auto; margin:10px auto;">
            <h3 style="margin:0 0 10px 0; font-size:18px; line-height:1.3;"><?php esc_html_e('Change Box Size', 'mwf-subscriptions'); ?></h3>
            <p style="font-size:13px; margin:0 0 12px 0; line-height:1.4;"><?php esc_html_e('Select your new box size. The price change will apply at your next billing date.', 'mwf-subscriptions'); ?></p>
            
            <div style="margin:0;">
                <label style="display:block; margin-bottom:8px; padding:10px; border:2px solid #ddd; border-radius:4px; cursor:pointer;">
                    <input type="radio" name="new_plan" value="227231" style="margin-right:6px; vertical-align:middle;" />
                    <span style="font-size:14px; font-weight:600; line-height:1.3;">Single Person Box</span>
                    <span style="font-size:14px; line-height:1.3;"> - £10/wk</span><br/>
                    <small style="margin-left:20px; color:#666; font-size:11px; display:block; line-height:1.3; margin-top:2px;">1 person • Weekly</small>
                </label>
                
                <label style="display:block; margin-bottom:8px; padding:10px; border:2px solid #ddd; border-radius:4px; cursor:pointer;">
                    <input type="radio" name="new_plan" value="226492" style="margin-right:6px; vertical-align:middle;" />
                    <span style="font-size:14px; font-weight:600; line-height:1.3;">Couple's Box</span>
                    <span style="font-size:14px; line-height:1.3;"> - £15/wk</span><br/>
                    <small style="margin-left:20px; color:#666; font-size:11px; display:block; line-height:1.3; margin-top:2px;">2 people • Weekly</small>
                </label>
                
                <label style="display:block; margin-bottom:8px; padding:10px; border:2px solid #ddd; border-radius:4px; cursor:pointer;">
                    <input type="radio" name="new_plan" value="226496" style="margin-right:6px; vertical-align:middle;" />
                    <span style="font-size:14px; font-weight:600; line-height:1.3;">Small Family Box</span>
                    <span style="font-size:14px; line-height:1.3;"> - £22/wk</span><br/>
                    <small style="margin-left:20px; color:#666; font-size:11px; display:block; line-height:1.3; margin-top:2px;">3-4 people • Weekly</small>
                </label>
                
                <label style="display:block; margin-bottom:8px; padding:10px; border:2px solid #ddd; border-radius:4px; cursor:pointer;">
                    <input type="radio" name="new_plan" value="226499" style="margin-right:6px; vertical-align:middle;" />
                    <span style="font-size:14px; font-weight:600; line-height:1.3;">Large Family Box</span>
                    <span style="font-size:14px; line-height:1.3;"> - £25/wk</span><br/>
                    <small style="margin-left:20px; color:#666; font-size:11px; display:block; line-height:1.3; margin-top:2px;">5+ people • Weekly</small>
                </label>
            </div>
            
            <div style="margin-top:20px; text-align:right;">
                <button type="button" class="button" id="change-plan-modal-cancel"><?php esc_html_e('Cancel', 'mwf-subscriptions'); ?></button>
                <button type="button" class="button alt" id="change-plan-modal-confirm"><?php esc_html_e('Change Box Size', 'mwf-subscriptions'); ?></button>
            </div>
        </div>
    </div>
    
    <!-- Change Billing Frequency Modal -->
    <div id="change-frequency-modal" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.5); z-index:9999; align-items:flex-start; justify-content:center; padding:10px; padding-top:200px; overflow-y:auto;">
        <div style="background:white; padding:15px; border-radius:5px; max-width:450px; width:100%; max-height:85vh; overflow-y:auto; margin:10px auto;">
            <h3 style="margin:0 0 10px 0; font-size:18px; line-height:1.3;"><?php esc_html_e('Change Billing Frequency', 'mwf-subscriptions'); ?></h3>
            <p style="font-size:13px; margin:0 0 12px 0; line-height:1.4;"><?php esc_html_e('Choose how often you\'d like to be billed. The price will update based on your selected frequency.', 'mwf-subscriptions'); ?></p>
            
            <div style="margin:0;">
                <label style="display:block; margin-bottom:8px; padding:10px; border:2px solid #ddd; border-radius:4px; cursor:pointer;">
                    <input type="radio" name="new_frequency" value="weekly" style="margin-right:6px; vertical-align:middle;" />
                    <span style="font-size:14px; font-weight:600; line-height:1.3;">Weekly Billing</span><br/>
                    <small style="margin-left:20px; color:#666; font-size:11px; display:block; line-height:1.3; margin-top:2px;">Billed every week • Delivery every week</small>
                </label>
                
                <label style="display:block; margin-bottom:8px; padding:10px; border:2px solid #ddd; border-radius:4px; cursor:pointer;">
                    <input type="radio" name="new_frequency" value="fortnightly" style="margin-right:6px; vertical-align:middle;" />
                    <span style="font-size:14px; font-weight:600; line-height:1.3;">Fortnightly Billing</span><br/>
                    <small style="margin-left:20px; color:#666; font-size:11px; display:block; line-height:1.3; margin-top:2px;">Billed every 2 weeks • Delivery every 2 weeks</small>
                </label>
                
                <label style="display:block; margin-bottom:8px; padding:10px; border:2px solid #ddd; border-radius:4px; cursor:pointer;">
                    <input type="radio" name="new_frequency" value="monthly" style="margin-right:6px; vertical-align:middle;" />
                    <span style="font-size:14px; font-weight:600; line-height:1.3;">Monthly Billing</span><br/>
                    <small style="margin-left:20px; color:#666; font-size:11px; display:block; line-height:1.3; margin-top:2px;">Billed once per month • Save on admin</small>
                </label>
                
                <label style="display:block; margin-bottom:8px; padding:10px; border:2px solid #ddd; border-radius:4px; cursor:pointer;">
                    <input type="radio" name="new_frequency" value="annual" style="margin-right:6px; vertical-align:middle;" />
                    <span style="font-size:14px; font-weight:600; line-height:1.3;">Annual Billing</span><br/>
                    <small style="margin-left:20px; color:#666; font-size:11px; display:block; line-height:1.3; margin-top:2px;">Billed once per year • Best value</small>
                </label>
            </div>
            
            <div style="margin-top:20px; text-align:right;">
                <button type="button" class="button" id="change-frequency-modal-cancel"><?php esc_html_e('Cancel', 'mwf-subscriptions'); ?></button>
                <button type="button" class="button alt" id="change-frequency-modal-confirm"><?php esc_html_e('Change Frequency', 'mwf-subscriptions'); ?></button>
            </div>
        </div>
    </div>
    
    <!-- Change Delivery Method Modal -->
    <div id="change-delivery-modal" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.5); z-index:9999; align-items:flex-start; justify-content:center; padding:10px; padding-top:200px; overflow-y:auto;">
        <div style="background:white; padding:15px; border-radius:5px; max-width:450px; width:100%; max-height:85vh; overflow-y:auto; margin:10px auto;">
            <h3 style="margin:0 0 10px 0; font-size:18px; line-height:1.3;"><?php esc_html_e('Change Delivery Method', 'mwf-subscriptions'); ?></h3>
            <p style="font-size:13px; margin:0 0 12px 0; line-height:1.4;"><?php esc_html_e('Switch between home delivery or collection. The price will update automatically based on your current billing frequency.', 'mwf-subscriptions'); ?></p>
            
            <div style="margin:0;">
                <label style="display:block; margin-bottom:8px; padding:10px; border:2px solid #ddd; border-radius:4px; cursor:pointer;">
                    <input type="radio" name="new_delivery_method" value="delivery" style="margin-right:6px; vertical-align:middle;" />
                    <span style="font-size:14px; font-weight:600; line-height:1.3;">🚚 Home Delivery</span><br/>
                    <small style="margin-left:20px; color:#666; font-size:11px; display:block; line-height:1.3; margin-top:2px;">Delivered to your door • Includes delivery charge</small>
                </label>
                
                <label style="display:block; margin-bottom:8px; padding:10px; border:2px solid #ddd; border-radius:4px; cursor:pointer;">
                    <input type="radio" name="new_delivery_method" value="collection" style="margin-right:6px; vertical-align:middle;" />
                    <span style="font-size:14px; font-weight:600; line-height:1.3;">📍 Collection Point</span><br/>
                    <small style="margin-left:20px; color:#666; font-size:11px; display:block; line-height:1.3; margin-top:2px;">Pick up from local point • Lower price</small>
                </label>
            </div>
            
            <div style="margin-top:20px; text-align:right;">
                <button type="button" class="button" id="change-delivery-modal-cancel"><?php esc_html_e('Cancel', 'mwf-subscriptions'); ?></button>
                <button type="button" class="button alt" id="change-delivery-modal-confirm"><?php esc_html_e('Change Delivery', 'mwf-subscriptions'); ?></button>
            </div>
        </div>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        var nonce = '<?php echo wp_create_nonce('mwf_subscriptions'); ?>';
        var currentPrice = <?php echo floatval($subscription['billing_amount']); ?>;
        var currentProductName = '<?php echo esc_js($subscription['product_name']); ?>';
        
        // Change plan button
        $('#change-plan-btn').on('click', function() {
            $('#change-plan-modal').css('display', 'flex');
            
            // Reset all labels first
            $('input[name="new_plan"]').closest('label').css({
                'border-color': '#ddd',
                'background-color': 'transparent'
            }).find('.current-plan-label').remove();
            
            // Detect and highlight current plan based on product name
            if (currentProductName.indexOf('Single Person') !== -1) {
                $('input[name="new_plan"][value="227231"]').closest('label').css({
                    'border-color': '#0073aa',
                    'background-color': '#f0f8ff'
                }).find('strong').append(' <span class="current-plan-label" style="color:#0073aa;">(Current)</span>');
            } else if (currentProductName.indexOf("Couple's") !== -1) {
                $('input[name="new_plan"][value="226492"]').closest('label').css({
                    'border-color': '#0073aa',
                    'background-color': '#f0f8ff'
                }).find('strong').append(' <span class="current-plan-label" style="color:#0073aa;">(Current)</span>');
            } else if (currentProductName.indexOf('Small Family') !== -1) {
                $('input[name="new_plan"][value="226496"]').closest('label').css({
                    'border-color': '#0073aa',
                    'background-color': '#f0f8ff'
                }).find('strong').append(' <span class="current-plan-label" style="color:#0073aa;">(Current)</span>');
            } else if (currentProductName.indexOf('Large Family') !== -1) {
                $('input[name="new_plan"][value="226499"]').closest('label').css({
                    'border-color': '#0073aa',
                    'background-color': '#f0f8ff'
                }).find('strong').append(' <span class="current-plan-label" style="color:#0073aa;">(Current)</span>');
            }
        });
        
        $('#change-plan-modal-cancel').on('click', function() {
            $('#change-plan-modal').hide();
        });
        
        $('#change-plan-modal-confirm').on('click', function() {
            var newPlanId = $('input[name="new_plan"]:checked').val();
            
            if (!newPlanId) {
                alert('<?php esc_html_e('Please select a box size.', 'mwf-subscriptions'); ?>');
                return;
            }
            
            if (!confirm('<?php esc_html_e('Change your box size? The new price will apply at your next delivery.', 'mwf-subscriptions'); ?>')) return;
            
            var $btn = $(this);
            $btn.prop('disabled', true).text('<?php esc_html_e('Processing...', 'mwf-subscriptions'); ?>');
            
            $.ajax({
                url: wc_add_to_cart_params.ajax_url,
                type: 'POST',
                data: {
                    action: 'mwf_change_plan',
                    subscription_id: $('#change-plan-btn').data('subscription-id'),
                    new_plan_id: newPlanId,
                    nonce: nonce
                },
                success: function(response) {
                    console.log('Change plan response:', response);
                    
                    if (response.success) {
                        var message = (response.data && response.data.message) ? response.data.message : '<?php esc_html_e('Box size changed successfully!', 'mwf-subscriptions'); ?>';
                        alert(message);
                        location.reload();
                    } else {
                        var errorMsg = (response.data && response.data.message) ? response.data.message : '<?php esc_html_e('Error changing box size.', 'mwf-subscriptions'); ?>';
                        alert(errorMsg);
                        $btn.prop('disabled', false).text('<?php esc_html_e('Change Box Size', 'mwf-subscriptions'); ?>');
                    }
                    $('#change-plan-modal').hide();
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.error('Change plan error:', textStatus, errorThrown);
                    console.error('Response text:', jqXHR.responseText);
                    alert('<?php esc_html_e('Network error. The Laravel backend needs to be updated first. Check browser console for details.', 'mwf-subscriptions'); ?>');
                    $btn.prop('disabled', false).text('<?php esc_html_e('Change Box Size', 'mwf-subscriptions'); ?>');
                    $('#change-plan-modal').hide();
                },
                timeout: 30000
            });
        });
        
        // Change billing frequency button
        $('#change-frequency-btn').on('click', function() {
            $('#change-frequency-modal').css('display', 'flex');
        });
        
        $('#change-frequency-modal-cancel').on('click', function() {
            $('#change-frequency-modal').hide();
        });
        
        $('#change-frequency-modal-confirm').on('click', function() {
            var newFrequency = $('input[name="new_frequency"]:checked').val();
            
            if (!newFrequency) {
                alert('<?php esc_html_e('Please select a billing frequency.', 'mwf-subscriptions'); ?>');
                return;
            }
            
            if (!confirm('<?php esc_html_e('Change your billing frequency? This will apply from your next billing date.', 'mwf-subscriptions'); ?>')) return;
            
            var $btn = $(this);
            $btn.prop('disabled', true).text('<?php esc_html_e('Processing...', 'mwf-subscriptions'); ?>');
            
            $.ajax({
                url: wc_add_to_cart_params.ajax_url,
                type: 'POST',
                data: {
                    action: 'mwf_change_frequency',
                    subscription_id: $('#change-frequency-btn').data('subscription-id'),
                    new_frequency: newFrequency,
                    nonce: nonce
                },
                success: function(response) {
                    if (response.success) {
                        alert(response.data.message || '<?php esc_html_e('Billing frequency changed successfully!', 'mwf-subscriptions'); ?>');
                        location.reload();
                    } else {
                        alert(response.data.message || '<?php esc_html_e('Error changing billing frequency.', 'mwf-subscriptions'); ?>');
                        $btn.prop('disabled', false).text('<?php esc_html_e('Change Frequency', 'mwf-subscriptions'); ?>');
                    }
                    $('#change-frequency-modal').hide();
                },
                error: function() {
                    alert('<?php esc_html_e('Network error. Please try again.', 'mwf-subscriptions'); ?>');
                    $btn.prop('disabled', false).text('<?php esc_html_e('Change Frequency', 'mwf-subscriptions'); ?>');
                    $('#change-frequency-modal').hide();
                }
            });
        });
        
        // Change delivery method button
        $('#change-delivery-btn').on('click', function() {
            $('#change-delivery-modal').css('display', 'flex');
        });
        
        $('#change-delivery-modal-cancel').on('click', function() {
            $('#change-delivery-modal').hide();
        });
        
        $('#change-delivery-modal-confirm').on('click', function() {
            var newDeliveryMethod = $('input[name="new_delivery_method"]:checked').val();
            
            if (!newDeliveryMethod) {
                alert('<?php esc_html_e('Please select a delivery method.', 'mwf-subscriptions'); ?>');
                return;
            }
            
            if (!confirm('<?php esc_html_e('Change your delivery method? This will apply from your next delivery.', 'mwf-subscriptions'); ?>')) return;
            
            var $btn = $(this);
            $btn.prop('disabled', true).text('<?php esc_html_e('Processing...', 'mwf-subscriptions'); ?>');
            
            $.ajax({
                url: wc_add_to_cart_params.ajax_url,
                type: 'POST',
                data: {
                    action: 'mwf_change_delivery_method',
                    subscription_id: $('#change-delivery-btn').data('subscription-id'),
                    new_delivery_method: newDeliveryMethod,
                    nonce: nonce
                },
                success: function(response) {
                    if (response.success) {
                        alert(response.data.message || '<?php esc_html_e('Delivery method changed successfully!', 'mwf-subscriptions'); ?>');
                        location.reload();
                    } else {
                        alert(response.data.message || '<?php esc_html_e('Error changing delivery method.', 'mwf-subscriptions'); ?>');
                        $btn.prop('disabled', false).text('<?php esc_html_e('Change Delivery', 'mwf-subscriptions'); ?>');
                    }
                    $('#change-delivery-modal').hide();
                },
                error: function() {
                    alert('<?php esc_html_e('Network error. Please try again.', 'mwf-subscriptions'); ?>');
                    $btn.prop('disabled', false).text('<?php esc_html_e('Change Delivery', 'mwf-subscriptions'); ?>');
                    $('#change-delivery-modal').hide();
                }
            });
        });
        
        // Resume subscription
        $('#resume-subscription-btn').on('click', function() {
            if (!confirm('<?php esc_html_e('Resume your subscription now?', 'mwf-subscriptions'); ?>')) return;
            
            var $btn = $(this);
            $btn.prop('disabled', true).text('<?php esc_html_e('Processing...', 'mwf-subscriptions'); ?>');
            
            $.post(wc_add_to_cart_params.ajax_url, {
                action: 'mwf_resume_subscription',
                subscription_id: $btn.data('subscription-id'),
                nonce: nonce
            }, function(response) {
                if (response.success) {
                    alert(response.message || '<?php esc_html_e('Subscription resumed successfully!', 'mwf-subscriptions'); ?>');
                    location.reload();
                } else {
                    alert(response.message || '<?php esc_html_e('Error resuming subscription.', 'mwf-subscriptions'); ?>');
                    $btn.prop('disabled', false).text('<?php esc_html_e('▶ Resume Subscription', 'mwf-subscriptions'); ?>');
                }
            });
        });
        
        // Pause subscription - show modal
        $('#pause-subscription-btn').on('click', function() {
            $('#pause-modal').css('display', 'flex');
        });
        
        $('#pause-modal-cancel').on('click', function() {
            $('#pause-modal').hide();
        });
        
        $('#pause-modal-confirm').on('click', function() {
            var pauseUntil = $('#pause-until-date').val();
            if (!pauseUntil) {
                alert('<?php esc_html_e('Please select a resume date.', 'mwf-subscriptions'); ?>');
                return;
            }
            
            var $btn = $(this);
            $btn.prop('disabled', true).text('<?php esc_html_e('Processing...', 'mwf-subscriptions'); ?>');
            
            $.post(wc_add_to_cart_params.ajax_url, {
                action: 'mwf_pause_subscription',
                subscription_id: $('#pause-subscription-btn').data('subscription-id'),
                pause_until: pauseUntil,
                nonce: nonce
            }, function(response) {
                if (response.success) {
                    alert(response.message || '<?php esc_html_e('Subscription paused successfully!', 'mwf-subscriptions'); ?>');
                    location.reload();
                } else {
                    alert(response.message || '<?php esc_html_e('Error pausing subscription.', 'mwf-subscriptions'); ?>');
                    $btn.prop('disabled', false).text('<?php esc_html_e('Pause Subscription', 'mwf-subscriptions'); ?>');
                }
            });
        });
        
        // Cancel subscription
        $('#cancel-subscription-btn').on('click', function() {
            if (!confirm('<?php esc_html_e('Are you sure you want to cancel this subscription?\n\nThis action cannot be undone.', 'mwf-subscriptions'); ?>')) return;
            
            var $btn = $(this);
            $btn.prop('disabled', true).text('<?php esc_html_e('Processing...', 'mwf-subscriptions'); ?>');
            
            $.post(wc_add_to_cart_params.ajax_url, {
                action: 'mwf_cancel_subscription',
                subscription_id: $btn.data('subscription-id'),
                confirm: 'yes',
                nonce: nonce
            }, function(response) {
                if (response.success) {
                    alert(response.message || '<?php esc_html_e('Subscription cancelled successfully.', 'mwf-subscriptions'); ?>');
                    location.reload();
                } else {
                    alert(response.message || '<?php esc_html_e('Error cancelling subscription.', 'mwf-subscriptions'); ?>');
                    $btn.prop('disabled', false).text('<?php esc_html_e('✕ Cancel Subscription', 'mwf-subscriptions'); ?>');
                }
            });
        });
    });
    </script>
    
    <?php if (!empty($subscription['renewal_orders'])): ?>
    <h3 style="margin-top: 30px;"><?php esc_html_e('Renewal History', 'mwf-subscriptions'); ?></h3>
    <table class="shop_table shop_table_responsive subscription_renewals">
        <thead>
            <tr>
                <th><?php esc_html_e('Date', 'mwf-subscriptions'); ?></th>
                <th><?php esc_html_e('Amount', 'mwf-subscriptions'); ?></th>
                <th><?php esc_html_e('Status', 'mwf-subscriptions'); ?></th>
                <th><?php esc_html_e('Order', 'mwf-subscriptions'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($subscription['renewal_orders'] as $order): ?>
            <tr>
                <td data-title="<?php esc_attr_e('Date', 'mwf-subscriptions'); ?>">
                    <time datetime="<?php echo esc_attr($order['date']); ?>">
                        <?php echo esc_html(date_i18n(wc_date_format(), strtotime($order['date']))); ?>
                    </time>
                </td>
                <td data-title="<?php esc_attr_e('Amount', 'mwf-subscriptions'); ?>">
                    <?php echo wc_price($order['amount']); ?>
                </td>
                <td data-title="<?php esc_attr_e('Status', 'mwf-subscriptions'); ?>">
                    <span class="order-status status-<?php echo esc_attr($order['status']); ?>">
                        <?php echo esc_html(ucfirst($order['status'])); ?>
                    </span>
                </td>
                <td data-title="<?php esc_attr_e('Order', 'mwf-subscriptions'); ?>">
                    <?php if (!empty($order['wordpress_order_id'])): ?>
                        <a href="<?php echo esc_url(wc_get_endpoint_url('view-order', $order['wordpress_order_id'], wc_get_page_permalink('myaccount'))); ?>">
                            #<?php echo esc_html($order['wordpress_order_id']); ?>
                        </a>
                    <?php else: ?>
                        <span class="no-order"><?php esc_html_e('N/A', 'mwf-subscriptions'); ?></span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<style>
.woocommerce-subscription-details h2 {
    display: flex;
    align-items: center;
    gap: 15px;
    flex-wrap: wrap;
}

.subscription-status-badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 3px;
    font-size: 0.75em;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.status-active {
    background: #c6e1c6;
    color: #2e4e2e;
}

.status-paused {
    background: #fff3cd;
    color: #856404;
}

.status-cancelled {
    background: #f8d7da;
    color: #721c24;
}

.status-expired {
    background: #e2e3e5;
    color: #383d41;
}

.subscription_details th {
    width: 30%;
}

.subscription-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.subscription-actions .button.alt {
    background-color: #96588a;
    border-color: #96588a;
}

.subscription-actions .button.alt:hover {
    background-color: #7a4670;
    border-color: #7a4670;
}

.subscription_renewals .order-status {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 3px;
    font-size: 0.85em;
}

.order-status.status-completed {
    background: #c6e1c6;
    color: #2e4e2e;
}

.order-status.status-pending {
    background: #fff3cd;
    color: #856404;
}

.order-status.status-failed {
    background: #f8d7da;
    color: #721c24;
}

.subscription_renewals .no-order {
    color: #999;
    font-style: italic;
}

@media screen and (max-width: 768px) {
    .woocommerce-subscription-details h2 {
        font-size: 1.2em;
    }
    
    .subscription_details th {
        width: 40%;
    }
    
    .subscription-actions {
        flex-direction: column;
    }
    
    .subscription-actions .button {
        width: 100%;
        text-align: center;
    }
}
</style>
