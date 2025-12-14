<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wt_iew_export_main">
	<p><?php echo esc_html($step_info['description']); ?></p>
	<form class="wt_iew_export_advanced_form">
		<table class="form-table wt-iew-form-table wt-iew-export-filter-table">
			<?php
			Wt_Import_Export_For_Woo_Basic_Common_Helper::field_generator($advanced_screen_fields, $advanced_form_data);
			?>
		</table>
	</form>
</div>
<div class="wt_iew_suite_banner">
    <div class="wt_iew_suite_banner_border"></div>
    <p style="font-size: 13px; font-weight: 400; margin-top: -61px;margin-left: 13px; padding: 10px 10px;">
        <strong><?php echo esc_html__('💡 Did You Know?', 'users-customers-import-export-for-wp-woocommerce'); ?></strong> <?php echo esc_html__('Get advanced features like FTP/SFTP import, and support for XLSX, XLS, XML, and TXT files with our premium version.', 'users-customers-import-export-for-wp-woocommerce'); ?>
        <a href="<?php echo esc_url($link . WT_U_IEW_VERSION); ?>" style="color: blue;" target="_blank"><?php echo esc_html($text); ?></a>
    </p>
</div>
<?php

$file_int_field_tr_arr=array();
$file_int_field_tr_arr=apply_filters('wt_iew_exporter_file_into_fields_row_id_basic', $file_int_field_tr_arr);
?>
<script type="text/javascript">
var file_int_field_tr_arr=<?php echo wp_json_encode($file_int_field_tr_arr); ?>;
/* remote file modules can hook */
function wt_iew_set_file_into_fields(file_into)
{
	/* first hide all */
	if(file_int_field_tr_arr.length>0)
	{	
		jQuery(file_int_field_tr_arr.join(', ')).hide();
	}
//	wt_iew_toggle_schedule_btn(0); //hide scheduler btn if exists
	<?php
	do_action('wt_iew_exporter_file_into_js_fn');
	?>
}
<?php /*
function wt_iew_toggle_schedule_btn(state) /* show/hide cron button 
{
	<?php
	do_action('wt_iew_toggle_schedule_btn');
	?>
} */ ?>

/* custom action: other than export, save, update. Eg: schedule */
function wt_iew_custom_action_basic(ajx_dta, action, id)
{
	ajx_dta['item_type']=ajx_dta['to_export'];
	<?php
	do_action('wt_iew_custom_action_basic');
	?>
}
</script>