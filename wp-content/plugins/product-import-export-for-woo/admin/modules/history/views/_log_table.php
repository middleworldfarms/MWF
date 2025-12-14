<?php
/**
 * Log table view file
 *
 * @link       
 *
 * @package  Wt_Import_Export_For_Woo 
 */
if (!defined('ABSPATH')) {
    exit;
}

$summary = array(
    'type' => array(
        0 => array(
            'count' => 0,
            'description' => __('Item with same ID already exists.', 'product-import-export-for-woo'),
            'help_link' => 'https://www.webtoffee.com/how-to-resolve-id-conflict-during-import-in-woocommerce/',
            'error_code' => 'already exists'
        ),
        1 => array(
            'count' => 0,
            'description' => __('Importing item conflicts with an existing post.', 'product-import-export-for-woo'),
            'help_link' => 'https://www.webtoffee.com/how-to-resolve-id-conflict-during-import-in-woocommerce/',
            'error_code' => 'conflicts with an existing post'
        ),
        2 => array(
            'count' => 0,
            'description' => __('Invalid product type.', 'product-import-export-for-woo'),
            'help_link' => 'https://www.webtoffee.com/setting-up-product-import-export-plugin-for-woocommerce/',
            'error_code' => 'Invalid product type'
        )
    )
);
if(isset($log_list) && is_array($log_list) && count($log_list)>0)
{
	if($offset==0)
	{
	?>
		<table class="wp-list-table widefat fixed striped log_view_tb" style="margin-bottom:25px;">
		<thead>
			<tr>
				<th style="width:100px;"><?php esc_html_e("Row No.", 'product-import-export-for-woo'); ?></th>
				<th><?php esc_html_e("Status", 'product-import-export-for-woo'); ?></th>
				<th><?php esc_html_e("Message", 'product-import-export-for-woo'); ?></th>
				<th><?php esc_html_e("Item", 'product-import-export-for-woo'); ?></th>
			</tr>
		</thead>
		<tbody class="log_view_tb_tbody">
	<?php
	}
	foreach($log_list as $key =>$log_item)
	{   
                if(!$log_item['status']){
                    if(strpos($log_item['message'], 'already exists')!==false){
                        $summary['type'][0]['count'] = $summary['type'][0]['count']+1;                      
                    }
                    if(strpos($log_item['message'], 'conflicts with an existing post')!==false){
                        $summary['type'][1]['count'] = $summary['type'][1]['count']+1;                       
                    }
                    if(strpos($log_item['message'], 'Invalid product type')!==false){
                        $summary['type'][2]['count'] = $summary['type'][2]['count']+1;                       
                    }
                }
		?>
		<tr>
			<td><?php echo absint($log_item['row']); ?></td>
			<td><?php echo esc_html($log_item['status'] ? __('Success', 'product-import-export-for-woo') : __('Failed/Skipped', 'product-import-export-for-woo') ); ?></td>
			<td><?php echo esc_html($log_item['message']); ?></td>
			<td>
			<?php 
				if($show_item_details)
				{
					$item_data=$item_type_module_obj->get_item_by_id($log_item['post_id']);					
					if($item_data && isset($item_data['title']))
					{
						if(isset($item_data['edit_url']))
						{
							echo '<a href="'.esc_url($item_data['edit_url']).'" target="_blank">'.esc_html($item_data['title']).'</a>';
						}else
						{
							echo esc_html($item_data['title']);
						}
					}else
					{
						echo esc_html($log_item['post_id']);
					}
				}else
				{
					echo esc_html($log_item['post_id']);	
				}
			?>
			</td>
		</tr>
		<?php	
	}?>
                <div style="background-color: #f6f7f7;padding: 10px;">
            <?php

            foreach ($summary['type'] as $summary_row) {
                $summary_row_count = $summary_row['count'];
                $summary_row_help_link = $summary_row['help_link'];
                if($summary_row_count):
                ?>
                    <p><?php echo wp_kses_post($summary_row['description']."($summary_row_count)");?> - <?php esc_html_e('Please refer', 'product-import-export-for-woo')?> <a href="<?php echo esc_url($summary_row_help_link); ?>" target="_blank"><?php esc_html_e('this article', 'product-import-export-for-woo');?></a> <?php esc_html_e('for troubleshoot.', 'product-import-export-for-woo');?></p> 
          <?php 
                endif;
          
            }
        ?>
        </div>  
        <?php    
	if($offset==0)
	{
	?>
		</tbody>
		</table>
		<h4 style="margin-top:0px;"> 
			<a class="wt_iew_history_loadmore_btn button button-primary"> <?php esc_html_e("Load more.", 'product-import-export-for-woo'); ?></a>
			<span class="wt_iew_history_loadmore_loading" style="display:none;"><?php esc_html_e("Loading....", 'product-import-export-for-woo'); ?></span>
		</h4>
	<?php
	}
}else
{
	?>
	<h4 style="margin-bottom:55px;"><?php esc_html_e("No records found.", 'product-import-export-for-woo'); ?> </h4>
	<?php
}
?>