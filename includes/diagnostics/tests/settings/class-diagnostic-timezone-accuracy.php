<?php
/**
 * Timezone Accuracy
 *
 * Checks if site timezone is correctly configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Configuration
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Timezone_Accuracy Class
 *
 * Validates timezone configuration accuracy.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Timezone_Accuracy extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'timezone-accuracy';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Timezone Accuracy';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates timezone configuration matches actual location';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'configuration';

	/**
	 * Run the diagnostic check.
	 *
	 * Tests timezone configuration.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$timezone_string = get_option( 'timezone_string', '' );
		$gmt_offset = get_option( 'gmt_offset', 0 );

		// Check 1: Timezone is configured (not just using GMT offset)
		if ( empty( $timezone_string ) && intval( $gmt_offset ) === 0 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Timezone not configured - using UTC/GMT', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 25,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/wordpress-timezone-setup',
				'recommendations' => array(
					__( 'Set timezone to match your location or audience', 'wpshadow' ),
					__( 'Affects scheduled posts, cron jobs, and timestamps', 'wpshadow' ),
					__( 'Use named timezone (e.g., America/New_York) for DST support', 'wpshadow' ),
				),
			);
		}

		// Check 2: Using named timezone instead of GMT offset
		if ( empty( $timezone_string ) && intval( $gmt_offset ) !== 0 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Using manual GMT offset instead of named timezone', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 30,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/timezone-named-vs-offset',
				'recommendations' => array(
					__( 'Switch to named timezone (America/New_York, Europe/London, etc)', 'wpshadow' ),
					__( 'Named timezones automatically handle daylight saving time', 'wpshadow' ),
					__( 'Prevents DST transition issues', 'wpshadow' ),
				),
			);
		}

		// Check 3: Validate timezone is valid
		if ( ! empty( $timezone_string ) && ! self::is_valid_timezone( $timezone_string ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: timezone string */
					__( 'Configured timezone %s is invalid', 'wpshadow' ),
					$timezone_string
				),
				'severity'     => 'high',
				'threat_level' => 60,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/invalid-timezone',
				'recommendations' => array(
					__( 'Select valid timezone from WordPress settings', 'wpshadow' ),
					__( 'View list of valid timezones at WordPress.org', 'wpshadow' ),
				),
			);
		}

		// Check 4: Server timezone mismatch
		if ( ! self::server_timezone_matches() ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Server timezone differs from WordPress setting', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/server-timezone-mismatch',
				'recommendations' => array(
					__( 'Contact hosting provider to set server timezone', 'wpshadow' ),
					__( 'Or adjust WordPress timezone to match server', 'wpshadow' ),
					__( 'Consistent timezone prevents cron/scheduling issues', 'wpshadow' ),
				),
			);
		}

		return null;
	}

	/**
	 * Check if timezone is valid.
	 *
	 * @since 1.6093.1200
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
	 * @since 1.6093.1200
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
