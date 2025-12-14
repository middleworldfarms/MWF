<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wt_iew_import_main">
	<p><?php //echo $this->step_description;?></p>
	<div class="wt_iew_warn wt_iew_method_import_wrn" style="display:none;">
		<?php esc_html_e('Please select an import template.', 'users-customers-import-export-for-wp-woocommerce');?>
	</div>
	<table class="form-table wt-iew-form-table">
		<tr>
			<th><label><?php esc_html_e('Import method', 'users-customers-import-export-for-wp-woocommerce');?></label></th>
			<td colspan="2" style="width:75%;">
                <div class="wt_iew_radio_block">
                    <?php
					if(empty($this->mapping_templates)){
						unset($this->import_obj->import_methods['template']);
					}					
                    foreach($this->import_obj->import_methods as $key => $value) 
                    {
                        ?>
                        <p>
                            <input type="radio" value="<?php echo esc_attr($key);?>" id="wt_iew_import_<?php echo esc_attr($key);?>_import" name="wt_iew_import_method_import" <?php echo ($this->import_method==$key ? 'checked="checked"' : '');?>><b><label for="wt_iew_import_<?php echo esc_attr($key);?>_import"><?php echo esc_html($value['title']); ?></label></b> <br />
                            <span><label for="wt_iew_import_<?php echo esc_attr($key);?>_import"><?php echo esc_html($value['description']); ?></label></span>
                        </p>
                        <?php
                    }
                    ?>
                </div>
			</td>
		</tr>
		<tr><div id="user-required-field-message" class="updated" style="margin-left:0px;display: none;background: #dceff4;"><p><?php esc_html_e('Ensure the import file has the user\'s email ID for a successful import. Use default column name <b>user_email</b> or map the column accordingly if you are using a custom column name.', 'users-customers-import-export-for-wp-woocommerce'); ?></p></div></tr>
		<tr class="wt-iew-import-method-options wt-iew-import-method-options-template wt-iew-import-template-sele-tr" style="display:none;">
    		<th><label><?php esc_html_e('Import template', 'users-customers-import-export-for-wp-woocommerce');?></label></th>
    		<td>
    			<select class="wt-iew-import-template-sele">
    				<option value="0">-- <?php esc_html_e('Select a template', 'users-customers-import-export-for-wp-woocommerce'); ?> --</option>
    				<?php
    				foreach($this->mapping_templates as $mapping_template)
    				{
    				?>
    					<option value="<?php echo esc_attr($mapping_template['id']);?>" <?php echo ($form_data_import_template==$mapping_template['id'] ? ' selected="selected"' : ''); ?>>
    						<?php echo esc_html($mapping_template['name']);?>
    					</option>
    				<?php
    				}
    				?>
    			</select>
    		</td>
    		<td>
    		</td>
    	</tr>
	</table>
    <form class="wt_iew_import_method_import_form">
        <table class="form-table wt-iew-form-table">
            <?php
            Wt_Import_Export_For_Woo_Basic_Common_Helper::field_generator($method_import_screen_fields, $method_import_form_data);
            ?>
        </table>
		<div class="wt_iew_suite_banner">
			<div class="wt_iew_suite_banner_border"></div>
			<p style="font-size: 13px; font-weight: 400; margin-top: -61px;margin-left: 13px; padding: 10px 10px;">
				<strong><?php echo esc_html__('💡 Did You Know?', 'users-customers-import-export-for-wp-woocommerce'); ?></strong> <?php echo esc_html__('Get advanced features like FTP/SFTP import, and support for XLSX, XLS, XML, and TXT files with our premium version.', 'users-customers-import-export-for-wp-woocommerce'); ?>
				<a href="<?php echo esc_url($link . WT_U_IEW_VERSION); ?>" style="color: blue;" target="_blank"><?php echo esc_html($text); ?></a>
			</p>
		</div>
    </form>
</div>
<script type="text/javascript">
/* remote file modules can hook */
function wt_iew_set_file_from_fields(file_from)
{
    <?php
    do_action('wt_iew_importer_file_from_js_fn');
    ?>
}

function wt_iew_set_validate_file_info(file_from)
{
    <?php
    do_action('wt_iew_importer_set_validate_file_info');
    ?>
}
</script>