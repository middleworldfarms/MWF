<?php

defined( 'ABSPATH' ) || exit;


abstract class GPLVault_Log_Handler implements GPLVault_Log_Handler_Interface {

	protected static function format_time( $timestamp ) {
		return gmdate( 'c', $timestamp );
	}

	protected static function format_entry( $timestamp, $level, $message, $context ) {
		$time_string  = self::format_time( $timestamp );
		$level_string = strtoupper( $level );
		$entry        = "[{$time_string}] {$level_string}: {$message}";

		return apply_filters(
			'gplvault_format_log_entry',
			$entry,
			array(
				'timestamp' => $timestamp,
				'level'     => $level,
				'message'   => $message,
				'context'   => $context,
			)
		);
	}
}
