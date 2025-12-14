<?php
if ( ! defined( 'WPINC' ) ) {
    die;
}
?>
<div class="wt-iew-tab-content" data-id="<?php echo esc_attr( $target_id );?>">
	<ul class="wt_iew_sub_tab">
		<li style="border-left:none; padding-left: 0px;" data-target="help-links"><a><?php esc_html_e('Help Links', 'product-import-export-for-woo'); ?></a></li>
		<li data-target="help-doc"><a><?php esc_html_e('Sample CSV', 'product-import-export-for-woo');?></a></li>
	</ul>
	<div class="wt_iew_sub_tab_container">		
		<div class="wt_iew_sub_tab_content" data-id="help-links" style="display:block;">
			<!--<h3><?php //_e('Help Links'); ?></h3>-->
			<ul class="wf-help-links">
			    <li>
			        <img src="<?php echo esc_url(WT_P_IEW_PLUGIN_URL);?>assets/images/documentation.png">
			        <h3><?php esc_html_e('Documentation', 'product-import-export-for-woo'); ?></h3>
			        <p><?php esc_html_e('Refer to our documentation to set up and get started.', 'product-import-export-for-woo'); ?></p>
			        <a target="_blank" href="https://www.webtoffee.com/category/documentation/" class="button button-primary">
			            <?php esc_html_e('Documentation', 'product-import-export-for-woo'); ?>        
			        </a>
			    </li>
			    <li>
			        <img src="<?php echo esc_url(WT_P_IEW_PLUGIN_URL);?>assets/images/support.png">
			        <h3><?php esc_html_e('Help and Support', 'product-import-export-for-woo'); ?></h3>
			        <p><?php esc_html_e('We would love to help you on any queries or issues.', 'product-import-export-for-woo'); ?></p>
			        <a target="_blank" href="https://www.webtoffee.com/support/" class="button button-primary">
			            <?php esc_html_e('Contact Us', 'product-import-export-for-woo'); ?>
			        </a>
			    </li>               
			</ul>
		</div>
		<div class="wt_iew_sub_tab_content" data-id="help-doc">
			<!--<h3><?php //_e( 'Help Docs' ); ?></h3>-->
			<ul class="wf-help-links">
				<?php do_action( 'wt_user_addon_basic_help_content' ); ?>
				<?php do_action( 'wt_order_addon_basic_help_content' ); ?>
				<?php do_action( 'wt_coupon_addon_basic_help_content' ); ?>
                                <?php do_action( 'wt_product_addon_basic_help_content' ); ?>				
			</ul>
		</div>
	</div>
</div>