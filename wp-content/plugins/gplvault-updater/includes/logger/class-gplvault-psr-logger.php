<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class GPLVault_Psr_Logger extends GPLVault_Abstract_Logger {

	const DEFAULT_DATETIME_FORMAT = 'c';

	/**
	 * @var GPLVault_Psr_Log_Handler_Interface
	 */
	private $handler;

	public function __construct( $handler ) {
		$this->handler = $handler;
	}

	/**
	 * Logs with an arbitrary level.
	 *
	 * @param mixed $level
	 * @param string $message
	 * @param array $context
	 *
	 * @return void
	 * @throws Exception
	 */
	public function log( $level, $message, $context = array() ): void {
		$data_time = new DateTimeImmutable( 'now', new DateTimeZone( 'UTC' ) );
		$this->handler->handle(
			array(
				'message'   => self::interpolate( $message, $context ),
				'level'     => strtoupper( $level ),
				'timestamp' => $data_time->format( self::DEFAULT_DATETIME_FORMAT ),
			)
		);
	}

	protected static function interpolate( $message, $context = array() ) {
		$replace = array();
		foreach ( $context as $key => $value ) {
			if ( is_string( $value ) || ( is_object( $value ) && method_exists( $value, '__toString' ) ) ) {
				$replace[ '{' . $key . '}' ] = method_exists( $value, '__toString' ) ?
					(string) $value : $value;
			}
		}

		return trim( strtr( $message, $replace ), " \n\r\t\v\x00" );
	}
}
