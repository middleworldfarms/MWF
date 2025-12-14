<?php

if ( ! function_exists( 'et_core_cache_init' ) ):
function et_core_cache_init() {}
endif;


if ( ! function_exists( 'et_core_cache_dir' ) ):
function et_core_cache_dir() {
	return ET_Core_Cache_Directory::instance();
}
endif;
