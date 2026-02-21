<?php
/**
 * Timezone Accuracy
 *
 * Checks if site timezone is correctly configured.
 *
 * @package    WPShadow
 * @subpackage Treatments\Configuration
 * @since      1.6030.2148
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Timezone_Accuracy Class
 *
 * Validates timezone configuration accuracy.
 *
 * @since 1.6030.2148
 */
class Treatment_Timezone_Accuracy extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'timezone-accuracy';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Timezone Accuracy';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Validates timezone configuration matches actual location';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'configuration';

	/**
	 * Run the treatment check.
	 *
	 * Tests timezone configuration.
	 *
	 * @since  1.6030.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Timezone_Accuracy' );
	}

	/**
	 * Check if timezone is valid.
	 *
	 * @since  1.6030.2148
	 * @param  string $timezone Timezone string.
	 * @return bool True if timezone is valid.
	 */
	private static function is_valid_timezone( $timezone ) {
		if ( empty( $timezone ) ) {
			return false;
		}

		try {
			new \DateTimeZone( $timezone );
			return true;
		} catch ( \Exception $e ) {
			return false;
		}
	}

	/**
	 * Check if server timezone matches.
	 *
	 * @since  1.6030.2148
	 * @return bool True if matches.
	 */
	private static function server_timezone_matches() {
		$wp_timezone = get_option( 'timezone_string', '' );

		if ( empty( $wp_timezone ) ) {
			return true; // Can't verify without WordPress timezone
		}

		try {
			$wp_tz = new \DateTimeZone( $wp_timezone );
			$server_offset = intval( date( 'Z' ) ); // Server timezone offset
			$wp_offset = $wp_tz->getOffset( new \DateTime() );

			// Allow small differences for DST transitions
			$difference = abs( $server_offset - $wp_offset );
			return $difference < 3600; // Less than 1 hour difference
		} catch ( \Exception $e ) {
			return false;
		}
	}
}
