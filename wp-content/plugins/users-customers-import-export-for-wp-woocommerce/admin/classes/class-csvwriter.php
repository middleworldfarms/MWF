<?php
/**
 * CSV writing section of the plugin
 *
 * @link       
 *
 * @package  Wt_Import_Export_For_Woo 
 */
if (!defined('ABSPATH')) {
    exit;
}
if(!class_exists('Wt_Import_Export_For_Woo_Basic_Csvwriter')){
	
#[AllowDynamicProperties]
class Wt_Import_Export_For_Woo_Basic_Csvwriter
{
	public $file_path='';
	public $data_ar='';
	public $csv_delimiter='';
	public $use_bom=true;
	public function __construct($file_path, $offset, $csv_delimiter=",", $use_bom=true)
	{
		$this->csv_delimiter=$csv_delimiter;
		$this->file_path=$file_path;
		$this->use_bom = $use_bom;
		$this->get_file_pointer($offset);
	}
	
	/**
	* This is used in XML to CSV converting 
	*/
	public function write_row($row_data, $offset=0, $is_last_offset=false)
	{
		if($is_last_offset)
		{
			$this->close_file_pointer();
		}else
		{
			if($offset==0) /* set heading */
			{
				$this->fput_csv($this->file_pointer, array_keys($row_data), $this->csv_delimiter);
			}
			$this->fput_csv($this->file_pointer, $row_data, $this->csv_delimiter);
		}
	}

	/**
	* 	Create CSV 
	*
	*/
	public function write_to_file($export_data, $offset, $is_last_offset, $to_export)
	{		
		$this->export_data=$export_data;	
		$this->set_head($export_data, $offset, $this->csv_delimiter);
		$this->set_content($export_data, $this->csv_delimiter);
		$this->close_file_pointer();
	}
	private function get_file_pointer($offset)
	{
		if($offset==0)
		{
			// phpcs:disable WordPress.WP.AlternativeFunctions.file_system_operations_fopen
        	$this->file_pointer=fopen($this->file_path, 'w');
			// phpcs:enable WordPress.WP.AlternativeFunctions.file_system_operations_fopen
			$this->use_bom = apply_filters('wt_ier_include_bom_in_csv', $this->use_bom);
			if($this->use_bom){
				$BOM = "\xEF\xBB\xBF"; // UTF-8 BOM
				// phpcs:disable WordPress.WP.AlternativeFunctions.file_system_operations_fwrite
				fwrite($this->file_pointer, $BOM); // NEW LINE
				// phpcs:enable WordPress.WP.AlternativeFunctions.file_system_operations_fwrite
			}
		}else
		{
			// phpcs:disable WordPress.WP.AlternativeFunctions.file_system_operations_fopen
			$this->file_pointer=fopen($this->file_path, 'a+');
			// phpcs:enable WordPress.WP.AlternativeFunctions.file_system_operations_fopen
		}
	}
	private function close_file_pointer()
	{
		if($this->file_pointer!=null) {
			// phpcs:disable WordPress.WP.AlternativeFunctions.file_system_operations_fclose
			fclose($this->file_pointer);
			// phpcs:enable WordPress.WP.AlternativeFunctions.file_system_operations_fclose
		}
	}
	/**
	 * Escape a string to be used in a CSV context
	 *
	 * Malicious input can inject formulas into CSV files, opening up the possibility
	 * for phishing attacks and disclosure of sensitive information.
	 *
	 * Additionally, Excel exposes the ability to launch arbitrary commands through
	 * the DDE protocol.
	 *
	 * @see http://www.contextis.com/resources/blog/comma-separated-vulnerabilities/
	 * @see https://hackerone.com/reports/72785
	 *
	 * @param string $data CSV field to escape.
	 * @return string
	 */
	public function escape_data( $data )
	{
		$active_content_triggers = array( '=', '+', '-', '@' );

		if ( in_array( mb_substr( $data, 0, 1 ), $active_content_triggers, true ) ) {
			$data = "'" . $data;
		}

		return $data;
	}
	public function format_data( $data )
	{
		if ( ! is_scalar( $data ) ) {
			if ( is_a( $data, 'WC_Datetime' ) ) {
				$data = $data->date( 'Y-m-d G:i:s' );
			} else {
				$data = ''; // Not supported.
			}
		} elseif ( is_bool( $data ) ) {
			$data = $data ? 1 : 0;
		}

		return $this->escape_data( wt_iew_utf8ize_basic( $data ) );
	}
	private function set_content($export_data, $delm=',')
	{
		if(isset($export_data) && isset($export_data['body_data']) && count($export_data['body_data'])>0)
		{
			$row_datas=array_values($export_data['body_data']);
			foreach($row_datas as $row_data)
			{
				foreach($row_data as $key => $value) 
				{
					$row_data[$key]=$this->format_data($value);
				}
				$this->fput_csv($this->file_pointer, $row_data, $delm);
			}			
		}
	}
	private function set_head($export_data, $offset, $delm=',')
	{
		if($offset==0 && isset($export_data) && isset($export_data['head_data']) && count($export_data['head_data'])>0)
		{
			foreach($export_data['head_data'] as $key => $value) 
			{
				$export_data['head_data'][$key]=$this->format_data($value);
			}
			$this->fput_csv($this->file_pointer, $export_data['head_data'], $delm);
		}
	}
	private function fput_csv($fp, $row, $delm=',', $encloser='"' )
	{
		fputcsv($fp,$row,$delm,$encloser);
	}
	private function array_to_csv($arr, $delm=',', $encloser='"') {
		// phpcs:disable WordPress.WP.AlternativeFunctions.file_system_operations_fopen
		$fp=fopen('php://memory','rw');
		// phpcs:enable WordPress.WP.AlternativeFunctions.file_system_operations_fopen
		foreach($arr as $row)
		{
			$this->fput_csv($fp, $row, $delm, $encloser);
		}
		rewind($fp);
		$csv=stream_get_contents($fp);
		// phpcs:disable WordPress.WP.AlternativeFunctions.file_system_operations_fclose
		fclose($fp);
		// phpcs:enable WordPress.WP.AlternativeFunctions.file_system_operations_fclose
		return $csv;
	}
}
}