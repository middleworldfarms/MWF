<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<table class="wt-iew-mapping-tb wt-iew-importer-meta-mapping-tb wt-iew-mapping-tb-imp" data-field-type="<?php echo esc_attr($meta_mapping_screen_field_key); ?>">
	<thead>
		<tr>
    		<th>
    			<?php 
    			$is_checked=$meta_mapping_screen_field_val['checked'];
    			$checked_attr=($is_checked==1 ? ' checked="checked"' : '');
    			?>
    			<input type="checkbox" name="" class="wt_iew_mapping_checkbox_main" <?php echo esc_attr($checked_attr); ?>>
    		</th>
    		<th width="35%"><span class="wt_iew_step_head_post_type_name"></span> <?php esc_html_e( 'fields', 'product-import-export-for-woo');?></th>
    		<th><?php esc_html_e('File columns', 'product-import-export-for-woo');?></th>
			<th><?php esc_html_e( 'Transform', 'product-import-export-for-woo');?></th>
    	</tr>
	</thead>
	<tbody>
		<?php
		$tr_count=0; 
		
		foreach($meta_mapping_screen_field_val['fields'] as $key=>$val_arr)
		{
			extract($val_arr);
			include "_import_mapping_tr_html.php";
			$tr_count++;
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