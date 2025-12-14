<?php
if (!defined('ABSPATH')) {
	exit;
}
?>
<div class="wt_iew_import_main">
	<div id="product-type-notice" style="display:block;">
        <?php
            // Define unsupported types to check
            $unsupported_types = array(
                'variable'     => 'Variable',
                'subscription' => 'Subscription',
                'bundle'       => 'Bundle',
                'composite'    => 'Composite',
            );

            $detected_types = array();

            foreach ($unsupported_types as $type => $label) {
                $args = array(
                    'type'   => $type,
                    'limit'  => 1,
                    'return' => 'ids',
                );
                $products = wc_get_products($args);
                if (!empty($products)) {
                    $detected_types[] = $label;
                }
            }

            if (!empty($detected_types)) {
                $last = array_pop($detected_types);
                if (empty($detected_types)) {
                    $types_string = $last;
                } else {
                    $types_string = implode(', ', $detected_types) . ' and ' . $last;
                }
                
                ?>
                <div class="notice notice-warning" style="width: 100%; max-width: 810px; margin-left: 0px; display: inline-flex; padding: 16px 18px 16px 26px; justify-content: flex-end; align-items: center; border-radius: 8px; border: 1px solid var(--Warning-W300, #EACB78); background: var(--Warning-W50, #FFFDF5); box-sizing: border-box;">
                    <div style="flex: 1 1 0; flex-direction: column; justify-content: flex-start; align-items: flex-start; gap: 7px; display: inline-flex; width: 100%;">  
                        <div style="align-self: stretch; color: #2A3646; font-size: 14px; font-family: Inter; font-weight: 600; line-height: 16px; word-wrap: break-word">
                            Uh oh! Unsupported Product Types Detected
                        </div>
                        <div style="align-self: stretch; flex-direction: column; justify-content: flex-start; align-items: flex-start; gap: 4px; display: flex; width: 100%;">
                            <div style="width: 100%; max-width: 679px">
                                <span style="color: #2A3646; font-size: 14px; font-family: Inter; font-weight: 400; word-wrap: break-word">
                                Your site has <?php echo esc_html($types_string); ?> products that the free version does not support.
                                </span>
                                <a href="https://www.webtoffee.com/product/product-import-export-woocommerce/?utm_source=free_plugin_file_upload&utm_medium=basic_revamp&utm_campaign=Product_Import_Export2.5.3" style="color: #0576FE; font-size: 14px; font-family: Inter; font-weight: 400; text-decoration: underline; word-wrap: break-word" target="_blank" rel="noopener noreferrer">
                                    Upgrade to Pro
                                </a>
                                <span style="color: #2A3646; font-size: 14px; font-family: Inter; font-weight: 400; word-wrap: break-word">
                                    to include them in your export/import.
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
            }
            ?>
    </div>
	<p><?php //echo $this->step_description;
		?></p>
	<div class="wt_iew_warn wt_iew_method_import_wrn" style="display:none;">
		<?php esc_html_e('Please select an import template.', 'product-import-export-for-woo'); ?>
	</div>
	<table class="form-table wt-iew-form-table">
		<tr>
			<th><label><?php esc_html_e('Import method', 'product-import-export-for-woo'); ?></label></th>
			<td colspan="2" style="width:75%;">
				<div class="wt_iew_radio_block">
					<?php
					if (empty($this->mapping_templates)) {
						unset($this->import_obj->import_methods['template']);
					}
					foreach ($this->import_obj->import_methods as $key => $value) {
					?>
						<p>
							<input type="radio" value="<?php echo esc_attr($key); ?>" id="wt_iew_import_<?php echo esc_attr($key); ?>_import" name="wt_iew_import_method_import" <?php echo ($this->import_method === $key ? 'checked="checked"' : ''); ?>><b><label for="wt_iew_import_<?php echo esc_attr($key); ?>_import"><?php echo esc_html($value['title']); ?></label></b> <br />
							<span><label for="wt_iew_import_<?php echo esc_attr($key); ?>_import"><?php echo esc_html($value['description']); ?></label></span>
						</p>
					<?php
					}
					?>
				</div>
			</td>
		</tr>
		<tr>
			<div id="user-required-field-message" class="updated" style="margin-left:0px;display: none;background: #dceff4;">
				<p><?php esc_html_e('Ensure the import file has the user\'s email ID for a successful import. Use default column name <b>user_email</b> or map the column accordingly if you are using a custom column name.', 'product-import-export-for-woo'); ?></p>
			</div>
		</tr>
		<tr class="wt-iew-import-method-options wt-iew-import-method-options-template wt-iew-import-template-sele-tr" style="display:none;">
				<th><label><?php esc_html_e('Import template', 'product-import-export-for-woo'); ?></label></th>
			<td>
				<select class="wt-iew-import-template-sele">
					<option value="0">-- <?php esc_html_e('Select a template', 'product-import-export-for-woo'); ?> --</option>
					<?php
					foreach ($this->mapping_templates as $mapping_template) {
					?>
						<option value="<?php echo esc_attr($mapping_template['id']); ?>" <?php echo ($form_data_import_template == $mapping_template['id'] ? ' selected="selected"' : ''); ?>>
							<?php echo esc_html($mapping_template['name']); ?>
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
				<strong><?php echo esc_html__('💡 Did You Know?', 'product-import-export-for-woo'); ?></strong> <?php echo esc_html__('Get advanced features like FTP/SFTP import, and support for XLSX, XLS, XML, and TXT files with our premium version.', 'product-import-export-for-woo'); ?>
				<a href="<?php echo esc_url($link . WT_P_IEW_VERSION); ?>" style="color: blue;" target="_blank"><?php echo esc_html($text); ?></a>
			</p>
		</div>
	</form>
</div>

<script type="text/javascript">
	/* remote file modules can hook */
	function wt_iew_set_file_from_fields(file_from) {
		<?php
		do_action('wt_iew_importer_file_from_js_fn');
		?>
	}

	function wt_iew_set_validate_file_info(file_from) {
		<?php
		do_action('wt_iew_importer_set_validate_file_info');
		?>
	}
</script>