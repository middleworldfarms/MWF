<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class GPLVault_Null_Logger extends GPLVault_Abstract_Logger {

	public function log( $level, $message, $context = array() ) {}
}
