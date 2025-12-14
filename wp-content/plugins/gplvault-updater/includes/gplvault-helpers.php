<?php

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'gv_date_format' ) ) {
	function gv_date_format() {
		$date_format = get_option( 'date_format' );
		if ( empty( $date_format ) ) {
			// Return default date format if the option is empty.
			$date_format = 'F j, Y';
		}
		return $date_format;
	}
}

if ( ! function_exists( 'gv_time_format' ) ) {
	function gv_time_format() {
		$time_format = get_option( 'time_format' );
		if ( empty( $time_format ) ) {
			// Return default time format if the option is empty.
			$time_format = 'g:i a';
		}
		return $time_format;
	}
}

if ( ! function_exists( 'gv_clean' ) ) {
	/**
	 * This function sanitizes input text field
	 *
	 * This function is copy of WooCommerce `wc_clean` function.
	 *
	 * @param mixed $var
	 *
	 * @return mixed
	 */
	function gv_clean( $var ) {
		if ( is_array( $var ) ) {
			return array_map( 'gv_clean', $var );
		}

		return is_scalar( $var ) ? sanitize_text_field( $var ) : $var;
	}
}

if ( ! function_exists( 'gv_generate_password' ) ) :
	function gv_generate_password() {
		return (string) GPLVault_Ulid::generate();
	}
endif;

if ( ! function_exists( 'gv_print_r' ) ) :
	function gv_print_r( $expression, $return = false ) {
		$alternatives = array(
			array(
				'func' => 'print_r',
				'args' => array( $expression, true ),
			),
			array(
				'func' => 'var_export',
				'args' => array( $expression, true ),
			),
			array(
				'func' => 'json_encode',
				'args' => array( $expression ),
			),
			array(
				'func' => 'serialize',
				'args' => array( $expression ),
			),
		);

		$alternatives = apply_filters( 'gplvault_print_r_alternatives', $alternatives, $expression );

		foreach ( $alternatives as $alternative ) {
			if ( function_exists( $alternative['func'] ) ) {
				$res = call_user_func_array( $alternative['func'], $alternative['args'] );
				if ( $return ) {
					return $res;
				}

				echo $res; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				return true;
			}
		}

		return false;
	}
endif;


if ( ! function_exists( 'gv_take' ) ) {
	function gv_take( $payload, $keys ) {
		$accumulator = array();

		$payload_data = is_array( $payload ) ? $payload : ( is_object( $payload ) ? (array) $payload : array() );

		foreach ( $keys as $key ) {
			$accumulator[ $key ] = array_key_exists( $key, $payload_data ) ? $payload_data[ $key ] : null;
		}

		return $accumulator;
	}
}

if ( ! function_exists( 'gv_unslashit' ) ) {
	function gv_unslashit( $text ) {
		return trim( $text, '/\\' );
	}
}

if ( ! function_exists( 'gv_slashit' ) ) {
	function gv_slashit( $text ) {
		return '/' . gv_unslashit( $text ) . '/';
	}
}

if ( ! function_exists( 'gv_random_password' ) ) {
	function gv_random_password( $length = 12, $special_chars = true, $extra_special_chars = false ): string {
		$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
		if ( $special_chars ) {
			$chars .= '!@#$%^&*()';
		}
		if ( $extra_special_chars ) {
			$chars .= '-_ []{}<>~`+=,.;:/?|';
		}

		$password = '';
		for ( $i = 0; $i < $length; $i++ ) {
			$password .= substr( $chars, gv_rand( 0, strlen( $chars ) - 1 ), 1 );
		}

		return $password;
	}
}

if ( ! function_exists( 'gv_rand' ) ) {
	function gv_rand( $min = null, $max = null ) {
		global $rnd_value;

		if ( null === $min ) {
			$min = 0;
		}

		if ( null === $max ) {
			$max_random_number = 3000000000 == 2147483647 ? (float) '4294967295' : 4294967295; // @phpcs:ignore
			$max               = $max_random_number;
		}

		static $use_random_int_functionality = true;

		if ( $use_random_int_functionality ) {
			try {
				// wp_rand() can accept arguments in either order, PHP cannot.
				$_max = max( $min, $max );
				$_min = min( $min, $max );
				$val  = random_int( $_min, $_max );
				if ( false !== $val ) {
					return absint( $val );
				} else {
					$use_random_int_functionality = false;
				}
			} catch ( Error $e ) {
				$use_random_int_functionality = false;
			} catch ( Exception $e ) {
				$use_random_int_functionality = false;
			}
		}

		if ( strlen( $rnd_value ) < 8 ) {
			if ( defined( 'WP_SETUP_CONFIG' ) ) {
				static $seed = '';
			} else {
				$seed = get_transient( 'random_seed' );
			}
			// @phpcs:disable
			$rnd_value  = md5( uniqid( microtime() . mt_rand(), true ) . $seed );
			$rnd_value .= sha1( $rnd_value );
			$rnd_value .= sha1( $rnd_value . $seed );
			// @phpcs:enable
			$seed = md5( $seed . $rnd_value );
			if ( ! defined( 'WP_SETUP_CONFIG' ) && ! defined( 'WP_INSTALLING' ) ) {
				set_transient( 'random_seed', $seed );
			}
		}

		// Take the first 8 digits for our value.
		$value = substr( $rnd_value, 0, 8 );

		// Strip the first eight, leaving the remainder for the next call to wp_rand().
		$rnd_value = substr( $rnd_value, 8 ); // @phpcs:ignore

		$value = abs( hexdec( $value ) );

		// Reduce the value to be within the min - max range.
		$value = $min + ( $max - $min + 1 ) * $value / ( $max_random_number + 1 );

		return abs( (int) $value );
	}
}

if ( ! function_exists( 'gv_salt' ) ) {
	function gv_salt( $scheme = 'auth' ) {
		static $cached_salts = array();
		if ( isset( $cached_salts[ $scheme ] ) ) {
			return $cached_salts[ $scheme ];
		}

		static $duplicated_keys;
		if ( null === $duplicated_keys ) {
			$duplicated_keys = array(
				'put your unique phrase here' => true,
			);
			// Removed translation call that was causing early loading issues
			// $duplicated_keys[ __( 'put your unique phrase here', 'gplvault' ) ] = true;

			foreach ( array( 'AUTH', 'SECURE_AUTH', 'LOGGED_IN', 'NONCE', 'SECRET' ) as $first ) {
				foreach ( array( 'KEY', 'SALT' ) as $second ) {
					if ( ! defined( "{$first}_{$second}" ) ) {
						continue;
					}
					$value                     = constant( "{$first}_{$second}" );
					$duplicated_keys[ $value ] = isset( $duplicated_keys[ $value ] );
				}
			}
		}

		$values = array(
			'key'  => '',
			'salt' => '',
		);
		if ( defined( 'SECRET_KEY' ) && SECRET_KEY && empty( $duplicated_keys[ SECRET_KEY ] ) ) {
			$values['key'] = SECRET_KEY;
		}
		if ( 'auth' === $scheme && defined( 'SECRET_SALT' ) && SECRET_SALT && empty( $duplicated_keys[ SECRET_SALT ] ) ) {
			$values['salt'] = SECRET_SALT;
		}

		if ( in_array( $scheme, array( 'auth', 'secure_auth', 'logged_in', 'nonce' ), true ) ) {
			foreach ( array( 'key', 'salt' ) as $type ) {
				$const = strtoupper( "{$scheme}_{$type}" );
				if ( defined( $const ) && constant( $const ) && empty( $duplicated_keys[ constant( $const ) ] ) ) {
					$values[ $type ] = constant( $const );
				} elseif ( ! $values[ $type ] ) {
					$values[ $type ] = get_site_option( "{$scheme}_{$type}" );
					if ( ! $values[ $type ] ) {
						$values[ $type ] = gv_random_password( 64, true, true );
						update_site_option( "{$scheme}_{$type}", $values[ $type ] );
					}
				}
			}
		} else {
			if ( ! $values['key'] ) {
				$values['key'] = get_site_option( 'secret_key' );
				if ( ! $values['key'] ) {
					$values['key'] = gv_random_password( 64, true, true );
					update_site_option( 'secret_key', $values['key'] );
				}
			}
			$values['salt'] = hash_hmac( 'md5', $scheme, $values['key'] );
		}

		$cached_salts[ $scheme ] = $values['key'] . $values['salt'];

		return $cached_salts[ $scheme ];
	}
}

if ( ! function_exists( 'gv_hash' ) ) {
	function gv_hash( $data, $scheme = 'auth' ) {
		$salt = gv_salt( $scheme );

		return hash_hmac( 'md5', $data, $salt );
	}
}

if ( ! function_exists( 'gv_mask_str' ) ) {
	function gv_mask_str( $text, $mask_char = '*', $visible_len = 4 ) {
		$str_len = strlen( $text );
		if ( $str_len <= $visible_len ) {
			return $text;
		}
		$hidden_count = $str_len - $visible_len;
		return str_repeat( $mask_char, $hidden_count ) . substr( $text, ( $visible_len * -1 ), $visible_len );
	}
}
