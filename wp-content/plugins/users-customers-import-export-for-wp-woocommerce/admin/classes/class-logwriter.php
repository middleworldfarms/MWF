<?php
/**
 * Log writing section of the plugin
 *
 * @link       
 *
 * @package  Wt_Import_Export_For_Woo 
 */
if (!defined('ABSPATH')) {
    exit;
}
if(!class_exists('Wt_Import_Export_For_Woo_Basic_Logwriter')){
class Wt_Import_Export_For_Woo_Basic_Logwriter extends Wt_Import_Export_For_Woo_Basic_Log
{
	private static $file_path='';
	private static $file_pointer=null;
	private static $mode='';
	public function __construct()
	{
		
	}
	public static function init($file_path, $mode="a+")
	{
		self::$file_path=$file_path;
		self::$mode=$mode;
		// phpcs:disable WordPress.WP.AlternativeFunctions.file_system_operations_fopen
		self::$file_pointer=@fopen($file_path, $mode);
		// phpcs:enable WordPress.WP.AlternativeFunctions.file_system_operations_fopen
	}
	public static function write_row($text, $is_writing_finished=false)
	{
		if(is_null(self::$file_pointer)) {
			return;
		}
		// phpcs:disable WordPress.WP.AlternativeFunctions.file_system_operations_fwrite
		@fwrite(self::$file_pointer, $text.PHP_EOL);
		// phpcs:enable WordPress.WP.AlternativeFunctions.file_system_operations_fwrite
		if($is_writing_finished) {
			self::close_file_pointer();
		}
	}
	public static function close_file_pointer()
	{
		if(self::$file_pointer!=null) {
			// phpcs:disable WordPress.WP.AlternativeFunctions.file_system_operations_fclose
			fclose(self::$file_pointer);
			// phpcs:enable WordPress.WP.AlternativeFunctions.file_system_operations_fclose
		}
	}
	
	/**
	*	Debug log writing function
	*	@param string 	$post_type 		post type
	*	@param string 	$action_type	action type
	*	@param mixed 	$data			array/string of data to write
	*/
        public static function write_log( $post_type, $action_type, $data ) {

			if ( Wt_Import_Export_For_Woo_Basic_Common_Helper::get_advanced_settings( 'enable_import_log' ) == 1 ) {
				/**
				 * 	Checks log file created for the current day
				 */
				$old_file_name = self::check_log_exists_for_entry( self::$history_id );
				if ( !$old_file_name ) {
					$file_name = self::generate_file_name( $post_type, $action_type, self::$history_id );
				} else {
					$file_name = $old_file_name;
				}
				$file_path	 = self::get_file_path( $file_name );
				self::init( $file_path );
				$date_string = date_i18n( 'm-d-Y @ H:i:s' );
				if ( is_array( $data ) ) {
					foreach ( $data as $value ) {
						self::write_row( $date_string . " - " . wp_json_encode( $value ) );
					}
				} else {
					self::write_row( $date_string . " - " . $data );
				}
			}
	}

		/**
	*	Import response log
	*	@param array 	$data		array/string of import response data
	*	@param string 	$file_path	import log file
	*/
	public static function write_import_log($data_arr, $file_path)
	{
		self::init($file_path);
		foreach($data_arr as $key => $data)
		{
			self::write_row(wp_json_encode($data));
		}
		self::close_file_pointer();
	}
}
}