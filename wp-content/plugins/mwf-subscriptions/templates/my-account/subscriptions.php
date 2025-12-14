<?php
/**
 * My Account Subscriptions List
 * 
 * @var array $subscriptions
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<h2><?php esc_html_e('My Subscriptions', 'mwf-subscriptions'); ?></h2>

<?php if (empty($subscriptions)): ?>
    <div class="woocommerce-Message woocommerce-Message--info woocommerce-info">
        <p><?php esc_html_e('You have no active subscriptions.', 'mwf-subscriptions'); ?></p>
    </div>
<?php else: ?>
    <table class="shop_table shop_table_responsive my_account_subscriptions">
        <thead>
            <tr>
                <th class="subscription-id"><?php esc_html_e('Subscription', 'mwf-subscriptions'); ?></th>
                <th class="subscription-status"><?php esc_html_e('Status', 'mwf-subscriptions'); ?></th>
                <th class="subscription-next-payment"><?php esc_html_e('Next Payment', 'mwf-subscriptions'); ?></th>
                <th class="subscription-total"><?php esc_html_e('Total', 'mwf-subscriptions'); ?></th>
                <th class="subscription-actions"><?php esc_html_e('Actions', 'mwf-subscriptions'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($subscriptions as $subscription): ?>
                <tr class="subscription">
                    <td class="subscription-id" data-title="<?php esc_attr_e('Subscription', 'mwf-subscriptions'); ?>">
                        <a href="<?php echo esc_url(wc_get_account_endpoint_url('mwf-view-subscription') . '/' . $subscription['id']); ?>">
                            #<?php echo esc_html($subscription['id']); ?> - <?php echo esc_html($subscription['product_name']); ?>
                        </a>
                        <?php if (!empty($subscription['variation_name'])): ?>
                            <br><small class="variation-name"><?php echo esc_html($subscription['variation_name']); ?></small>
                        <?php endif; ?>
                    </td>
                    <td class="subscription-status" data-title="<?php esc_attr_e('Status', 'mwf-subscriptions'); ?>">
                        <span class="subscription-status-badge status-<?php echo esc_attr($subscription['status']); ?>">
                            <?php echo esc_html(ucfirst($subscription['status'])); ?>
                        </span>
                    </td>
                    <td class="subscription-next-payment" data-title="<?php esc_attr_e('Next Payment', 'mwf-subscriptions'); ?>">
                        <time datetime="<?php echo esc_attr($subscription['next_billing_date']); ?>">
                            <?php echo esc_html(date_i18n(wc_date_format(), strtotime($subscription['next_billing_date']))); ?>
                        </time>
                    </td>
                    <td class="subscription-total" data-title="<?php esc_attr_e('Total', 'mwf-subscriptions'); ?>">
                        <span class="amount"><?php echo wc_price($subscription['billing_amount']); ?></span>
                        <small>/ <?php echo esc_html($subscription['billing_period']); ?></small>
                    </td>
                    <td class="subscription-actions" data-title="<?php esc_attr_e('Actions', 'mwf-subscriptions'); ?>">
                        <a href="<?php echo esc_url(wc_get_account_endpoint_url('mwf-view-subscription') . '/' . $subscription['id']); ?>" class="woocommerce-button button view">
                            <?php esc_html_e('View Details', 'mwf-subscriptions'); ?>
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<style>
.my_account_subscriptions .variation-name {
    color: #666;
    font-style: italic;
}

.subscription-status-badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 3px;
    font-size: 0.85em;
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

.subscription-actions .button {
    margin-right: 5px;
    margin-bottom: 5px;
}

.subscription-actions .button.manage {
    background-color: #96588a;
    border-color: #96588a;
}

.subscription-actions .button.manage:hover {
    background-color: #7a4670;
    border-color: #7a4670;
}

@media screen and (max-width: 768px) {
    .my_account_subscriptions td.subscription-actions {
        text-align: left;
    }
    
    .subscription-actions .button {
        display: inline-block;
        width: auto;
    }
}
</style>
