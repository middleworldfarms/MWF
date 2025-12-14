<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<?php
	$click_to_use=__('Click to use', 'product-import-export-for-woo');
?>
<script type="text/javascript">
	var wt_iew_file_head_default=<?php echo json_encode($file_heading_default_fields);?>;
	var wt_iew_file_head_meta=<?php echo json_encode($file_heading_meta_fields);?>;
</script>

<!-- Mapping field editor popup -->
<div class="wt_iew_mapping_field_editor_container" data-title="<?php esc_attr_e('Set value for column:', 'product-import-export-for-woo');?> <span class='wt_iew_target_column'></span>" data-module="import">
	<div class="wt_iew_mapping_field_editor">
		<div class="wt_iew_mapping_field_editor_top">			
			<p class="wt_iew_mapping_field_editor_info">
				<?php esc_html_e('If you want to compute or combine any column values during import you can do it below.', 'product-import-export-for-woo'); ?>
				(<a target="_blank" href="https://www.webtoffee.com/evaluation-field-in-product-export-import-plugin-for-woocommerce/"><?php esc_html_e('Learn More', 'product-import-export-for-woo'); ?>)</a>
				<br/><span style="font-size: 95%;color:#6e6d6d;"><b><?php esc_html_e(' Supported operators:', 'product-import-export-for-woo'); ?> +, *, /, -, ()</b></span><br/>
				<br/><i><span style="font-size: 95%;color:#6e6d6d;"><?php esc_html_e(' Example:', 'product-import-export-for-woo'); ?> [{stock}+5] or [{regular_price}+(9/100)*{regular_price}]</span></i>
			</p>
			<p class="wt_iew_mapping_field_editor_er"></p>
			<div class="wt_iew_mapping_field_editor_box">
				<textarea class="wt_iew_mapping_field_editor_expression"></textarea>
			</div>
		</div>
		<label class="wt_iew_mapping_popup_label"><?php esc_html_e('Input file columns', 'product-import-export-for-woo');?></label>	
		<div class="wt_iew_mapping_field_editor_box">
			<input type="text" class="wt_iew_mapping_field_editor_column_search" placeholder="<?php esc_attr_e('Type here to search', 'product-import-export-for-woo');?>"><span style="float: right;position: absolute;top: 15px; left: 25px;" class="dashicons dashicons-search wt-input-column-search"></span>
			<div class="wt_iew_mapping_field_selector_box">
				<ul class="wt_iew_mapping_field_selector">
					<?php
					foreach ($file_heading_default_fields as $key => $value) 
					{
						?>
					<li title="<?php echo esc_attr($click_to_use);?>" data-val="<?php echo esc_attr($key);?>"><?php echo esc_html($key);?><br/><span style="color:#cdb2b2;"><?php echo wp_kses_post(Wt_Iew_IE_Basic_Helper::wt_truncate($value, 80));?></span></li>
						<?php
					}
					foreach ($file_heading_meta_fields as $key => $value) 
					{
						?>
					<li title="<?php echo esc_attr($click_to_use);?>" data-val="<?php echo esc_attr($key);?>"><?php echo esc_html($key);?><br/><span style="color:#cdb2b2;"><?php echo wp_kses_post(Wt_Iew_IE_Basic_Helper::wt_truncate($value, 80));?></span></li>
						<?php
					}
					?>
				</ul>
				<div class="wt_iew_mapping_field_selector_no_column"><?php esc_html_e('No column found.', 'product-import-export-for-woo');?></div>	
			</div>
		</div>
		<div class="wt_iew_mapping_field_editor_bottom">
			<label><?php esc_html_e('Preview', 'product-import-export-for-woo');?></label>
			<p class="wt_iew_mapping_field_editor_info">
				<?php esc_html_e('Sample value based on the first record from input file.', 'product-import-export-for-woo'); ?>
			</p>
			<div class="wt_iew_mapping_field_editor_box" style="max-height:80px; overflow:auto; margin-bottom:0px; border:dashed 1px #ccc; padding:5px;">
				<div class="wt_iew_mapping_field_editor_sample"></div>
			</div>
		</div>		
	</div>
</div>
<!-- Mapping field editor popup -->

<div class="wt_iew_import_main">	
	<p><?php echo wp_kses_post($this->step_description);?></p>

	<p class="wt_iew_info_box wt_iew_info">
		-- <?php esc_html_e('The first row from your input file is considered as a header for mapping columns and hence will NOT BE imported.', 'product-import-export-for-woo');?>
		<br />
		-- <?php esc_html_e('Columns are mapped automatically only if a matching header name is found in the input file.', 'product-import-export-for-woo');?>
		<br/>
		-- <?php esc_html_e('In the case of empty fields, you can simply click on the respective field and map the corresponding column from your input file.', 'product-import-export-for-woo');?>
	</p>

	<div class="meta_mapping_box">
		<div class="meta_mapping_box_hd_nil wt_iew_noselect">
			<?php esc_html_e('Default fields', 'product-import-export-for-woo');?>
			<span class="meta_mapping_box_selected_count_box"><span class="meta_mapping_box_selected_count_box_num">0</span> <?php esc_html_e(' columns(s) selected', 'product-import-export-for-woo'); ?></span>
		</div>
		<div style="clear:both;"></div>
		<div class="meta_mapping_box_con" data-sortable="0" data-loaded="1" data-field-validated="0" data-key="" style="display:inline-block;">
			<table class="wt-iew-mapping-tb wt-iew-mapping-tb-imp wt-iew-importer-default-mapping-tb">
				<thead>
					<tr>
			    		<th>
			    			<input type="checkbox" name="" class="wt_iew_mapping_checkbox_main">
			    		</th>
			    		<th width="35%"><span class="wt_iew_step_head_post_type_name"></span> <?php esc_html_e( 'fields', 'product-import-export-for-woo' );?></th>
			    		<th><?php esc_html_e( 'File columns', 'product-import-export-for-woo' );?></th>
						<th><?php esc_html_e( 'Transform', 'product-import-export-for-woo' );?></th>
			    	</tr>
				</thead>
				<tbody>
				<?php
				$draggable_tooltip=__("Drag to rearrange the columns", 'product-import-export-for-woo');
				$tr_count=0;
				foreach($form_data_mapping_fields as $key=>$val_arr) /* looping the template form data */
				{
					$val=$val_arr[0]; /* normal column val */
					$checked=$val_arr[1]; /* import this column? */
					
					if(isset($mapping_fields[$key])) /* found in default field list */
					{
						$label=(isset($mapping_fields[$key]['title']) ? $mapping_fields[$key]['title'] : '');
						$description=(isset($mapping_fields[$key]['description']) ? $mapping_fields[$key]['description'] : '');
						$type=(isset($mapping_fields[$key]['type']) ? $mapping_fields[$key]['type'] : '');
						unset($mapping_fields[$key]); //remove the field from default list
						
						if(isset($file_heading_default_fields[$key])) /* also found in file heading list */
						{
							unset($file_heading_default_fields[$key]); //remove the field from file heading list
						}

						include "_import_mapping_tr_html.php";
						$tr_count++;
					}
					elseif(isset($file_heading_default_fields[$key])) /* found in file heading list */
					{
						$label=$key;
						$description=$key;
						$type='';
						unset($file_heading_default_fields[$key]); //remove the field from file heading list
						include "_import_mapping_tr_html.php";
						$tr_count++;	
					}
					elseif(isset($file_heading_meta_fields[$key])) /* some meta items will show inside default field list, Eg: yoast */
					{
						$label=$key;
						$description=$key;
						$type='';
						unset($file_heading_meta_fields[$key]); //remove the field from file heading list
						include "_import_mapping_tr_html.php";
						$tr_count++;
					}						
				}

				/**
				*	####Important#### 
				*	The similar code also done in Default mapping preparation step for quick import. 
				*	If any updates done please update there also 
				*	Method _prepare_for_quick in import ajax  class
				*/

				if(count($mapping_fields)>0)
				{                                           
                                        $array_keys_file_heading_default_fields = array_keys($file_heading_default_fields);    
					$allowed_field_types=array('start_with', 'end_with', 'contains', 'alternates');
					foreach($mapping_fields as $key=>$val_arr)
					{	
						$label=(isset($val_arr['title']) ? $val_arr['title'] : '');
						$description=(isset($val_arr['description']) ? $val_arr['description'] : '');
						$type=(isset($val_arr['type']) ? $val_arr['type'] : '');
						$val='';
						$checked=0; /* import this column? */
//						if(isset($file_heading_default_fields[$key]))                                                
                                                if($case_key = preg_grep("/^$key$/i", $array_keys_file_heading_default_fields))   //preg_grep used escape from case sensitive check.
						{       
							$checked=1; /* import this column? */
//                                                        $val='{'.$key.'}';
							$val='{'.array_shift($case_key).'}';  //  preg_grep give an array with actual index and value
							unset($file_heading_default_fields[$key]); //remove the field from file heading list
                                                        unset($array_keys_file_heading_default_fields[$key]);
							include "_import_mapping_tr_html.php";
							$tr_count++;
						}
						elseif(isset($file_heading_meta_fields[$key])) /* some meta items will show inside default field list, Eg: yoast */
						{
							$checked=1; /* import this column? */
							$val='{'.$key.'}';
							unset($file_heading_meta_fields[$key]); //remove the field from file heading list
							include "_import_mapping_tr_html.php";
							$tr_count++;
						}else
						{
							
							$field_type=(isset($val_arr['field_type']) ? $val_arr['field_type'] : '');
							if($field_type!="" && in_array($field_type, $allowed_field_types)) // it may be a different field type 
							{	
								$is_checked_inside = 0;
								foreach ($file_heading_default_fields as $def_key => $def_val) 
								{
									$matched=false;
									$alternate_set = false;
									if($field_type=='start_with' && strpos($def_key, $key)===0)
									{
										$matched=true;
									}
									elseif($field_type=='ends_with' && strrpos($def_key, $key)===(strlen($def_key) - strlen($key)))
									{
										$matched=true;
									}
									elseif($field_type=='contains' && strpos($def_key, $key)!==false)
									{
										$matched=true;
									}
									elseif($field_type=='alternates' && in_array($def_key, $val_arr['similar_fields']))
									{
										$alternate_set = true;
										$matched = true;
									}
									if($matched && $alternate_set)
									{
										$is_checked_inside = 1;
										$checked=1; // import this column? 
										$val='{'.$def_key.'}';
										unset($file_heading_default_fields[$def_key]); //remove the field from file heading list
										include "_import_mapping_tr_html.php";
										$tr_count++;
									}elseif($matched){
										$is_checked_inside = 1;
										$checked=1; // import this column? 
										$val='{'.$def_key.'}';
										$label=$def_key;
										$key_backup=$key;
										$key=$def_key;
										unset($file_heading_default_fields[$def_key]); //remove the field from file heading list
										include "_import_mapping_tr_html.php";
										$tr_count++;
										$key=$key_backup;										
									}
								}
								if(!$is_checked_inside){
									$checked=0; /* import this column? */
									$val='';
									include "_import_mapping_tr_html.php";
									$tr_count++;
								}
							}else /* unmatched keys */
							{
								$checked=0; /* import this column? */
								$val='';
								include "_import_mapping_tr_html.php";
								$tr_count++;
							}
						}
					}
				}
				if(count($file_heading_default_fields)>0) /* show the remaining items */
				{
					/*
					foreach($file_heading_default_fields as $key=>$sample_val)
					{
						$label=$key;
						$description=$key;
						$val='{'.$key.'}';
						$checked=1;
						include "_import_mapping_tr_html.php";
						$tr_count++;
					}
					*/
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
			?>
			<div class="meta_mapping_box">
				<div class="meta_mapping_box_hd wt_iew_noselect">
					<span class="dashicons dashicons-arrow-right"></span>
					<?php echo esc_html($mapping_enabled_field[0]);?>
					<span class="meta_mapping_box_selected_count_box"><span class="meta_mapping_box_selected_count_box_num">0</span> <?php esc_html_e(' columns(s) selected', 'product-import-export-for-woo'); ?></span>
				</div>
				<div style="clear:both;"></div>
				<div class="meta_mapping_box_con" data-sortable="0" data-loaded="0" data-field-validated="0" data-key="<?php echo esc_attr($mapping_enabled_field_key);?>"></div>
			</div>
			<div style="clear:both;"></div>
			<?php
		}
	}
	?>	
</div>
<script type="text/javascript">
	var wt_iew_file_head_remaining_meta=<?php echo json_encode($file_heading_meta_fields);?>;
</script>