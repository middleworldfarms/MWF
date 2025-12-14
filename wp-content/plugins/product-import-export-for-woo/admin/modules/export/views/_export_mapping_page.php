<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wt_iew_export_main">
	<p><?php echo esc_html($step_info['description']); ?></p>
	<div class="meta_mapping_box">
		<div class="meta_mapping_box_hd wt_iew_noselect">
			<span class="dashicons dashicons-arrow-down"></span>
			<?php esc_html_e('Default fields', 'product-import-export-for-woo');?>
			<span class="meta_mapping_box_selected_count_box"><span class="meta_mapping_box_selected_count_box_num">0</span> <?php esc_html_e(' columns(s) selected', 'product-import-export-for-woo'); ?></span>
		</div>
		<div style="clear:both;"></div>
		<div class="meta_mapping_box_con" data-sortable="0" data-loaded="1" data-field-validated="0" data-key="" style="display:inline-block;">
			<table class="wt-iew-mapping-tb wt-iew-exporter-default-mapping-tb">
				<thead>
					<tr>
			    		<th>
			    			<input type="checkbox" name="" class="wt_iew_mapping_checkbox_main">
			    		</th>
			    		<th width="35%"><?php esc_html_e('Column', 'product-import-export-for-woo');?></th>
			    		<th><?php esc_html_e('Column name', 'product-import-export-for-woo');?></th>
			    	</tr>
				</thead>
				<tbody>
				<?php
				$draggable_tooltip=__("Drag to rearrange the columns", 'product-import-export-for-woo');
				$tr_count=0;
				foreach($form_data_mapping_fields as $key=>$val)
				{
					if(isset($mapping_fields[$key]))
					{
						$label=$mapping_fields[$key];
						include "_export_mapping_tr_html.php";
					  	unset($mapping_fields[$key]); //remove the field from default list
					  	$tr_count++;
					}	
				}
				if(count($mapping_fields)>0)
				{
					foreach($mapping_fields as $key=>$label)
					{
						$disable_mapping_fields = apply_filters( 'wt_ier_disable_mapping_fields', array( 'aov', 'total_spent'));
						if( in_array( $key, $disable_mapping_fields )){
							$val = array($key, 0); //disable the field
						}else{
							$val = array($key, 1); //enable the field		
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
		</div>
	</div>
	<div style="clear:both;"></div>
	<?php
	if($this->mapping_enabled_fields)
	{
		foreach($this->mapping_enabled_fields as $mapping_enabled_field_key=>$mapping_enabled_field)
		{
			$mapping_enabled_field=(!is_array($mapping_enabled_field) ? array($mapping_enabled_field, 0) : $mapping_enabled_field);
			
			// Skip hidden_meta section if there are no hidden meta keys
			if ($mapping_enabled_field_key === 'hidden_meta') {
				// Check if there are actually hidden meta keys
				$product_module = new Wt_Import_Export_For_Woo_Basic_Product();
				if (!$product_module->has_hidden_meta_keys()) {
					continue;
				}
			}
			
			if(count($form_data_mapping_enabled_fields)>0)
			{
				if(in_array($mapping_enabled_field_key, $form_data_mapping_enabled_fields))
				{
					$mapping_enabled_field[1]=1;
				}else
				{
					$mapping_enabled_field[1]=0;
				}
			}

			$data_loaded = 0;

			?>
			<div class="meta_mapping_box">
				<div class="meta_mapping_box_hd wt_iew_noselect">
					<span class="dashicons dashicons-arrow-right"></span>
					<?php echo esc_html($mapping_enabled_field[0]);?>
					<?php if( 'hidden_meta' === $mapping_enabled_field_key ): ?>
					<span class="premium-badge" style="padding:2px 4px;width: 77px;height: 20px;top: 180px;left: 380px;border-radius: 10px;border: 0.5px solid #F2E971;background-color:#FFF29B;font-family: system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;font-weight: 500;font-size: 11px;line-height: 100%;letter-spacing: 0%;text-align: center;"> Premium 💎 </span>
					<?php endif; ?>
					<span class="meta_mapping_box_selected_count_box"><span class="meta_mapping_box_selected_count_box_num">0</span> <?php esc_html_e(' columns(s) selected', 'product-import-export-for-woo'); ?></span>
				</div>
				<div style="clear:both;"></div>
				<div class="meta_mapping_box_con" data-sortable="0" data-loaded="<?php echo esc_attr($data_loaded); ?>" data-field-validated="0" data-key="<?php echo esc_attr($mapping_enabled_field_key);?>"></div>
			</div>
			<div style="clear:both;"></div>
			<?php
		}
	}
	?>
</div>