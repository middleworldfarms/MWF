<?php
if (!defined('ABSPATH')) {
    exit;
}
$wf_admin_view_path=WT_P_IEW_PLUGIN_PATH.'admin/views/';
$wf_img_path=WT_P_IEW_PLUGIN_URL.'images/';
?>
<div class="wrap" id="<?php echo esc_attr(WT_IEW_PLUGIN_ID_BASIC);?>">
    <h2 class="wp-heading-inline">
    <?php esc_html_e('Import Export for WooCommerce', 'product-import-export-for-woo');?>
    </h2>
    <div class="nav-tab-wrapper wp-clearfix wt-iew-tab-head">
        <?php
        $tab_head_arr=array(
            'wt-advanced'=>esc_html__('General', 'product-import-export-for-woo'),
            'wt-help'=>esc_html__('Help Guide', 'product-import-export-for-woo'),
            'wt-pro-upgrade'=>esc_html__('Pro Upgrade', 'product-import-export-for-woo'),
			'wt-other-solutions' => esc_html__('Other Solutions', 'product-import-export-for-woo')
        );
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce verification not required.
        if(isset($_GET['debug']))
        {
            $tab_head_arr['wt-debug']='Debug';
        }
        Wt_Import_Export_For_Woo_Basic::generate_settings_tabhead($tab_head_arr);
        ?>
    </div>
    <div class="wt-iew-tab-container">
        <?php
        //inside the settings form
        $setting_views_a=array(
            'wt-advanced'=>'admin-settings-advanced.php',        
        );

        //outside the settings form
        $setting_views_b=array(          
            'wt-help'=>'admin-settings-help.php',
			'wt-other-solutions'=>'admin-settings-other-solutions.php'
        );
        $setting_views_b['wt-pro-upgrade']='admin-settings-marketing.php';
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce verification not required.
        if(isset($_GET['debug']))
        {
            $setting_views_b['wt-debug']='admin-settings-debug.php';
        }
        ?>
        <form method="post" class="wt_iew_settings_form_basic">
            <?php
            // Set nonce:
            if (function_exists('wp_nonce_field'))
            {
                wp_nonce_field(WT_IEW_PLUGIN_ID_BASIC);
            }
            foreach ($setting_views_a as $target_id=>$value) 
            {
                $settings_view=$wf_admin_view_path.$value;
                if(file_exists($settings_view))
                {
                    include $settings_view;
                }
            }
            ?>
            <?php 
            //settings form fields for module
            do_action('wt_iew_plugin_settings_form');?>           
        </form>
        <?php
        foreach ($setting_views_b as $target_id=>$value) 
        {
            $settings_view=$wf_admin_view_path.$value;
            if(file_exists($settings_view))
            {
                include $settings_view;
            }
        }
        ?>
        <?php do_action('wt_iew_plugin_out_settings_form');?> 
    </div>
    <?php //include $wf_admin_view_path."market.php";  ?>
</div>

<?php

include $wf_admin_view_path."admin-header-and-help.php";

?>