<?php 

$top_header_loadedoption_name = 'wbft_product_top_header_loaded';
$top_header_loaded = absint(get_option($top_header_loadedoption_name));

if (0===$top_header_loaded) {
?>


<style>
.wbte_pimpexp_header {
  top: 28px;
}
#wpbody-content {
	margin-top: 100px;
}

.wbtf_top_header {
  position: absolute;
  top: 0;
  left: -20px;
  width: calc(100% + 20px);
  height: 28px;
  background: #2B9E46;
}



.wbtf_top_header .wbtf_top_header_text,
.wbtf_top_header .wbtf_top_header_text a {
  font-family: 'Poppins', sans-serif;
  font-weight: 500;
  font-size: 14px;
  line-height: 21px;
  margin: 0;
  color: #fff;
}

.wbtf_top_header .wbtf_top_header_content_wrapper {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100%;
}

.wbtf_top_header .wbtf_close_btn {
  background: none;
  border: none;
  color: white;
  font-size: 16px;
  font-weight: bold;
  cursor: pointer;
  position: absolute;
  right: 10px;
  top: 50%;
  transform: translateY(-50%);
}

</style>


<?php 
if (is_plugin_active('order-import-export-for-woocommerce/order-import-export-for-woocommerce.php') || is_plugin_active('users-customers-import-export-for-wp-woocommerce/users-customers-import-export-for-wp-woocommerce.php')) {
  $plugin_pro_url='https://www.webtoffee.com/product/woocommerce-import-export-suite/?utm_source=free_plugin&utm_medium=post_type_import_tab&utm_campaign=Import_Export_Suite';

} else {
  $plugin_pro_url='https://www.webtoffee.com/product/product-import-export-woocommerce/?utm_source=free_plugin&utm_medium=post_type_import_tab&utm_campaign=Product_Import_Export';
}
?>

<div class="wbtf_top_header">
	<div class="wbtf_top_header_content_wrapper">
		<p class="wbtf_top_header_text"><?php echo  esc_html__('You’re using our free version. To unlock more features,', 'product-import-export-for-woo'); ?> <a href="<?php echo esc_url($plugin_pro_url); ?>" id="wbtf_top_header_pro_link" target="_blank"><?php echo  esc_html__('upgrade to pro ', 'product-import-export-for-woo'); ?> →</a> </p>
	</div>
	<button class="wbtf_close_btn" onclick="closeTopHeader()">×</button>

</div>

<?php 
}
?>