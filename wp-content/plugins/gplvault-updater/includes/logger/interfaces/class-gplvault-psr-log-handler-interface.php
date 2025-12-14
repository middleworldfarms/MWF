<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

interface GPLVault_Psr_Log_Handler_Interface {
	const DEFAULT_FORMAT = '%timestamp% [%level%]: %message%';

	public function handle( $vars);
}
