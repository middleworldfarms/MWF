<?php
/**
 * Credits: https://github.com/robinvdvleuten/php-ulid
 */

final class GPLVault_Ulid {
	const ENCODING_CHARS  = '0123456789ABCDEFGHJKMNPQRSTVWXYZ';
	const ENCODING_LENGTH = 32;
	const TIME_MAX        = 281474976710655;
	const TIME_LENGTH     = 10;
	const RANDOM_LENGTH   = 16;

	/**
	 * @var int
	 */
	private static $lastGenTime = 0;

	/**
	 * @var array
	 */
	private static $lastRandChars = array();

	/**
	 * @var string
	 */
	private $time;

	/**
	 * @var string
	 */
	private $randomness;

	/**
	 * @var bool
	 */
	private $lowercase;

	private function __construct( string $time, string $randomness, bool $lowercase = true ) {
		$this->time       = $time;
		$this->randomness = $randomness;
		$this->lowercase  = $lowercase;
	}

	public static function fromString( string $value, bool $lowercase = false ): self {
		if ( strlen( $value ) !== self::TIME_LENGTH + self::RANDOM_LENGTH ) {
			throw new GPLVault_Invalid_Ulid_Exception( 'Invalid ULID string (wrong length): ' . esc_html( $value ) );
		}

		// Convert to uppercase for regex. Doesn't matter for output later, that is determined by $lowercase.
		$value = strtoupper( $value );

		if ( ! preg_match( sprintf( '!^[%s]{%d}$!', self::ENCODING_CHARS, self::TIME_LENGTH + self::RANDOM_LENGTH ), $value ) ) {
			throw new GPLVault_Invalid_Ulid_Exception( 'Invalid ULID string (wrong characters): ' . esc_html( $value ) );
		}

		return new self( substr( $value, 0, self::TIME_LENGTH ), substr( $value, self::TIME_LENGTH, self::RANDOM_LENGTH ), $lowercase );
	}

	/**
	 * Create a ULID using the given timestamp.
	 * @param int $milliseconds Number of milliseconds since the UNIX epoch for which to generate this ULID.
	 * @param bool $lowercase True to output lowercase ULIDs.
	 * @return GPLVault_Ulid Returns a GPLVault_Ulid object for the given microsecond time.
	 */
	public static function fromTimestamp( int $milliseconds, bool $lowercase = false ): self {
		$duplicate_time = $milliseconds === self::$lastGenTime;

		self::$lastGenTime = $milliseconds;

		$time_chars = '';
		$rand_chars = '';

		$encoding_chars = self::ENCODING_CHARS;

		for ( $i = self::TIME_LENGTH - 1; $i >= 0; $i-- ) {
			$mod          = $milliseconds % self::ENCODING_LENGTH;
			$time_chars   = $encoding_chars[ $mod ] . $time_chars;
			$milliseconds = ( $milliseconds - $mod ) / self::ENCODING_LENGTH;
		}

		if ( ! $duplicate_time ) {
			for ( $i = 0; $i < self::RANDOM_LENGTH; $i++ ) {
				self::$lastRandChars[ $i ] = random_int( 0, 31 );
			}
		} else {
			// If the timestamp hasn't changed since last push,
			// use the same random number, except incremented by 1.
			for ( $i = self::RANDOM_LENGTH - 1; $i >= 0 && self::$lastRandChars[ $i ] === 31; $i-- ) { // phpcs:ignore
				self::$lastRandChars[ $i ] = 0;
			}

			self::$lastRandChars[ $i ]++;
		}

		for ( $i = 0; $i < self::RANDOM_LENGTH; $i++ ) {
			$rand_chars .= $encoding_chars[ self::$lastRandChars[ $i ] ];
		}

		return new self( $time_chars, $rand_chars, $lowercase );
	}

	public static function generate( bool $lowercase = false ): self {
		$now = (int) ( microtime( true ) * 1000 );

		return self::fromTimestamp( $now, $lowercase );
	}

	public function getTime(): string {
		return $this->time;
	}

	public function getRandomness(): string {
		return $this->randomness;
	}

	public function isLowercase(): bool {
		return $this->lowercase;
	}

	public function toTimestamp(): int {
		return $this->decodeTime( $this->time );
	}

	public function __toString(): string {
		return ( $value = $this->time . $this->randomness ) && $this->lowercase ? strtolower( $value ) : strtoupper( $value ); // phpcs:ignore Squiz.PHP.DisallowMultipleAssignments.Found
	}

	private function decodeTime( string $time ): int {
		$time_chars = str_split( strrev( $time ) );
		$carry      = 0;

		foreach ( $time_chars as $index => $char ) {
			if ( ( $encoding_index = strripos( self::ENCODING_CHARS, $char ) ) === false ) { // phpcs:ignore
				throw new GPLVault_Invalid_Ulid_Exception( 'Invalid ULID character: ' . esc_html( $char ) );
			}

			$carry += ( $encoding_index * pow( self::ENCODING_LENGTH, $index ) );
		}

		if ( $carry > self::TIME_MAX ) {
			throw new GPLVault_Invalid_Ulid_Exception( 'Invalid ULID string: timestamp too large' );
		}

		return $carry;
	}
}
