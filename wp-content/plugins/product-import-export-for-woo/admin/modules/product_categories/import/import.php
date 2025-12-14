<?php

if (!defined('WPINC')) {
    exit;
}

#[AllowDynamicProperties]
class Wt_Import_Export_For_Woo_Basic_Categories_Import {

    public $parent_module = null;
    public $parsed_data = array();
    var $is_update;
    var $taxonomy_type;
    var $import_results = array();
    var $row;

    public function __construct($parent_object) {

        $this->parent_module = $parent_object;

        $this->taxonomy_post_defaults = apply_filters('wt_categories_csv_product_post_columns', array(
            'term_id' => 'term_id',
            'name' => 'name',
            'slug' => 'slug',
            'description' => 'description',
            'parent' => 'parent',
            'thumbnail' => 'thumbnail',
        ));
    }

    public function hf_log_data_change($content = 'categories-csv-import', $data = '') {

        Wt_Import_Export_For_Woo_Basic_Logwriter::write_log($this->parent_module->module_base, 'import', $data);
    }

    public function prepare_data_to_import($import_data, $form_data, $batch_offset, $is_last_batch) {

        $this->is_update = isset($form_data['advanced_form_data']['wt_iew_merge']) ? $form_data['advanced_form_data']['wt_iew_merge'] : 0;
        $this->taxonomy_type =  'product_cat';

        Wt_Import_Export_For_Woo_Basic_Logwriter::write_log($this->parent_module->module_base, 'import', "Preparing for import.");

        $success = 0;
        $failed = 0;
        $msg = 'Category imported successfully.';
        foreach ($import_data as $key => $data) {
            $row = $batch_offset + $key + 1;
            Wt_Import_Export_For_Woo_Basic_Logwriter::write_log($this->parent_module->module_base, 'import', "Row :$row - Parsing item.");
            $parsed_data = $this->parse_product_categories($data, $this->is_update, $this->taxonomy_type);
            if (!is_wp_error($parsed_data)) {
                Wt_Import_Export_For_Woo_Basic_Logwriter::write_log($this->parent_module->module_base, 'import', "Row :$row - Processing item.");
                $result = $this->process_product_categories($parsed_data, $this->is_update, $this->taxonomy_type);

                if (!is_wp_error($result)) {
                    if (isset($result['status']) && $result['status'] == 'updated') {
                        $msg = 'Category updated successfully.';
                    }

                    $this->import_results[$row] = array('row'=>$row, 'message'=>$msg, 'status'=>true, 'status_msg' => __( 'Success', 'product-import-export-for-woo' ), 'post_id'=>$result['id'], 'post_link' => Wt_Import_Export_For_Woo_Basic_Product_Categories::get_item_link_by_id($result['id'])); 
                    Wt_Import_Export_For_Woo_Basic_Logwriter::write_log($this->parent_module->module_base, 'import', "Row :$row - " . $msg);
                    $success++;
                } else {

                    $this->import_results[$row] = array('row'=>$row, 'message'=>$result->get_error_message(), 'status'=>false, 'status_msg' => __( 'Failed/Skipped', 'product-import-export-for-woo' ), 'post_id'=>'', 'post_link' => array( 'title' => __( 'Untitled', 'product-import-export-for-woo' ), 'edit_url' => false ) );
                    Wt_Import_Export_For_Woo_Basic_Logwriter::write_log($this->parent_module->module_base, 'import', "Row :$row - Prosessing failed. Reason: " . $result->get_error_message());
                    $failed++;
                }
            } else {
                $this->import_results[$row] = array('row'=>$row, 'message'=>$parsed_data->get_error_message(), 'status'=>false, 'status_msg' => __( 'Failed/Skipped', 'product-import-export-for-woo' ), 'post_id'=>'', 'post_link' => array( 'title' => __( 'Untitled', 'product-import-export-for-woo' ), 'edit_url' => false ) );
                Wt_Import_Export_For_Woo_Basic_Logwriter::write_log($this->parent_module->module_base, 'import', "Row :$row - Parsing failed. Reason: " . $parsed_data->get_error_message());
                $failed++;
            }
            unset($data, $parsed_data);
        }

        $this->clean_after_import();

        $import_response = array(
            'total_success' => $success,
            'total_failed' => $failed,
            'log_data' => $this->import_results,
        );

        return $import_response;
    }

    /**
     * Parse product review
     * @param  array  $item
     * @return array
     */
    public function parse_product_categories($data, $is_update, $taxonomy_type) {
        try {
            $data = apply_filters('wt_woocommerce_product_categories_importer_pre_parse_data', $data);

            $item = $data['mapping_fields'];

            return $item;
        } catch (Exception $e) {
            return new WP_Error('woocommerce_product_importer_error', $e->getMessage(), array('status' => $e->getCode()));
        }
    }

    /**
     * Create new taxonomy based on import information
     */
    public function process_product_categories($post, $is_update, $taxonomy_type) {
        try {

            $term_data = $this->process_taxonomy_by_type($post, $is_update, $taxonomy_type);
            return $term_data;
        } catch (Exception $e) {
            return new WP_Error('woocommerce_product_importer_error', $e->getMessage(), array('status' => $e->getCode()));
        }
    }

    public function process_taxonomy_by_type($data, $is_update = 0, $taxonomy_type = 'product_cat') {


        $name = isset($data['name']) ? $data['name'] : '';
        $slug = isset($data['slug']) ? $data['slug'] : '';
        $term_id = isset($data['term_id']) ? $data['term_id'] : '';
        $description = isset($data['description']) ? $data['description'] : '';
        $display_type = isset($data['display_type']) ? $data['display_type'] : '';

        $parent_id = (isset($data['parent']) && ($data['parent'] != 0 )) ? $data['parent'] : '';

        global $wpdb;

        switch ($taxonomy_type) {

            case 'product_cat':
                $tax_type = 'product_cat';
                $term_meta_tbl_key = 'orginal_term_id';
                break;
            case 'product_tag':
                $tax_type = 'product_tag';
                $term_meta_tbl_key = 'orginal_product_tag_term_id';
                break;
        }
        $pid = '';
        if ($parent_id) {
            $pid = $parent_id;
        }
        $term_name = $name;
        $taxonomy_name = $tax_type;
        $related_data = array(
            'name' => $name,
            'description' => $description,
            'slug' => $slug);
        if ($pid) {
            $related_data['parent'] = $pid;
        }
		
        $tid = '';

        // Check by term_id.
        if ( '' !== $term_id ) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
            $chk_term_id = $wpdb->get_results($wpdb->prepare("SELECT t.term_id, t.slug FROM $wpdb->terms AS t INNER JOIN $wpdb->term_taxonomy as tt ON tt.term_id = t.term_id WHERE t.term_id = %d and tt.taxonomy = %s ORDER BY t.term_id", $term_id, $tax_type), ARRAY_A);
            if ( ! empty($chk_term_id[0]['term_id']) ) {
                $tid = $chk_term_id[0]['term_id'];

            // Check by original term_id in termmeta.
            } else {
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
                $chk_meta = $wpdb->get_results($wpdb->prepare("SELECT term_id FROM $wpdb->termmeta WHERE meta_key = %s and meta_value = %d ORDER BY meta_key,meta_id", $term_meta_tbl_key, $term_id), ARRAY_A);
                if ( ! empty($chk_meta[0]['term_id']) ) {
                    $tid = $chk_meta[0]['term_id'];
                }
            }
        }

        // Check by slug if term_id check didn't find anything.
        if ( '' !== $slug && '' === $tid ) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
            $chk_slug = $wpdb->get_row($wpdb->prepare("SELECT t.term_id, t.slug FROM $wpdb->terms AS t INNER JOIN $wpdb->term_taxonomy as tt ON tt.term_id = t.term_id WHERE t.slug = %s and tt.taxonomy = %s ORDER BY t.term_id", rawurlencode($slug), $tax_type), ARRAY_A);
            if ( ! empty($chk_slug['term_id']) ) {
                $tid = $chk_slug['term_id'];
            }
        }

        $status = array();
        if ($taxonomy_type == 'product_tag' || $taxonomy_type == 'product_cat') {

            if ($tid == '') {

                if (!empty($data['slug']) || '' !== $name) {

                    if ($taxonomy_type == 'product_tag' || $taxonomy_type == 'product_cat') {

                        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
                        $res = $wpdb->get_results($wpdb->prepare("SELECT term_id FROM $wpdb->termmeta WHERE meta_key = %s and meta_value = %d ORDER BY meta_key,meta_id", $term_meta_tbl_key, $parent_id), ARRAY_A);
						if(empty($res)){
                            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
							$res = $wpdb->get_results($wpdb->prepare("SELECT term_id FROM $wpdb->terms WHERE term_id = %d ORDER BY term_id", $parent_id), ARRAY_A);
						}

                        if (!empty($res)) {
                            $pid = $res[0]['term_id'];
                        }
                        $related_data['parent'] = $pid;
                    }
                    if (!empty($parent_id) && empty($res)) {

                        $status = array(
                            'name' => $name,
                            'status' => 'Data skipped parent not found',
                        );
                        return new WP_Error('data-error', '> Data skipped parent not found');
                    } else {

                        $cid = wp_insert_term($term_name, $taxonomy_name, $related_data);
                        if (is_wp_error($cid)) {
                            return new WP_Error('data-error', $cid->get_error_message());
                        }

                        $cid = $cid['term_id'];
                        if(!empty($term_id))
                        update_term_meta($cid, $term_meta_tbl_key, $term_id);

                        if ($taxonomy_type == 'product_cat') {

                            $thumbnail = isset($data['thumbnail']) ? $data['thumbnail'] : '';
                            if ($thumbnail != "") {

                                $image_url = $data['thumbnail'];
                                $attach_id = $this->image_library_attachment($image_url);
                                update_term_meta($cid, 'thumbnail_id', absint($attach_id));
                            }
                        }
                        if(!empty($display_type)){
                            update_term_meta($cid, 'display_type', $display_type);
                        }
                        $status = array(
                            'id' => $cid,
                            'name' => $name,
                            'status' => 'imported',
                        );
                        unset($cid);
                    }
                }else{
					return new WP_Error('data-error', 'Missing category details to insert');
				}
            } else {

                if ($is_update) {
                    $update = wp_update_term($tid, $taxonomy_name, $related_data);
                    if ($taxonomy_type == 'product_cat') {
                        $thumbnail = isset($data['thumbnail']) ? $data['thumbnail'] : '';
                        if ($thumbnail != "") {

                            $thumbnail_id = get_term_meta($tid, 'thumbnail_id', true);
                            $thumbnail_url = wp_get_attachment_url($thumbnail_id);
                            $existing_filename = basename($thumbnail_url);

                            $image_url = $thumbnail;
                            $current_filename = basename($image_url);
                            if ($current_filename != $existing_filename) {

                                $attach_id = $this->image_library_attachment($image_url);

                                update_term_meta($tid, 'thumbnail_id', absint($attach_id));
                            }
                        }
                    }
                        
                    if (!empty($display_type)) {
                        update_term_meta($tid, 'display_type', $display_type);
                    }
                    $status = array(
                        'id' => $tid,
                        'name' => $name,
                        'status' => 'updated',
                    );
                } else {
                    return new WP_Error('data-exist', '> Category skipped - already exist');
                }
                if($term_id)
                update_term_meta($tid, $term_meta_tbl_key, $term_id);
            }
        }

        unset($chk);
        return $status;
    }

    /**
     * Method used for attach image file to wp library
     *  
     * $image_url is url
     * return attachment id
     */
    public function image_library_attachment($image_url)
    {

        $allowed_extensions = apply_filters('wt_category_thumbnail_allowed_image_extensions', array('jpg', 'jpeg', 'png', 'gif'));


        $file_extension = strtolower(pathinfo($image_url, PATHINFO_EXTENSION));

        if (!in_array($file_extension, $allowed_extensions)) {
            return false;
        }

        $upload_dir = wp_upload_dir();

        $image_data = file_get_contents($image_url);

        $filename = basename($image_url);

        if (wp_mkdir_p($upload_dir['path'])) {
            $file = $upload_dir['path'] . '/' . $filename;
        } else {
            $file = $upload_dir['basedir'] . '/' . $filename;
        }

        file_put_contents($file, $image_data);

        $wp_filetype = wp_check_filetype($filename, null);

        $attachment = array(
            'post_mime_type' => $wp_filetype['type'],
            'post_title' => sanitize_file_name($filename),
            'post_content' => '',
            'post_status' => 'inherit'
        );

        $attach_id = wp_insert_attachment($attachment, $file);
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        $attach_data = wp_generate_attachment_metadata($attach_id, $file);
        wp_update_attachment_metadata($attach_id, $attach_data);

        return $attach_id;
    }

    public function clean_after_import() {
        wp_suspend_cache_invalidation(false);
        wp_defer_term_counting(false);
        wp_defer_comment_counting(false);

    }

}
