<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class GPLVault_Log_Reader implements GPLVault_Log_Reader_Interface {

	/** @var string */
	private $filepath;
	/**
	 * @var null|false|resource
	 */
	private $file;

	/**
	 * @param string $filepath
	 */
	public function __construct( $filepath ) {
		$this->filepath = $filepath;
		if ( $this->fileExists() ) {
			// Using fopen for memory-efficient streaming of large log files
			$this->file = fopen( $this->filepath, 'rb' ); // @phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen
		}
	}

	public function __destruct() {
		if ( $this->file ) {
			fclose( $this->file ); // @phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
		}
	}

	public function getRows( $level_filter = '' ) {
		if ( is_resource( $this->file ) ) {
			while ( ( $line = fgets( $this->file ) ) !== false ) { // @phpcs:ignore WordPress.CodeAnalysis.AssignmentInCondition.FoundInWhileCondition
				preg_match( static::LOG_PATTERN, $line, $content );
				$ret = array(
					'message'   => trim( $content['message'] ),
					'level'     => strtolower( trim( $content['level'] ) ),
					'timestamp' => trim( $content['timestamp'] ),
				);

				if ( empty( $ret['level'] ) ) {
					continue;
				}

				if ( ! empty( $level_filter ) && strtolower( $level_filter ) !== $ret['level'] ) {
					continue;
				}

				yield $ret;
			}
		}

		return; // @phpcs:ignore Squiz.PHP.NonExecutableCode.ReturnNotRequired
	}

	public function fetchLines( $level_filter = '' ) {
		if ( ! is_resource( $this->file ) ) {
			return;
		}

		while ( ( $line = fgets( $this->file ) ) !== false ) { // @phpcs:ignore WordPress.CodeAnalysis.AssignmentInCondition.FoundInWhileCondition
			if ( empty( $level_filter ) ) {
				yield $line;
			} else {
				preg_match( static::LOG_PATTERN, $line, $content );
				if ( empty( $content['level'] ) || strtolower( $level_filter ) !== strtolower( $content['level'] ) ) {
					continue;
				}
				yield $line;
			}
		}

		return; // @phpcs:ignore
	}

	/**
	 * {@inheritdoc}
	 */
	public function fileExists() {
		return is_readable( $this->filepath );
	}
}
