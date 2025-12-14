<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<tr id="columns_<?php echo esc_attr($key);?>">
	<td>
		<input type="checkbox" name="columns_key[]" class="columns_key wt_iew_mapping_checkbox_sub" value="<?php echo esc_attr($key);?>" <?php echo ($checked==1 ? 'checked' : ''); ?>>
	</td>
	<td>
		<label class="wt_iew_mapping_column_label"><?php echo esc_html($label);?></label>
	</td>
	<td>
		<input type="hidden" name="columns_val[]" class="columns_val" value="<?php echo esc_attr($val);?>" data-type="<?php echo esc_attr($type);?>">
		<span data-wt_iew_popover="1" data-title="" data-content-container=".wt_iew_mapping_field_editor_container" class="wt_iew_mapping_field_val"><?php echo esc_html($val);?></span>		
	</td>
	<td>
		<span style="margin-left:20px;cursor:pointer" data-wt_iew_popover="1" data-title="" data-content-container=".wt_iew_mapping_field_editor_container" class="dashicons dashicons-edit wt-iew-tips" data-wt-iew-tip="<span class='wt_iew_tooltip_span'><?php esc_html_e('Use expression', 'users-customers-import-export-for-wp-woocommerce');?></span>"></span>
	</td>	
</tr>