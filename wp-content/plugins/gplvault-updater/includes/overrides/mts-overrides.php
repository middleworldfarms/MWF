<?php
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'mts_connection', false ) ) :
	class mts_connection {} // phpcs:ignore PEAR.NamingConventions.ValidClassName.StartWithCapital, PEAR.NamingConventions.ValidClassName.Invalid
endif;

defined( 'MTS_CONNECT_ACTIVE' ) || define( 'MTS_CONNECT_ACTIVE', true );
