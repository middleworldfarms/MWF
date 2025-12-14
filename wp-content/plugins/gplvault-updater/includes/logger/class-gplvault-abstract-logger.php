<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

abstract class GPLVault_Abstract_Logger implements GPLVault_Psr_Log_Interface {
	use GPLVault_Logger_Trait;
}
