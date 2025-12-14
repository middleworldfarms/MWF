<?php
if(!class_exists('Wt_Import_Export_For_Woo_Basic_Common_Helper')){
class Wt_Import_Export_For_Woo_Basic_Common_Helper
{
	
    public static $min_version_msg='';

    /**
    * Get File name by url
    * @param string $file_url URL of the file.
    * @return string the base name of the given URL (File name).
    */
   public static function wt_wc_get_filename_from_url( $file_url ) {
       $parts = wp_parse_url( $file_url );
       if ( isset( $parts['path'] ) ) {
           return basename( $parts['path'] );
       }
   }

   /**
    * Get info like language code, parent product ID etc by product id.
    * @param int Product ID.
    * @return array/false.
    */
   public static function wt_get_wpml_original_post_language_info($element_id){
       $get_language_args = array('element_id' => $element_id, 'element_type' => 'post_product');
       $original_post_language_info = apply_filters('wpml_element_language_details', null, $get_language_args);
       return $original_post_language_info;
   }

   public static function wt_get_product_id_by_sku($sku){
       global $wpdb;
       // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
       $post_exists_sku = $wpdb->get_var($wpdb->prepare("
                   SELECT $wpdb->posts.ID
                   FROM $wpdb->posts
                   LEFT JOIN $wpdb->postmeta ON ( $wpdb->posts.ID = $wpdb->postmeta.post_id )
                   WHERE $wpdb->posts.post_status IN ( 'publish', 'private', 'draft', 'pending', 'future' )
                   AND $wpdb->postmeta.meta_key = '_sku' AND $wpdb->postmeta.meta_value = %s
                   ", $sku));
       // phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
       if ($post_exists_sku) {
           return $post_exists_sku;
       }
       return false;

   }

   /**
    * To strip the specific string from the array key as well as value.
    * @param array $array.
    * @param string $data.
    * @return array.
    */
   public static function wt_array_walk($array , $data) {
       $new_array =array();
       foreach ($array as $key => $value) {
           $new_array[str_replace($data, '', $key)] = str_replace($data, '', $value);
       }
       return $new_array;
   }

   /**
   *  Check the minimum base version required for post type modules
   *
   */
    public static function check_base_version($post_type, $post_type_title, $min_version)
    {
        $warn_icon='<span class="dashicons dashicons-warning"></span>&nbsp;';
        if(!version_compare(WT_U_IEW_VERSION, $min_version, '>=')) /* not matching the min version */
        {
            /* translators: $1: Post type title, $2: Plugin name, $3: Minimum version, $4: Plugin name */
            self::$min_version_msg.=$warn_icon.sprintf(__('The %1$s requires a minimum version of %2$s %3$s. Please upgrade the %4$s accordingly.', 'users-customers-import-export-for-wp-woocommerce'), "<b>$post_type_title</b>", "<b>".WT_U_IEW_PLUGIN_NAME."</b>", "<b>v$min_version</b>", "<b>".WT_U_IEW_PLUGIN_NAME."</b>").'<br />';
            add_action('admin_notices', array(__CLASS__, 'no_minimum_base_version') );
            return false;
        }
        return true;
    }

    /**
    *
    *   No minimum version error message
    */
    public static function no_minimum_base_version()
    {
        ?>
        <div class="notice notice-warning">
            <p>
                <?php 
                echo wp_kses_post(self::$min_version_msg);
                ?>
            </p>
        </div>
        <?php
    }

    /**
    *   Decode the post data as normal array from json encoded from data.
    *   If step key is specified, then it will return the data corresponds to the form key
    *   @param array $form_data
    *   @param string $key
    */
    public static function process_formdata($form_data, $key='')
    {
        if($key!="") /* if key is given then take its data */
        {
            if(isset($form_data[$key]))
            {
                if(is_array($form_data[$key]))
                {
                    $form_data_vl=$form_data[$key];
                }else
                {
                    $form_data_vl=json_decode(stripslashes($form_data[$key]),true);
                } 
            }else
            {
                $form_data_vl=array();
            }
        }else
        {
            $form_data_vl=array();
            foreach($form_data as $form_datak=>$form_datav)
            {
                $form_data_vl[$form_datak]=self::process_formdata($form_data, $form_datak);
            }
        }
        return (is_array($form_data_vl) ? $form_data_vl : array());
    }

    /**
     * Safe custom unserialize function that handles only basic types
     * 
     * @since 2.6.3 Added to ensure data migration from serialized to JSON format
     * @param string $data Serialized data
     * @return mixed Unserialized data (only int, string, bool, array)
     */
    public static function wt_unserialize_safe($data) {

        if( empty($data) ) {
            return false;
        } 
        $offset = 0;
    
        // Recursive function to handle different types.
        $unserialize_value = function(&$offset) use ($data, &$unserialize_value) {
            $type = $data[$offset];
            $offset++;
    
            switch ($type) {
                case 's': // String.
                    preg_match('/:(\d+):"/', $data, $matches, 0, $offset);
                    $length = (int) $matches[1];
                    $offset += strlen($matches[0]);
                    $value = substr($data, $offset, $length);
                    $offset += $length + 2; // Skip closing quotes and semicolon.
                    return $value;
    
                case 'i': // Integer.
                    preg_match('/:(-?\d+);/', $data, $matches, 0, $offset);
                    $offset += strlen($matches[0]);
                    return (int) $matches[1];
    
                case 'd': // Float/Double.
                    preg_match('/:(-?\d+(\.\d+)?);/', $data, $matches, 0, $offset);
                    $offset += strlen($matches[0]);
                    return (float) $matches[1];
    
                case 'b': // Boolean.
                    preg_match('/:(\d);/', $data, $matches, 0, $offset);
                    $offset += strlen($matches[0]);
                    return (bool) $matches[1];
    
                case 'N': // NULL.
                    $offset += 1; // Move past ';'.
                    return false;
    
                case 'a': // Array.
                    preg_match('/:(\d+):{/', $data, $matches, 0, $offset);
                    $num_elements = (int) $matches[1];
                    $offset += strlen($matches[0]);
    
                    $result = array();
                    for ($i = 0; $i < $num_elements; $i++) {
                        $key = $unserialize_value($offset);
                        $value = $unserialize_value($offset);
                        $result[$key] = $value;
                    }
    
                    $offset++; // Move past closing '}'.
                    return $result;
    
                case 'O': // Object (Convert to Array).
                    preg_match('/:(\d+):"([^"]+)":(\d+):{/', $data, $matches, 0, $offset);
                    $num_properties = (int) $matches[3];
                    $offset += strlen($matches[0]);
    
                    $result = array();
                    for ($i = 0; $i < $num_properties; $i++) {
                        $key = $unserialize_value($offset);
                        $value = $unserialize_value($offset);
                        $result[$key] = $value;
                    }
    
                    $offset++; // Move past closing '}'.
                    return $result; // Object converted into an array.
    
                default:
                    // Skip unsupported type.
                    return false;
            }
        };
    
        return $unserialize_value($offset);
    }

    /**
	* Decode template data - handles both serialized and JSON data
	* @since 2.6.3
	* @param string $data The data to decode
	* @return array The decoded data as array
	*/
	public static function decode_template_data($data) 
	{
		// Return empty array if data is empty
		if (empty($data)) {
			return array();
		}

		// If data is already an array, return it
		if (is_array($data)) {
			return $data;
		}

		// Check if data is serialized first
		if (is_serialized($data)) {
			$unserialized_data = Wt_Import_Export_For_Woo_Basic_Common_Helper::wt_unserialize_safe($data);
			if ($unserialized_data !== false) {
				return $unserialized_data;
			}
		}

		// Try JSON decode
		$decoded = json_decode($data, true);
		if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
			return $decoded;
		}

		// Return empty array if all decoding attempts fail
		return array();
	}

    /**
    *   Form field generator
    */
    public static function field_generator($form_fields, $form_data)
    {
        include plugin_dir_path( dirname( __FILE__ ) ).'admin/partials/_form_field_generator.php';
    }


    /**
    *   Save advanced settings
    *   @param  array   $settings   array of setting values
    */
    public static function set_advanced_settings($settings)
    {
        update_option('wt_iew_advanced_settings', $settings);
    }

    /**
    *
    *   Extract validation rule from form field array
    *   @param  array   $fields   form field array
    */
    public static function extract_validation_rules($fields)
    {
        $out=array_map(function ($r) { return (isset($r['validation_rule']) ? $r['validation_rule'] : ''); }, $fields);
        return array_filter($out);
    }

    /**
    *   Get advanced settings.
    *   @param      string  $key    key for specific setting (optional)
    *   @return     mixed   if key provided then the value of key otherwise array of values
    */
    public static function get_advanced_settings($key="")
    {
        $advanced_settings=get_option('wt_iew_advanced_settings');       
        $advanced_settings=($advanced_settings ? $advanced_settings : array());
        if($key!="")
        {
            $key=(substr($key,0,8)!=='wt_iew_' ? 'wt_iew_' : '').$key;
            if(isset($advanced_settings[$key]))
            {
                return $advanced_settings[$key];
            }else
            {
                $default_settings=self::get_advanced_settings_default();
                return (isset($default_settings[$key]) ? $default_settings[$key] : '');
            }
        }else
        {
            $default_settings=self::get_advanced_settings_default();            
            $advanced_settings=wp_parse_args($advanced_settings, $default_settings);
            return $advanced_settings; 
        }
    }

    /**
    *   Get default value of advanced settings
    *   @return     array   array of default values
    *
    */
    public static function get_advanced_settings_default()
    {
        $fields=self::get_advanced_settings_fields();      
        foreach ($fields as $key => $value)
        {
            if(isset($value['value']))
            {
                $key=(substr($key,0,8)!=='wt_iew_' ? 'wt_iew_' : '').$key;
                $out[$key]=$value['value'];
            }
        }
        return $out;
    }

    /**
    *   Get advanced fields
    *   @return     array   array of fields
    *
    */
    public static function get_advanced_settings_fields()
    {
        $fields=array();
        return apply_filters('wt_iew_advanced_setting_fields_basic', $fields);
    }
    
    public static function wt_allowed_screens(){
        $screens=array('wt_import_export_for_woo_basic','wt_import_export_for_woo_basic_export','wt_import_export_for_woo_basic_import','wt_import_export_for_woo_basic_history','wt_import_export_for_woo_basic_history_log');
        return apply_filters('wt_iew_allowed_screens_basic', $screens);

    }
    public static function wt_get_current_page(){   
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce not required.
        return isset($_GET['page']) ? sanitize_text_field(wp_unslash($_GET['page'])) : '';
    }
    
    public static function wt_is_screen_allowed(){
        if(in_array(self::wt_get_current_page(), self::wt_allowed_screens())){
            return true;
        }else{
            return false;
        }
    }
}
}

if(!function_exists('is_woocommerce_prior_to_basic')){
    function is_woocommerce_prior_to_basic($version) {

        $woocommerce_is_pre_version = (!defined('WC_VERSION') || version_compare(WC_VERSION, $version, '<')) ? true : false;
        return $woocommerce_is_pre_version;

        if (WC()->version < $version) {
            return TRUE;
        } else {
            return FALSE;
        }
    }
}

if(!function_exists('wt_let_to_num_basic')){
function wt_let_to_num_basic( $size ) {
	$l   = substr( $size, -1 );
	$ret = (int) substr( $size, 0, -1 );
	switch ( strtoupper( $l ) ) {
		case 'P':
			$ret *= 1024;
			// No break.
		case 'T':
			$ret *= 1024;
			// No break.
		case 'G':
			$ret *= 1024;
			// No break.
		case 'M':
			$ret *= 1024;
			// No break.
		case 'K':
			$ret *= 1024;
			// No break.
	}
	return $ret;
}
}

if(!function_exists('wt_removeBomUtf8_basic')){
function wt_removeBomUtf8_basic($s) {
    if (substr($s, 0, 3) == chr(hexdec('EF')) . chr(hexdec('BB')) . chr(hexdec('BF'))) {
        return substr($s, 3);
    } else {
        return $s;
    }
}
}

if (!function_exists('wt_iew_utf8ize_basic')) {
    function wt_iew_utf8ize_basic($data) {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = wt_iew_utf8ize_basic($value);
            }
        } else if (is_string($data)) {
            // Use mbstring if available
            if (extension_loaded('mbstring')) {
                $encoding = mb_detect_encoding($data, ['UTF-8', 'ISO-8859-1', 'Windows-1252'], true);
                if ($encoding !== 'UTF-8') {
                    return mb_convert_encoding($data, 'UTF-8', $encoding ?: 'ISO-8859-1');
                }
                return $data; // Already UTF-8
            } else {
                // Fallback: utf8_encode (assumes ISO-8859-1)
                if (function_exists('utf8_encode')) { 
                    return utf8_encode($data);
                } else {
                    return $data;
                }
            }
        }
        
        return $data;
    }
}

/**
 * Outputs a HTML element with a star rating for a given rating.
 *
 * Outputs a HTML element with the star rating exposed on a 0..5 scale in
 * half star increments (ie. 1, 1.5, 2 stars). Optionally, if specified, the
 * number of ratings may also be displayed by passing the $number parameter.
 *
 * @since 3.8.0 - WP
 * @since 4.4.0 Introduced the `echo` parameter. - WP
 *
 * @param array $args {
 *     Optional. Array of star ratings arguments.
 *
 *     @type int|float $rating The rating to display, expressed in either a 0.5 rating increment,
 *                             or percentage. Default 0.
 *     @type string    $type   Format that the $rating is in. Valid values are 'rating' (default),
 *                             or, 'percent'. Default 'rating'.
 *     @type int       $number The number of ratings that makes up this rating. Default 0.
 *     @type bool      $echo   Whether to echo the generated markup. False to return the markup instead
 *                             of echoing it. Default true.
 * }
 * @return string Star rating HTML.
 */
if(!function_exists('wt_wp_star_rating')){
function wt_wp_star_rating( $args = array() ) {
	$defaults    = array(
		'rating' => 0,
		'type'   => 'rating',
		'number' => 0,
		'echo'   => true,
	);
	$parsed_args = wp_parse_args( $args, $defaults );

	// Non-English decimal places when the $rating is coming from a string.
	$rating = (float) str_replace( ',', '.', $parsed_args['rating'] );

	// Convert percentage to star rating, 0..5 in .5 increments.
	if ( 'percent' === $parsed_args['type'] ) {
		$rating = round( $rating / 10, 0 ) / 2;
	}

	// Calculate the number of each type of star needed.
	$full_stars  = floor( $rating );
	$half_stars  = ceil( $rating - $full_stars );
	$empty_stars = 5 - $full_stars - $half_stars;

	if ( $parsed_args['number'] ) {
		/* translators: 1: The rating, 2: The number of ratings. */
		$format = _n( '%1$s rating based on %2$s rating', '%1$s rating based on %2$s ratings', $parsed_args['number'], 'users-customers-import-export-for-wp-woocommerce' );
		$title  = sprintf( $format, number_format_i18n( $rating, 1 ), number_format_i18n( $parsed_args['number'] ) );
	} else {
		/* translators: %s: The rating. */
		$title = sprintf( __( '%s rating', 'users-customers-import-export-for-wp-woocommerce' ), number_format_i18n( $rating, 1 ) );
	}

	$output  = '<div class="wt-star-rating">';
	$output .= '<span class="screen-reader-text">' . $title . '</span>';
	$output .= str_repeat( '<div class="wt-star wt-star-full" aria-hidden="true"></div>', $full_stars );
	$output .= str_repeat( '<div class="wt-star wt-star-half" aria-hidden="true"></div>', $half_stars );
	$output .= str_repeat( '<div class="wt-star wt-star-empty" aria-hidden="true"></div>', $empty_stars );
	$output .= '</div>';

	if ( $parsed_args['echo'] ) {
		echo wp_kses_post($output);
	}

	return $output;
}
}