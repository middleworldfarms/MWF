<?php
if (!defined('ABSPATH')) {
    exit;
}

// Check if banner HTML should be displayed instead of table
if (isset($meta_mapping_screen_field_val['banner_html']) && !empty($meta_mapping_screen_field_val['banner_html'])) {
    echo wp_kses_post($meta_mapping_screen_field_val['banner_html']);
} else {
?>
<table class="wt-iew-mapping-tb wt-iew-exporter-meta-mapping-tb" data-field-type="<?php echo esc_attr($meta_mapping_screen_field_key); ?>">
	<thead>
		<tr>
    		<th>
    			<?php 
    			$is_checked=(isset($meta_mapping_screen_field_val['checked']) && $meta_mapping_screen_field_val['checked']==1 ? 1 : 0);
    			$checked_attr=($is_checked==1 ? ' checked="checked"' : '');
    			?>
    			<input type="checkbox" name="" class="wt_iew_mapping_checkbox_main" <?php echo esc_attr($checked_attr); ?>>
    		</th>
    		<th width="35%"><?php esc_html_e('Column', 'product-import-export-for-woo');?></th>
    		<th><?php esc_html_e('Column name', 'product-import-export-for-woo');?></th>
    	</tr>
	</thead>
	<tbody>
		<?php
		$tr_count=0; 
		
		if(isset($meta_mapping_screen_field_val['fields']) && is_array($meta_mapping_screen_field_val['fields']) && count($meta_mapping_screen_field_val['fields'])>0)
		{
			foreach($meta_mapping_screen_field_val['fields'] as $key=>$val)
			{
				$val=is_array($val) ? $val : array($val, 0);
				$label=$val[0];

				if(isset($current_meta_step_form_data[$key])) /* forma data/template data available */
				{
					$val=(is_array($current_meta_step_form_data[$key]) ? $current_meta_step_form_data[$key] : array($current_meta_step_form_data[$key], 1));
				}else
				{
					$val[1]=$is_checked; //parent is checked
				}

				include "_export_mapping_tr_html.php";
				$tr_count++;
			}
		}

		if($tr_count==0)
		{
			?>
			<tr>
				<td colspan="3" style="text-align:center;">
					<?php esc_html_e('No fields found.', 'product-import-export-for-woo'); ?>
				</td>
			</tr>
			<?php
		}
		?>
	</tbody>
</table>
<?php
}
?>   