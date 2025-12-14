<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

trait GPLVault_Logger_Trait {

	/**
	 * System is unusable
	 *
	 * @param string $message
	 * @param array $context
	 *
	 * @return void
	 */
	public function emergency( $message, $context = array() ) {
		$this->log( GPLVault_Log_Levels::EMERGENCY, $message, $context );
	}

	/**
	 * Action must be taken immediately.
	 *
	 * Example: Entire website down, database unavailable, etc. This should
	 * trigger the SMS alerts and wake you up.
	 *
	 * @param string $message
	 * @param array $context
	 *
	 * @return void
	 */
	public function alert( $message, $context = array() ) {
		$this->log( GPLVault_Log_Levels::ALERT, $message, $context );
	}

	/**
	 * Critical conditions.
	 *
	 * Example: Application component unavailable, unexpected exception.
	 *
	 * @param string $message
	 * @param array $context
	 *
	 * @return void
	 */
	public function critical( $message, $context = array() ) {
		$this->log( GPLVault_Log_Levels::CRITICAL, $message, $context );
	}


	/**
	 * Runtime errors that do not require immediate action but should typically
	 * be logged and monitored.
	 *
	 * @param string $message
	 * @param array $context
	 *
	 * @return void
	 */
	public function error( $message, $context = array() ) {
		$this->log( GPLVault_Log_Levels::ERROR, $message, $context );
	}

	/**
	 * Exceptional occurrences that are not errors.
	 *
	 * Example: Use of deprecated APIs, poor use of an API, undesirable things
	 * hat are not necessarily wrong.
	 *
	 * @param string $message
	 * @param array $context
	 *
	 * @return void
	 */
	public function warning( $message, $context = array() ) {
		$this->log( GPLVault_Log_Levels::WARNING, $message, $context );
	}

	/**
	 * Normal but significant events.
	 *
	 * @param string $message
	 * @param array $context
	 *
	 * @return void
	 */
	public function notice( $message, $context = array() ) {
		$this->log( GPLVault_Log_Levels::NOTICE, $message, $context );
	}

	/**
	 * Interesting events.
	 *
	 * Example: User logs in, SQL logs.
	 *
	 * @param string $message
	 * @param array $context
	 *
	 * @return void
	 */
	public function info( $message, $context = array() ) {
		$this->log( GPLVault_Log_Levels::INFO, $message, $context );
	}

	/**
	 * Detailed debug information.
	 *
	 * @param string $message
	 * @param array $context
	 *
	 * @return void
	 */
	public function debug( $message, $context = array() ) {
		$this->log( GPLVault_Log_Levels::DEBUG, $message, $context );
	}

	/**
	 * { @inheritdoc }
	 */
	abstract public function log( $level, $message, $context = array());
}
