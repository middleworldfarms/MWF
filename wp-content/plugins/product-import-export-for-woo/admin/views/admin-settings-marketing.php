<?php
if ( ! defined( 'WPINC' ) ) {
    die;
}
?>
<div class="wt-iew-tab-content" data-id="<?php echo esc_attr( $target_id );?>">
    <div class="wt-ier-wrapper">
    <h2 class="wt-ier-page-title"><?php esc_html_e('One stop solution for all your import-export needs.', 'product-import-export-for-woo');?></h2>
    <p class="wt-ier-subp"><?php esc_html_e('Upgrade to the premium version and get access to the advanced features with premium support.', 'product-import-export-for-woo');?></p>
    <div class="wt-ier-row">
      <div class="wt-ier-col-12 wt-ier-col-lg-4 wt-ier-lg-4 wt-ier-mb-lg-0">
        <div class="wt-ier-p-5 wt-ier-box-wrapper wt-ier-box-highlight">
          <img src="<?php echo esc_url(WT_P_IEW_PLUGIN_URL); ?>assets/images/upgrade/product-ie.svg" class="wt-ier-thumbnails">
          <h3 class="wt-ier-plugin-name"><?php esc_html_e('PRODUCT IMPORT EXPORT PLUGIN FOR WOOCOMMERCE', 'product-import-export-for-woo');?></h3>
          <p class="wt-ier-p"><?php esc_html_e('Imports and exports all product types and reviews. Supports both CSV and XML file formats.', 'product-import-export-for-woo');?></p>
          <div class="wt-ier-d-sm-flex wt-ier-btn-group">
            <a href="https://www.webtoffee.com/product/product-import-export-woocommerce/?utm_source=free_plugin_revamp&utm_medium=basic_revamp&utm_campaign=Product_Import_Export&utm_content=<?php echo esc_html(WT_P_IEW_VERSION); ?>" target="_blank" class="wt-ier-primary-btn wt-ier-btn"><?php esc_html_e('Get Premium', 'product-import-export-for-woo');?></a>
            <a href="" class="wt-ier-secondary-btn wt-ier-btn" data-toggle="modal" data-target="#wt-ier-comparison-modal-product"><?php esc_html_e('Compare with basic', 'product-import-export-for-woo');?></a>
          </div>
        </div>
      </div>
      <div class="wt-ier-col-12 wt-ier-col-lg-4 wt-ier-lg-4 wt-ier-mb-lg-0">
        <div class="wt-ier-p-5 wt-ier-box-wrapper wt-ier-box-highlight">
          <img src="<?php echo esc_url(WT_P_IEW_PLUGIN_URL); ?>assets/images/upgrade/customer-ie.svg" class="wt-ier-thumbnails">
          <h3 class="wt-ier-plugin-name"><?php esc_html_e('WORDPRESS USERS & WOOCOMMERCE CUSTOMERS IMPORT EXPORT', 'product-import-export-for-woo');?></h3>
          <p class="wt-ier-p"><?php esc_html_e('Import and export all your WordPress User and WooCommerce Customer data in CSV/XML file formats.', 'product-import-export-for-woo');?></p>
          <div class="wt-ier-d-sm-flex wt-ier-btn-group">
            <a href="https://www.webtoffee.com/product/wordpress-users-woocommerce-customers-import-export/?utm_source=free_plugin_revamp&utm_medium=basic_revamp&utm_campaign=User_Import_Export&utm_content=<?php echo esc_html(WT_P_IEW_VERSION); ?>" class="wt-ier-primary-btn wt-ier-btn" target="_blank"><?php esc_html_e('Get Premium', 'product-import-export-for-woo');?></a>
            <a href="" class="wt-ier-secondary-btn wt-ier-btn" data-toggle="modal" data-target="#wt-ier-comparison-modal-customer"><?php esc_html_e('Compare with basic', 'product-import-export-for-woo');?></a>
          </div>
        </div>
      </div>
      <div class="wt-ier-col-12 wt-ier-col-lg-4 wt-ier-lg-4 wt-ier-mb-lg-0">
        <div class="wt-ier-p-5 wt-ier-box-wrapper wt-ier-box-highlight">
          <img src="<?php echo esc_url(WT_P_IEW_PLUGIN_URL); ?>assets/images/upgrade/order-ie.svg" class="wt-ier-thumbnails">
          <h3 class="wt-ier-plugin-name"><?php esc_html_e('ORDER, COUPON, SUBSCRIPTION EXPORT IMPORT FOR WOOCOMMERCE', 'product-import-export-for-woo');?></h3>
          <p class="wt-ier-p"><?php esc_html_e('Export or Import WooCommerce orders, Coupons and Subscriptions.', 'product-import-export-for-woo');?></p>
          <div class="wt-ier-d-sm-flex wt-ier-btn-group">
            <a  href="https://www.webtoffee.com/product/order-import-export-plugin-for-woocommerce/?utm_source=free_plugin_revamp&utm_medium=basic_revamp&utm_campaign=Order_Import_Export&utm_content=<?php echo esc_html(WT_P_IEW_VERSION); ?>" class="wt-ier-primary-btn wt-ier-btn" target="_blank"><?php esc_html_e('Get Premium', 'product-import-export-for-woo');?></a>
            <a href="" class="wt-ier-secondary-btn wt-ier-btn" data-toggle="modal" data-target="#wt-ier-comparison-modal-order"><?php esc_html_e('Compare with basic', 'product-import-export-for-woo');?></a>
          </div>
        </div>
      </div>
    </div>
    <!--------product imp-exp comparison table --------->
    <div id="wt-ier-comparison-modal-product" class="wt-ier-modal">
      <div class="wt-ier-modal-content">
        <div class="wt-ier-resposive-table">
          <table class="wt-ier-table">
            <thead>
              <tr class="wt-ier-top-tr">
                <td></td>
                <td colspan="3"><span class="wt-ier-close">&times;</span></td>
              </tr>
              <tr>
                <th><?php esc_html_e('Features', 'product-import-export-for-woo');?></th>
                <th><?php esc_html_e('Free', 'product-import-export-for-woo');?></th>
                <th><img src="<?php echo esc_url(WT_P_IEW_PLUGIN_URL); ?>assets/images/upgrade/pre-icon.svg" class="wt-ier-pre-icon"><?php esc_html_e('Premium', 'product-import-export-for-woo');?></th>
                <th><?php esc_html_e('Import Export Suite', 'product-import-export-for-woo');?></th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td><?php esc_html_e('Import and export', 'product-import-export-for-woo');?></td>
                <td>
					<ul>				
					  <li><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('Products', 'product-import-export-for-woo');?></li>
                      <li><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('Product Reviews', 'product-import-export-for-woo');?></li>
					  <li><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('Product Categories', 'product-import-export-for-woo');?></li>
					  <li><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('Product Tags', 'product-import-export-for-woo');?></li>
                      <li><span style="color:red; line-height:inherit;" class="dashicons dashicons-dismiss"></span> <?php esc_html_e('Orders', 'product-import-export-for-woo');?></li>
                      <li><span style="color:red; line-height:inherit;" class="dashicons dashicons-dismiss"></span> <?php esc_html_e('Coupons', 'product-import-export-for-woo');?></li>
                      <li><span style="color:red; line-height:inherit;" class="dashicons dashicons-dismiss"></span> <?php esc_html_e('Subscriptions', 'product-import-export-for-woo');?></li>
                      <li><span style="color:red; line-height:inherit;" class="dashicons dashicons-dismiss"></span> <?php esc_html_e('WordPress Users', 'product-import-export-for-woo');?></li>
                      <li><span style="color:red; line-height:inherit;" class="dashicons dashicons-dismiss"></span> <?php esc_html_e('WooCommerce Customers', 'product-import-export-for-woo');?></li>					  
                    </ul>
				</td>
                <td>
					<ul>
					  <li><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('Products', 'product-import-export-for-woo');?></li>
                      <li><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('Product Reviews', 'product-import-export-for-woo');?></li>
					  <li><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('Product Categories', 'product-import-export-for-woo');?></li>
					  <li><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('Product Tags', 'product-import-export-for-woo');?></li>
                      <li><span style="color:red; line-height:inherit;" class="dashicons dashicons-dismiss"></span> <?php esc_html_e('Orders', 'product-import-export-for-woo');?></li>
                      <li><span style="color:red; line-height:inherit;" class="dashicons dashicons-dismiss"></span> <?php esc_html_e('Coupons', 'product-import-export-for-woo');?></li>
                      <li><span style="color:red; line-height:inherit;" class="dashicons dashicons-dismiss"></span> <?php esc_html_e('Subscriptions', 'product-import-export-for-woo');?></li>
                      <li><span style="color:red; line-height:inherit;" class="dashicons dashicons-dismiss"></span> <?php esc_html_e('WordPress Users', 'product-import-export-for-woo');?></li>
                      <li><span style="color:red; line-height:inherit;" class="dashicons dashicons-dismiss"></span> <?php esc_html_e('WooCommerce Customers', 'product-import-export-for-woo');?></li>					  
                    </ul>
				</td>
                  <td>
					<ul>	                						
					  <li><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('Products', 'product-import-export-for-woo');?></li>
                      <li><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('Product Reviews', 'product-import-export-for-woo');?></li>
					  <li><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('Product Categories', 'product-import-export-for-woo');?></li>
					  <li><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('Product Tags', 'product-import-export-for-woo');?></li>
                      <li><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('Orders', 'product-import-export-for-woo');?></li>
                      <li><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('Coupons', 'product-import-export-for-woo');?></li>
                      <li><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('Subscriptions', 'product-import-export-for-woo');?></li>
                      <li><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('WordPress Users', 'product-import-export-for-woo');?></li>
                      <li><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('WooCommerce Customers', 'product-import-export-for-woo');?></li>					  
                    </ul>
                  </td>
                </tr>
                <tr>
                  <td><?php esc_html_e('Supported product types', 'product-import-export-for-woo');?></td>
                  <td>
					<ul>
					  <li><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('Simple Products', 'product-import-export-for-woo');?></li>
                      <li><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('Grouped Products', 'product-import-export-for-woo');?></li>
					  <li><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('External/Affiliate Products', 'product-import-export-for-woo');?></li>
					  <li><span style="color:red; line-height:inherit;" class="dashicons dashicons-dismiss"></span> <?php esc_html_e('Variable product', 'product-import-export-for-woo');?></li>
                      <li><span style="color:red; line-height:inherit;" class="dashicons dashicons-dismiss"></span> <?php esc_html_e('Simple subscription', 'product-import-export-for-woo');?></li>
                      <li><span style="color:red; line-height:inherit;" class="dashicons dashicons-dismiss"></span> <?php esc_html_e('Variable subscription', 'product-import-export-for-woo');?></li>
					  <li><span style="color:red; line-height:inherit;" class="dashicons dashicons-dismiss"></span> <?php esc_html_e('WooCommerce Bookings', 'product-import-export-for-woo');?></li>
                      <li><span style="color:red; line-height:inherit;" class="dashicons dashicons-dismiss"></span> <?php esc_html_e('Custom Products', 'product-import-export-for-woo');?></li>
                    </ul>
				</td>
                <td>
					<ul>
					  <li><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('Simple Products', 'product-import-export-for-woo');?></li>
                      <li><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('Grouped Products', 'product-import-export-for-woo');?></li>
					  <li><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('External/Affiliate Products', 'product-import-export-for-woo');?></li>
					  <li><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('Variable product', 'product-import-export-for-woo');?></li>
                      <li><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('Simple subscription', 'product-import-export-for-woo');?></li>
                      <li><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('Variable subscription', 'product-import-export-for-woo');?></li>
					  <li><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('WooCommerce Bookings', 'product-import-export-for-woo');?></li>
                      <li><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('Custom Products', 'product-import-export-for-woo');?></li>
                    </ul>
				</td>
                  <td>
					<ul>
					  <li><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('Simple Products', 'product-import-export-for-woo');?></li>
                      <li><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('Grouped Products', 'product-import-export-for-woo');?></li>
					  <li><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('External/Affiliate Products', 'product-import-export-for-woo');?></li>
					  <li><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('Variable product', 'product-import-export-for-woo');?></li>
                      <li><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('Simple subscription', 'product-import-export-for-woo');?></li>
                      <li><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('Variable subscription', 'product-import-export-for-woo');?></li>
					  <li><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('WooCommerce Bookings', 'product-import-export-for-woo');?></li>
                      <li><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('Custom Products', 'product-import-export-for-woo');?></li>
                    </ul>
                  </td>
                </tr>
				<tr>
                  <td><?php esc_html_e('Supported file types', 'product-import-export-for-woo');?></td>
                  <td>
					<ul>
					  <li><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('CSV', 'product-import-export-for-woo');?></li>
                      <li><span style="color:red; line-height:inherit;" class="dashicons dashicons-dismiss"></span> <?php esc_html_e('XML', 'product-import-export-for-woo');?></li>
                    </ul>
				  </td>
                  <td>
					<ul>
					  <li><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('CSV', 'product-import-export-for-woo');?></li>
                      <li><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('XML', 'product-import-export-for-woo');?></li>
                    </ul>
				  </td>
                  <td>
					<ul>
					  <li><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('CSV', 'product-import-export-for-woo');?></li>
                      <li><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('XML', 'product-import-export-for-woo');?></li>
                    </ul>
				  </td>
                </tr>
				<tr>
                  <td><?php esc_html_e('Supported import methods', 'product-import-export-for-woo');?></td>
                  <td>
					<ul>
					  <li><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('Local', 'product-import-export-for-woo');?></li>
                      <li><span style="color:red; line-height:inherit;" class="dashicons dashicons-dismiss"></span> <?php esc_html_e('FTP/SFTP', 'product-import-export-for-woo');?></li>
					  <li><span style="color:red; line-height:inherit;" class="dashicons dashicons-dismiss"></span> <?php esc_html_e('From URL', 'product-import-export-for-woo');?></li>
                      <li><span style="color:red; line-height:inherit;" class="dashicons dashicons-dismiss"></span> <?php esc_html_e('From existing files', 'product-import-export-for-woo');?></li>
                    </ul>
				  </td>
                  <td>
					<ul>
					  <li><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('Local', 'product-import-export-for-woo');?></li>
                      <li><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('FTP/SFTP', 'product-import-export-for-woo');?></li>
					  <li><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('From URL', 'product-import-export-for-woo');?></li>
                      <li><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('From existing files', 'product-import-export-for-woo');?></li>
                    </ul>
				  </td>
                  <td>
					<ul>
					  <li><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('Local', 'product-import-export-for-woo');?></li>
                      <li><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('FTP/SFTP', 'product-import-export-for-woo');?></li>
					  <li><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('From URL', 'product-import-export-for-woo');?></li>
                      <li><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('From existing files', 'product-import-export-for-woo');?></li>
                    </ul>
				  </td>
                </tr>
                <tr>
                  <td><?php esc_html_e('Automatic scheduled import & export', 'product-import-export-for-woo');?></td>
                  <td><span style="color:red; line-height:inherit;" class="dashicons dashicons-dismiss"></span></td>
                  <td><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span></td>
                  <td><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span></td>
                </tr>
				<tr>
                  <td><?php esc_html_e('Import & export custom fields ( metadata )', 'product-import-export-for-woo');?></td>
                  <td><span style="color:red; line-height:inherit;" class="dashicons dashicons-dismiss"></span></td>
                  <td><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span></td>
                  <td><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span></td>
                </tr>
				<tr>
                  <td><?php esc_html_e('Import & export hidden meta', 'product-import-export-for-woo');?></td>
                  <td><span style="color:red; line-height:inherit;" class="dashicons dashicons-dismiss"></span></td>
                  <td><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span></td>
                  <td><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span></td>
                </tr>
				<tr>
                  <td><?php esc_html_e('Bulk delete products', 'product-import-export-for-woo');?></td>
                  <td><span style="color:red; line-height:inherit;" class="dashicons dashicons-dismiss"></span></td>
                  <td><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span></td>
                  <td><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span></td>
                </tr>
                <tr>
                  <td><?php esc_html_e('Option to import products as new item during post id conflicts', 'product-import-export-for-woo');?></td>
                  <td><span style="color:red; line-height:inherit;" class="dashicons dashicons-dismiss"></span></td>
                  <td><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span></td>
                  <td><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span></td>
                </tr>
				<tr>
                  <td><?php esc_html_e('Export to FTP/SFTP', 'product-import-export-for-woo');?></td>
                  <td><span style="color:red; line-height:inherit;" class="dashicons dashicons-dismiss"></span></td>
                  <td><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span></td>
                  <td><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span></td>
                </tr>
				<tr>
                  <td><?php esc_html_e('Set CSV delimiter for export', 'product-import-export-for-woo');?></td>
                  <td><span style="color:red; line-height:inherit;" class="dashicons dashicons-dismiss"></span></td>
                  <td><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span></td>
                  <td><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span></td>
                </tr>
				<tr>
                  <td><?php esc_html_e('Export images as a separate zip', 'product-import-export-for-woo');?></td>
                  <td><span style="color:red; line-height:inherit;" class="dashicons dashicons-dismiss"></span></td>
                  <td><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span></td>
                  <td><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span></td>
                </tr>
				<tr>
                  <td><?php esc_html_e('Convert shortcodes to HTML on export', 'product-import-export-for-woo');?></td>
                  <td><span style="color:red; line-height:inherit;" class="dashicons dashicons-dismiss"></span></td>
                  <td><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span></td>
                  <td><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span></td>
                </tr>
				<tr>
                  <td><?php esc_html_e('Custom export filename', 'product-import-export-for-woo');?></td>
                  <td><span style="color:red; line-height:inherit;" class="dashicons dashicons-dismiss"></span></td>
                  <td><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span></td>
                  <td><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span></td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <!--------customer imp-exp comparison table --------->
      <div id="wt-ier-comparison-modal-customer" class="wt-ier-modal">
        <div class="wt-ier-modal-content">
          <div class="wt-ier-resposive-table">
            <table class="wt-ier-table">
            <thead>
              <tr class="wt-ier-top-tr">
                <td></td>
                <td colspan="3"><span class="wt-ier-close">&times;</span></td>
              </tr>
              <tr>
                <th><?php esc_html_e('Features', 'product-import-export-for-woo');?></th>
                <th><?php esc_html_e('Free', 'product-import-export-for-woo');?></th>
                <th><img src="<?php echo esc_url(WT_P_IEW_PLUGIN_URL); ?>assets/images/upgrade/pre-icon.svg" class="wt-ier-pre-icon"><?php esc_html_e('Premium', 'product-import-export-for-woo');?></th>
                <th><?php esc_html_e('Import Export Suite', 'product-import-export-for-woo');?></th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td><?php esc_html_e('Import and export', 'product-import-export-for-woo');?></td>
                <td>
					<ul>
					  <li><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('WordPress Users', 'product-import-export-for-woo');?></li>
                      <li><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('WooCommerce Customers', 'product-import-export-for-woo');?></li>						
					  <li><span style="color:red; line-height:inherit;" class="dashicons dashicons-dismiss"></span> <?php esc_html_e('Products', 'product-import-export-for-woo');?></li>
                      <li><span style="color:red; line-height:inherit;" class="dashicons dashicons-dismiss"></span> <?php esc_html_e('Product Reviews', 'product-import-export-for-woo');?></li>
					  <li><span style="color:red; line-height:inherit;" class="dashicons dashicons-dismiss"></span> <?php esc_html_e('Product Categories', 'product-import-export-for-woo');?></li>
					  <li><span style="color:red; line-height:inherit;" class="dashicons dashicons-dismiss"></span> <?php esc_html_e('Product Tags', 'product-import-export-for-woo');?></li>
                      <li><span style="color:red; line-height:inherit;" class="dashicons dashicons-dismiss"></span> <?php esc_html_e('Orders', 'product-import-export-for-woo');?></li>
                      <li><span style="color:red; line-height:inherit;" class="dashicons dashicons-dismiss"></span> <?php esc_html_e('Coupons', 'product-import-export-for-woo');?></li>
                      <li><span style="color:red; line-height:inherit;" class="dashicons dashicons-dismiss"></span> <?php esc_html_e('Subscriptions', 'product-import-export-for-woo');?></li>                      
                    </ul>
				</td>
                <td>
					<ul>
					  <li><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('WordPress Users', 'product-import-export-for-woo');?></li>
                      <li><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('WooCommerce Customers', 'product-import-export-for-woo');?></li>						
					  <li><span style="color:red; line-height:inherit;" class="dashicons dashicons-dismiss"></span> <?php esc_html_e('Products', 'product-import-export-for-woo');?></li>
                      <li><span style="color:red; line-height:inherit;" class="dashicons dashicons-dismiss"></span> <?php esc_html_e('Product Reviews', 'product-import-export-for-woo');?></li>
					  <li><span style="color:red; line-height:inherit;" class="dashicons dashicons-dismiss"></span> <?php esc_html_e('Product Categories', 'product-import-export-for-woo');?></li>
					  <li><span style="color:red; line-height:inherit;" class="dashicons dashicons-dismiss"></span> <?php esc_html_e('Product Tags', 'product-import-export-for-woo');?></li>
                      <li><span style="color:red; line-height:inherit;" class="dashicons dashicons-dismiss"></span> <?php esc_html_e('Orders', 'product-import-export-for-woo');?></li>
                      <li><span style="color:red; line-height:inherit;" class="dashicons dashicons-dismiss"></span> <?php esc_html_e('Coupons', 'product-import-export-for-woo');?></li>
                      <li><span style="color:red; line-height:inherit;" class="dashicons dashicons-dismiss"></span> <?php esc_html_e('Subscriptions', 'product-import-export-for-woo');?></li>                     
                    </ul>
				</td>
                  <td>
					<ul>
					  <li><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('WordPress Users', 'product-import-export-for-woo');?></li>
                      <li><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('WooCommerce Customers', 'product-import-export-for-woo');?></li>
					  <li><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('Products', 'product-import-export-for-woo');?></li>
                      <li><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('Product Reviews', 'product-import-export-for-woo');?></li>
					  <li><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('Product Categories', 'product-import-export-for-woo');?></li>
					  <li><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('Product Tags', 'product-import-export-for-woo');?></li>
                      <li><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('Orders', 'product-import-export-for-woo');?></li>
                      <li><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('Coupons', 'product-import-export-for-woo');?></li>
                      <li><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('Subscriptions', 'product-import-export-for-woo');?></li>                      
                    </ul>
                  </td>
                </tr>          
				<tr>
                  <td><?php esc_html_e('Supported file types', 'product-import-export-for-woo');?></td>
                  <td>
					<ul>
					  <li><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('CSV', 'product-import-export-for-woo');?></li>
                      <li><span style="color:red; line-height:inherit;" class="dashicons dashicons-dismiss"></span> <?php esc_html_e('XML', 'product-import-export-for-woo');?></li>
                    </ul>
				  </td>
                  <td>
					<ul>
					  <li><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('CSV', 'product-import-export-for-woo');?></li>
                      <li><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('XML', 'product-import-export-for-woo');?></li>
                    </ul>
				  </td>
                  <td>
					<ul>
					  <li><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('CSV', 'product-import-export-for-woo');?></li>
                      <li><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('XML', 'product-import-export-for-woo');?></li>
                    </ul>
				  </td>
                </tr>
				<tr>
                  <td><?php esc_html_e('Supported import methods', 'product-import-export-for-woo');?></td>
                  <td>
					<ul>
					  <li><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('Local', 'product-import-export-for-woo');?></li>
                      <li><span style="color:red; line-height:inherit;" class="dashicons dashicons-dismiss"></span> <?php esc_html_e('FTP/SFTP', 'product-import-export-for-woo');?></li>
					  <li><span style="color:red; line-height:inherit;" class="dashicons dashicons-dismiss"></span> <?php esc_html_e('From URL', 'product-import-export-for-woo');?></li>
                      <li><span style="color:red; line-height:inherit;" class="dashicons dashicons-dismiss"></span> <?php esc_html_e('From existing files', 'product-import-export-for-woo');?></li>
                    </ul>
				  </td>
                  <td>
					<ul>
					  <li><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('Local', 'product-import-export-for-woo');?></li>
                      <li><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('FTP/SFTP', 'product-import-export-for-woo');?></li>
					  <li><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('From URL', 'product-import-export-for-woo');?></li>
                      <li><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('From existing files', 'product-import-export-for-woo');?></li>
                    </ul>
				  </td>
                  <td>
					<ul>
					  <li><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('Local', 'product-import-export-for-woo');?></li>
                      <li><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('FTP/SFTP', 'product-import-export-for-woo');?></li>
					  <li><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('From URL', 'product-import-export-for-woo');?></li>
                      <li><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('From existing files', 'product-import-export-for-woo');?></li>
                    </ul>
				  </td>
                </tr>
                <tr>
                  <td><?php esc_html_e('Automatic scheduled import & export', 'product-import-export-for-woo');?></td>
                  <td><span style="color:red; line-height:inherit;" class="dashicons dashicons-dismiss"></span></td>
                  <td><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span></td>
                  <td><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span></td>
                </tr>
				<tr>
                  <td><?php esc_html_e('Import & export custom fields ( metadata )', 'product-import-export-for-woo');?></td>
                  <td><span style="color:red; line-height:inherit;" class="dashicons dashicons-dismiss"></span></td>
                  <td><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span></td>
                  <td><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span></td>
                </tr>
				<tr>
                  <td><?php esc_html_e('Import & export hidden meta', 'product-import-export-for-woo');?></td>
                  <td><span style="color:red; line-height:inherit;" class="dashicons dashicons-dismiss"></span></td>
                  <td><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span></td>
                  <td><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span></td>
                </tr>
                <tr>
                  <td><?php esc_html_e('Option to email new users on import', 'product-import-export-for-woo');?></td>
                  <td><span style="color:red; line-height:inherit;" class="dashicons dashicons-dismiss"></span></td>
                  <td><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span></td>
                  <td><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span></td>
                </tr>
				<tr>
                  <td><?php esc_html_e('Export to FTP/SFTP', 'product-import-export-for-woo');?></td>
                  <td><span style="color:red; line-height:inherit;" class="dashicons dashicons-dismiss"></span></td>
                  <td><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span></td>
                  <td><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span></td>
                </tr>
				<tr>
                  <td><?php esc_html_e('Set CSV delimiter for export', 'product-import-export-for-woo');?></td>
                  <td><span style="color:red; line-height:inherit;" class="dashicons dashicons-dismiss"></span></td>
                  <td><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span></td>
                  <td><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span></td>
                </tr>
				<tr>
                  <td><?php esc_html_e('Custom export filename', 'product-import-export-for-woo');?></td>
                  <td><span style="color:red; line-height:inherit;" class="dashicons dashicons-dismiss"></span></td>
                  <td><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span></td>
                  <td><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span></td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <!--------order imp-exp comparison table --------->
      <div id="wt-ier-comparison-modal-order" class="wt-ier-modal">
        <div class="wt-ier-modal-content">
          <div class="wt-ier-resposive-table">
            <table class="wt-ier-table">
            <thead>
              <tr class="wt-ier-top-tr">
                <td></td>
                <td colspan="3"><span class="wt-ier-close">&times;</span></td>
              </tr>
              <tr>
                <th><?php esc_html_e('Features', 'product-import-export-for-woo');?></th>
                <th><?php esc_html_e('Free', 'product-import-export-for-woo');?></th>
                <th><img src="<?php echo esc_url(WT_P_IEW_PLUGIN_URL); ?>assets/images/upgrade/pre-icon.svg" class="wt-ier-pre-icon"><?php esc_html_e('Premium', 'product-import-export-for-woo');?></th>
                <th><?php esc_html_e('Import Export Suite', 'product-import-export-for-woo');?></th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td><?php esc_html_e('Import and export', 'product-import-export-for-woo');?></td>
                <td>
					<ul>
					  <li><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('Orders', 'product-import-export-for-woo');?></li>
                      <li><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('Coupons', 'product-import-export-for-woo');?></li>	
                      <li><span style="color:red; line-height:inherit;" class="dashicons dashicons-dismiss"></span> <?php esc_html_e('Subscriptions', 'product-import-export-for-woo');?></li>					  
					  <li><span style="color:red; line-height:inherit;" class="dashicons dashicons-dismiss"></span> <?php esc_html_e('Products', 'product-import-export-for-woo');?></li>
                      <li><span style="color:red; line-height:inherit;" class="dashicons dashicons-dismiss"></span> <?php esc_html_e('Product Reviews', 'product-import-export-for-woo');?></li>
					  <li><span style="color:red; line-height:inherit;" class="dashicons dashicons-dismiss"></span> <?php esc_html_e('Product Categories', 'product-import-export-for-woo');?></li>
					  <li><span style="color:red; line-height:inherit;" class="dashicons dashicons-dismiss"></span> <?php esc_html_e('Product Tags', 'product-import-export-for-woo');?></li>
					  <li><span style="color:red; line-height:inherit;" class="dashicons dashicons-dismiss"></span> <?php esc_html_e('WordPress Users', 'product-import-export-for-woo');?></li>
                      <li><span style="color:red; line-height:inherit;" class="dashicons dashicons-dismiss"></span> <?php esc_html_e('WooCommerce Customers', 'product-import-export-for-woo');?></li>
                    </ul>
				</td>
                <td>
					<ul>
					  <li><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('Orders', 'product-import-export-for-woo');?></li>
                      <li><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('Coupons', 'product-import-export-for-woo');?></li>
                      <li><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('Subscriptions', 'product-import-export-for-woo');?></li>						
					  <li><span style="color:red; line-height:inherit;" class="dashicons dashicons-dismiss"></span> <?php esc_html_e('Products', 'product-import-export-for-woo');?></li>
                      <li><span style="color:red; line-height:inherit;" class="dashicons dashicons-dismiss"></span> <?php esc_html_e('Product Reviews', 'product-import-export-for-woo');?></li>
					  <li><span style="color:red; line-height:inherit;" class="dashicons dashicons-dismiss"></span> <?php esc_html_e('Product Categories', 'product-import-export-for-woo');?></li>
					  <li><span style="color:red; line-height:inherit;" class="dashicons dashicons-dismiss"></span> <?php esc_html_e('Product Tags', 'product-import-export-for-woo');?></li>                      
					  <li><span style="color:red; line-height:inherit;" class="dashicons dashicons-dismiss"></span> <?php esc_html_e('WordPress Users', 'product-import-export-for-woo');?></li>
                      <li><span style="color:red; line-height:inherit;" class="dashicons dashicons-dismiss"></span> <?php esc_html_e('WooCommerce Customers', 'product-import-export-for-woo');?></li>
                    </ul>
				</td>
                  <td>
					<ul>
						<li><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('Orders', 'product-import-export-for-woo');?></li>
                      <li><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('Coupons', 'product-import-export-for-woo');?></li>
                      <li><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('Subscriptions', 'product-import-export-for-woo');?></li>					 
					  <li><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('Products', 'product-import-export-for-woo');?></li>
                      <li><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('Product Reviews', 'product-import-export-for-woo');?></li>
					  <li><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('Product Categories', 'product-import-export-for-woo');?></li>
					  <li><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('Product Tags', 'product-import-export-for-woo');?></li> 
					  <li><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('WordPress Users', 'product-import-export-for-woo');?></li>
                      <li><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('WooCommerce Customers', 'product-import-export-for-woo');?></li>
                    </ul>
                  </td>
                </tr>          
				<tr>
                  <td><?php esc_html_e('Supported file types', 'product-import-export-for-woo');?></td>
                  <td>
					<ul>
					  <li><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('CSV', 'product-import-export-for-woo');?></li>
                      <li><span style="color:red; line-height:inherit;" class="dashicons dashicons-dismiss"></span> <?php esc_html_e('XML', 'product-import-export-for-woo');?></li>
                    </ul>
				  </td>
                  <td>
					<ul>
					  <li><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('CSV', 'product-import-export-for-woo');?></li>
                      <li><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('XML', 'product-import-export-for-woo');?></li>
                    </ul>
				  </td>
                  <td>
					<ul>
					  <li><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('CSV', 'product-import-export-for-woo');?></li>
                      <li><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('XML', 'product-import-export-for-woo');?></li>
                    </ul>
				  </td>
                </tr>
				<tr>
                  <td><?php esc_html_e('Supported import methods', 'product-import-export-for-woo');?></td>
                  <td>
					<ul>
					  <li><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('Local', 'product-import-export-for-woo');?></li>
                      <li><span style="color:red; line-height:inherit;" class="dashicons dashicons-dismiss"></span> <?php esc_html_e('FTP/SFTP', 'product-import-export-for-woo');?></li>
					  <li><span style="color:red; line-height:inherit;" class="dashicons dashicons-dismiss"></span> <?php esc_html_e('From URL', 'product-import-export-for-woo');?></li>
                      <li><span style="color:red; line-height:inherit;" class="dashicons dashicons-dismiss"></span> <?php esc_html_e('From existing files', 'product-import-export-for-woo');?></li>
                    </ul>
				  </td>
                  <td>
					<ul>
					  <li><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('Local', 'product-import-export-for-woo');?></li>
                      <li><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('FTP/SFTP', 'product-import-export-for-woo');?></li>
					  <li><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('From URL', 'product-import-export-for-woo');?></li>
                      <li><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('From existing files', 'product-import-export-for-woo');?></li>
                    </ul>
				  </td>
                  <td>
					<ul>
					  <li><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('Local', 'product-import-export-for-woo');?></li>
                      <li><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('FTP/SFTP', 'product-import-export-for-woo');?></li>
					  <li><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('From URL', 'product-import-export-for-woo');?></li>
                      <li><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('From existing files', 'product-import-export-for-woo');?></li>
                    </ul>
				  </td>
                </tr>
                <tr>
                  <td><?php esc_html_e('Automatic scheduled import & export', 'product-import-export-for-woo');?></td>
                  <td><span style="color:red; line-height:inherit;" class="dashicons dashicons-dismiss"></span></td>
                  <td><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span></td>
                  <td><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span></td>
                </tr>
				<tr>
                  <td><?php esc_html_e('Import & export custom fields ( metadata )', 'product-import-export-for-woo');?></td>
                  <td><span style="color:red; line-height:inherit;" class="dashicons dashicons-dismiss"></span></td>
                  <td><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span></td>
                  <td><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span></td>
                </tr>
				<tr>
                  <td><?php esc_html_e('Import & export hidden meta', 'product-import-export-for-woo');?></td>
                  <td><span style="color:red; line-height:inherit;" class="dashicons dashicons-dismiss"></span></td>
                  <td><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span></td>
                  <td><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span></td>
                </tr>
                <tr>
                  <td><?php esc_html_e('Email customers on order status update', 'product-import-export-for-woo');?></td>
                  <td><span style="color:red; line-height:inherit;" class="dashicons dashicons-dismiss"></span></td>
                  <td><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span></td>
                  <td><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span></td>
                </tr>
                <tr>
                  <td><?php esc_html_e('Create customers on order import', 'product-import-export-for-woo');?></td>
                  <td><span style="color:red; line-height:inherit;" class="dashicons dashicons-dismiss"></span></td>
                  <td><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span></td>
                  <td><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span></td>
                </tr>
                <tr>
                  <td><?php esc_html_e('Bulk delete orders/coupons/subcriptions', 'product-import-export-for-woo');?></td>
                  <td><span style="color:red; line-height:inherit;" class="dashicons dashicons-dismiss"></span></td>
                  <td><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span></td>
                  <td><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span></td>
                </tr>					
				<tr>
                  <td><?php esc_html_e('Export to FTP/SFTP', 'product-import-export-for-woo');?></td>
                  <td><span style="color:red; line-height:inherit;" class="dashicons dashicons-dismiss"></span></td>
                  <td><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span></td>
                  <td><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span></td>
                </tr>
				<tr>
                  <td><?php esc_html_e('Set CSV delimiter for export', 'product-import-export-for-woo');?></td>
                  <td><span style="color:red; line-height:inherit;" class="dashicons dashicons-dismiss"></span></td>
                  <td><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span></td>
                  <td><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span></td>
                </tr>
				<tr>
                  <td><?php esc_html_e('Custom export filename', 'product-import-export-for-woo');?></td>
                  <td><span style="color:red; line-height:inherit;" class="dashicons dashicons-dismiss"></span></td>
                  <td><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span></td>
                  <td><span style="color:#36D144; line-height:inherit;" class="dashicons dashicons-yes-alt"></span></td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <!--------comparison table ends here--------->
      <div class=" wt-ier-box-wrapper wt-ier-mt-5 wt-ier-suite">
        <div class="wt-ier-row wt-ier-p-5">
          <div class="wt-ier-col-12 wt-ier-col-lg-6">
            <img src="<?php echo esc_url(WT_P_IEW_PLUGIN_URL); ?>assets/images/upgrade/suite.svg" class="wt-ier-thumbnails">
            <h2 class="wt-ier-page-title"><?php esc_html_e('Import Export Suite for WooCommerce', 'product-import-export-for-woo');?></h2>
            <p class="wt-ier-p"><?php esc_html_e('WooCommerce Import Export Suite is an all-in-one bundle of plugins with which you can import and export WooCommerce products, product reviews, orders, customers, coupons and subscriptions.', 'product-import-export-for-woo');?></p>
            <a href="https://www.webtoffee.com/product/woocommerce-import-export-suite/?utm_source=free_plugin_revamp&utm_medium=basic_revamp&utm_campaign=Import_Export_Suite&utm_content=<?php echo esc_html(WT_P_IEW_VERSION); ?>" class="wt-ier-primary-btn" target="_blank"><?php esc_html_e('Get Premium', 'product-import-export-for-woo');?></a>
          </div>
        </div>
      </div>
    </div>
    <script>
    jQuery("a[data-toggle=modal]").click(function(e){
      e.preventDefault();
      var target=jQuery(this).attr('data-target');
      jQuery(target).css('display','block');
    });
    jQuery(document).click(function (e) {
      if (jQuery(e.target).is('.wt-ier-modal')) {
        jQuery('.wt-ier-modal').css('display','none');
      }

    });
    jQuery(".wt-ier-close").click(function (e) {
      jQuery(this).closest('.wt-ier-modal').css('display','none');
    });
  </script>
</div>