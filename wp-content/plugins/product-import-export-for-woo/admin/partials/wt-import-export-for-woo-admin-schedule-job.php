<?php
if (!defined('ABSPATH')) {
    exit;
}
$wf_admin_view_path = WT_P_IEW_PLUGIN_PATH . 'admin/views/';
$wf_img_path = WT_P_IEW_PLUGIN_URL . 'images/';
?>
<style type="text/css">
    .wt_iew_cta_header_job {
        font-size: 14px; color: black;
    }
    .wt_content_icon {
        width: 820px; height: 380px; border-radius: 10px; background-color:#FFFFFF; padding: 40px;position: relative;
    }
    .wt_content_icon_image {
        display: flex; align-items: center;;
    }
    .wt_content_review {
        display: flex; align-items: center;margin-left: 11px;margin-top:2px;;
    }
    .wt_scheduled_icon {
        position: absolute; top: 15px; right: 0px;
    }
    .wt_listout_features {
        list-style-type: none; padding: 0; font-size: 14px;margin-top: 40px;;
    }
    .wt_support_footer {
        background-color: #F5F9FF; width: 100%; max-width: 800px; height: auto; margin-top: 35px; border-radius: 6px; padding: 10px;
    }
    .wt_support_inside_divider {
        display: flex; justify-content: space-between; align-items: center;
    }


</style>
<div class="wrap" id="<?php echo esc_attr(WT_IEW_PLUGIN_ID_BASIC); ?>">
    <h2 class="wp-heading-inline" style="font-weight:600;">
        <?php echo esc_html__('Automate Using Scheduled Imports and Exports', 'product-import-export-for-woo'); ?>
    </h2>
    <p class="wt_iew_cta_header_job">
        <?php echo esc_html__('Escape from the troubles of manually importing and exporting data on your WooCommerce Store and enjoy the power of scheduled ', 'product-import-export-for-woo'); ?><br>
        <?php echo esc_html__('automation.', 'product-import-export-for-woo'); ?>
    </p>

    <div class="wt_content_icon">
        <!-- Image, Title, and Rating in one line -->
        <div class="wt_content_icon_image">
            <!-- Image on the left -->
            <img src="<?php echo esc_url(WT_P_IEW_PLUGIN_URL . 'assets/images/other_solutions/product-import-export-plugin.png'); ?>"
                alt="<?php echo esc_attr__('Product Import Export Plugin Icon', 'product-import-export-for-woo'); ?>"
                style="width: 40px; height: 40px; margin-right: 10px;">

            <!-- Title and Rating container -->
            <div>
                <!-- Title -->
                <h3 style="margin: 0;margin-left: 12px;"><?php echo esc_html__('Product Import Export Plugin For WooCommerce', 'product-import-export-for-woo'); ?></h3>

                <!-- Star rating with green bulb icons and 4.8 rating aligned next to the title -->
                <div class="wt_content_review">
                    <!-- star icons for rating -->
                    <?php for ($i = 0; $i < 5; $i++) : ?>
                        <img src="<?php echo esc_url(WT_P_IEW_PLUGIN_URL . 'assets/images/wt_review_star.svg'); ?>"
                            alt="<?php echo esc_attr__('wt_review_star', 'product-import-export-for-woo'); ?>"
                            style="margin-right: 8px;height:12px;">
                    <?php endfor; ?>
                    <!-- Rating score -->
                    <span style="font-size: 14px; margin-left: 5px;">4.8</span>
                </div>
            </div>
        </div>

        <!-- Scheduled Job Icon on the top right corner -->
        <div class="wt_scheduled_icon">
            <img src="<?php echo esc_url(WT_P_IEW_PLUGIN_URL . 'assets/images/wt_frame_scheduled_job.svg'); ?>"
                alt="<?php echo esc_attr__('Scheduled Job Icon', 'product-import-export-for-woo'); ?>"
                style="width: 310px;height: 359px;">
        </div>

        <!-- Feature listing below the title and rating -->
        <div style="margin-top: 20px;">
            <ul class="wt_listout_features">
                <!-- First list item -->
                <?php
                function render_list_item($text)
                {
                    $icon_url = esc_url(WT_P_IEW_PLUGIN_URL . 'assets/images/wt_tick_mark.svg');
                    echo '<li style="display: flex; align-items: center; margin-bottom: 20px;">';
                    echo '<img src="' . esc_url($icon_url) . '" alt="' . esc_attr__('Check Icon', 'product-import-export-for-woo') . '" style="width: 13px; height: 16px; margin-right: 10px;color: black;">';
                    echo esc_html($text);
                    echo '</li>';
                }

                $list_items = [
                    esc_html__('Automate regular import/export jobs with ease', 'product-import-export-for-woo'),
                    esc_html__('Run scheduled imports and exports in the background', 'product-import-export-for-woo'),
                    esc_html__('Choose your schedule: daily, weekly, monthly, or custom intervals', 'product-import-export-for-woo'),
                    esc_html__('Choose your ideal start time for maximum efficiency', 'product-import-export-for-woo'),
                    esc_html__('Supports both WordPress and Server Cron', 'product-import-export-for-woo')
                ];

                foreach ($list_items as $item) {
                    render_list_item($item);
                }
                ?>
            </ul>
        </div>

        <!-- Checkout button -->
        <div style="margin-top: 30px;">
            <a href="<?php echo esc_url('https://www.webtoffee.com/product/product-import-export-woocommerce/?utm_source=free_plugin_scheduling&utm_medium=basic_revamp&utm_campaign=Product_Import_Export' . WT_P_IEW_VERSION); ?>" target="_blank" class="button button-primary" style="background: #1665FF;background-color: #1665FF;border-radius: 6px;width: 168px;height: 40px;display: flex;justify-content: center; align-items: center; font-weight:600;">
                <?php echo esc_html__('Checkout Plugin', 'product-import-export-for-woo'); ?>
            </a>
        </div>

        <!-- Fast and Priority Support, 24/7 Customer Service -->
        <div class="wt_support_footer">
            <div class="wt_support_inside_divider">
                <!-- Fast and Priority Support -->
                <div style="display: flex; align-items: center;">
                    <img src="<?php echo esc_url(WT_P_IEW_PLUGIN_URL . 'assets/images/wt-green-headphone.svg'); ?>" alt="<?php echo esc_attr__('Headphone Icon', 'product-import-export-for-woo'); ?>" style="width: 20px; height: 20px; margin-right: 10px;">
                    <span style="font-size: 14px; font-weight: 600;"><?php echo esc_html__('Fast and Priority Support', 'product-import-export-for-woo'); ?></span>
                </div>

                <!-- Divider -->
                <div style="width: 1px; height: 30px; background-color: #CAD7EA; margin: 0 20px;"></div>

                <!-- 24/7 Customer Service -->
                <div style="display: flex; align-items: center;">
                    <img src="<?php echo esc_url(WT_P_IEW_PLUGIN_URL . 'assets/images/wt-blue-dollar.svg'); ?>" alt="<?php echo esc_attr__('Dollar Icon', 'product-import-export-for-woo'); ?>" style="width: 20px; height: 20px; margin-right: 10px;">
                    <span style="font-size: 14px; font-weight: 600;"><?php echo esc_html__('30 Day Money Back Guarantee', 'product-import-export-for-woo'); ?></span>
                </div>

                <!-- Divider -->
                <div style="width: 1px; height: 30px; background-color: #CAD7EA; margin: 0 20px;"></div>

                <!-- 99% Satisfaction Rating -->
                <div style="display: flex; align-items: center;">
                    <img src="<?php echo esc_url(WT_P_IEW_PLUGIN_URL . 'assets/images/wt-red-heart.svg'); ?>" alt="<?php echo esc_attr__('Heart Icon', 'product-import-export-for-woo'); ?>" style="width: 20px; height: 20px; margin-right: 10px;">
                    <span style="font-size: 14px; font-weight: 600;"><?php echo esc_html__('99% Satisfaction rating', 'product-import-export-for-woo'); ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
include $wf_admin_view_path."admin-header-and-help.php";
?>