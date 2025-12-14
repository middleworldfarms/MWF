<?php
/**
 * Main view file of export section
 *
 * @link            
 *
 * @package  Wt_Import_Export_For_Woo
 */
if (!defined('ABSPATH')) {
    exit;
}
?>
<?php
do_action('wt_iew_exporter_before_head');
$wf_admin_view_path=plugin_dir_path(WT_U_IEW_PLUGIN_FILENAME).'admin/views/';
?>
<style type="text/css">
.wt_iew_export_step{ display:none; }
.wt_iew_export_step_loader{ width:100%; height:400px; text-align:center; line-height:400px; font-size:14px; }
.wt_iew_export_step_main{ float:left; box-sizing:border-box; padding:15px; padding-bottom:0px; width:95%; margin:30px 2.5%; background:#fff; box-shadow:0px 2px 2px #ccc; border:solid 1px #efefef; }
.wt_iew_export_main{ padding:20px 0px; }
.wt_iew_file_ext_info_td{ vertical-align:top !important; }
.wt_iew_file_ext_info{ display:inline-block; margin-top:3px; }
.wt-something-went-wrong-wrap{position: absolute; margin-top: 150px; left:25%;color:#FFF;width:450px;height:275px;background: #FFF;padding: 25px; text-align: center;border: 1px solid #B32D2E;border-radius: 10px;box-shadow: 0px 0px 2px 2px #cdc8c8;}
</style>
<?php
Wt_Iew_IE_Basic_Helper::debug_panel($this->module_base);
?>
<?php include WT_U_IEW_PLUGIN_PATH."/admin/views/_save_template_popup.php"; ?>


<?php
	if($requested_rerun_id>0 && $this->rerun_id==0)
	{
		?>
		<div class="wt_iew_warn wt_iew_rerun_warn">
			<?php esc_html_e('Unable to handle Re-Run request.', 'users-customers-import-export-for-wp-woocommerce'); ?>
		</div>
		<?php
	}
?>

<div class="wt_iew_loader_info_box"></div>
<div class="wt_iew_overlayed_loader"></div>
<div class="wt_iew_export_step_main_wrapper" style="width:68%; float: left">
	
	
	<div class="wt-something-went-wrong" style="position:relative;display:none;">		
		<div class="wt-something-went-wrong-wrap">			
			<p class="wt_iew_popup_close" style="float:right;margin-top: -15px !important;margin-right: -15px !important;line-height: 0;"><a href="javascript:void(0)"><img src="<?php echo esc_url(WT_U_IEW_PLUGIN_URL.'/assets/images/wt-close-button.png');?>"/></a></p>
			<img src="<?php echo esc_url(WT_U_IEW_PLUGIN_URL.'/assets/images/wt-error-icon.png');?>"/>
			<h3><?php esc_html_e('Something went wrong', 'users-customers-import-export-for-wp-woocommerce'); ?></h3>
			<p style="color:#000;text-align: left;"><?php esc_html_e('We are unable to complete your request.Try reducing the import batch count to 5 or less and increasing the Maximum execution time in the ', 'users-customers-import-export-for-wp-woocommerce'); ?><a target="_blank" href="<?php echo esc_url(admin_url('admin.php?page=wt_import_export_for_woo_basic')); ?>"><?php esc_html_e('General settings', 'users-customers-import-export-for-wp-woocommerce'); ?></a>.</p>
			<p style="color:#000;text-align: left;"><?php esc_html_e(' If not resolved, contact the', 'users-customers-import-export-for-wp-woocommerce');?> <a target="_blank" href="https://www.webtoffee.com/contact/"><?php esc_html_e('support team', 'users-customers-import-export-for-wp-woocommerce'); ?></a> <?php esc_html_e('with the', 'users-customers-import-export-for-wp-woocommerce');?> <a target="_blank" href="<?php echo esc_url(admin_url('admin.php?page=wc-status&tab=logs')); ?>"><?php esc_html_e('WooCommerce fatal error log', 'users-customers-import-export-for-wp-woocommerce'); ?></a>, <?php esc_html_e('if any', 'users-customers-import-export-for-wp-woocommerce'); ?>.</p>
			<br/>
			<a href="javascript:void(0)" onclick='wt_iew_basic_export.refresh_export_page();' class="button button-primary"><?php esc_html_e('Try again', 'users-customers-import-export-for-wp-woocommerce'); ?></a>
		</div>
	</div>	
	
	
<div class="wt_iew_export_step_main" style = "width:100%; float: left">
	<?php
	foreach($this->steps as $stepk=>$stepv)
	{
		?>
		<div class="wt_iew_export_step wt_iew_export_step_<?php echo esc_attr($stepk);?>" data-loaded="0"></div>
		<?php
	}
	?>	
</div>
	</div>
<?php
include $wf_admin_view_path."market.php";
include $wf_admin_view_path."admin-header-and-help.php";