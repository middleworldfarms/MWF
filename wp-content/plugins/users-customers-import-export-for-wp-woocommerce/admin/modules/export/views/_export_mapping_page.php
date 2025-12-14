<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wt_iew_export_main">
	<p><?php echo esc_html($step_info['description']); ?></p>
	<div class="meta_mapping_box">
		<div class="meta_mapping_box_hd_nil wt_iew_noselect">
			<?php esc_html_e('Default fields', 'users-customers-import-export-for-wp-woocommerce'); ?>
			<span class="meta_mapping_box_selected_count_box"><span class="meta_mapping_box_selected_count_box_num">0</span> <?php esc_html_e(' columns(s) selected', 'users-customers-import-export-for-wp-woocommerce'); ?></span>
		</div>
		<div style="clear:both;"></div>
		<div class="meta_mapping_box_con" data-sortable="0" data-loaded="1" data-field-validated="0" data-key="" style="display:inline-block;">
			<table class="wt-iew-mapping-tb wt-iew-exporter-default-mapping-tb">
				<thead>
					<tr>
			    		<th>
			    			<input type="checkbox" name="" class="wt_iew_mapping_checkbox_main">
			    		</th>
			    		<th width="35%"><?php esc_html_e('Column', 'users-customers-import-export-for-wp-woocommerce'); ?></th>
			    		<th><?php esc_html_e('Column name', 'users-customers-import-export-for-wp-woocommerce'); ?></th>
			    	</tr>
				</thead>
				<tbody>
				<?php
				$draggable_tooltip=__("Drag to rearrange the columns", 'users-customers-import-export-for-wp-woocommerce');
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
							<?php esc_html_e('No fields found.', 'users-customers-import-export-for-wp-woocommerce'); ?>
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
                $user_module = new Wt_Import_Export_For_Woo_basic_User();
                if (!$user_module->has_hidden_meta_keys()) {
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
            $banner_html = '';

            if ( 'hidden_meta' === $mapping_enabled_field_key && ! empty( $mapping_enabled_field['banner_html'] ) ) {
                $data_loaded = 1;
                $banner_html = $mapping_enabled_field['banner_html'];
            }
            ?>
            <div class="meta_mapping_box">
                <div class="meta_mapping_box_hd wt_iew_noselect">
                    <span class="dashicons dashicons-arrow-right"></span>
                    <?php echo esc_html($mapping_enabled_field[0]);?>
                    <?php if( 'Hidden meta' == trim( $mapping_enabled_field[0] ) ): ?>
                    <span class="premium-badge" style="padding:2px 4px 2px 7px;width: 77px;height: 20px;top: 180px;left: 380px;border-radius: 10px;border: 0.5px solid #F2E971;background-color:#FFF29B;font-family: Inter;font-weight: 500;font-size: 11px;line-height: 100%;letter-spacing: 0%;text-align: center;"> Premium 💎 </span>
                    <?php endif; ?>
                    <span class="meta_mapping_box_selected_count_box"><span class="meta_mapping_box_selected_count_box_num">0</span> <?php esc_html_e(' columns(s) selected', 'users-customers-import-export-for-wp-woocommerce'); ?></span>
                </div>
                <div style="clear:both;"></div>
                <div class="meta_mapping_box_con" data-sortable="0" data-loaded="<?php echo esc_attr($data_loaded); ?>" data-field-validated="0" data-key="<?php echo esc_attr($mapping_enabled_field_key);?>">
                    <?php 
                    if ( ! empty( $banner_html ) ) {
                        echo wp_kses_post($banner_html);
                    }
                    ?>
                </div>
            </div>
			<div style="clear:both;"></div>
			<?php
		}
	}
	?>
</div>