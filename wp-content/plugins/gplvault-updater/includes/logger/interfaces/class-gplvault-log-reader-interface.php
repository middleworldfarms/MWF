<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

interface GPLVault_Log_Reader_Interface {
	const LOG_PATTERN = '#(?P<timestamp>\d{4}-\d{1,2}-\d{1,2}.*?)\s*\[(?P<level>.+?)\]:\s*(?P<message>.*)#';

	/**
	 * Fetch lines from a log file
	 * It fetches all lines of log file if empty string is provided, otherwise
	 * it will fetch only lines of matched level string
	 *
	 * @param string $level_filter
	 */
	public function getRows( $level_filter = '');

	/**
	 * Fetch lines from a log file
	 * It fetches all lines of log file if empty string is provided, otherwise
	 * it will fetch only lines of matched level string
	 *
	 * @param string $level_filter
	 */
	public function fetchLines( $level_filter = '');

	/**
	 * Checks if the log file is readable
	 * @return bool
	 */
	public function fileExists();
}
