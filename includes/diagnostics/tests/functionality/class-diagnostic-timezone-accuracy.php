<?php
/**
 * Timezone Accuracy Diagnostic
 *
 * Validates timezone setting matches server location. Tests scheduled post timing accuracy.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2602.0100
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Timezone Accuracy Diagnostic Class
 *
 * Checks if WordPress timezone is properly configured and matches server timezone.
 * Validates scheduled posts will run at expected times.
 *
 * @since 1.2602.0100
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
	protected static $description = 'Validates timezone setting matches server location';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2602.0100
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$timezone_string = get_option( 'timezone_string', '' );
		$gmt_offset      = get_option( 'gmt_offset', 0 );
		$server_timezone = date_default_timezone_get();
		$issues          = array();
		$details         = array(
			'wp_timezone_string' => $timezone_string,
			'wp_gmt_offset'      => $gmt_offset,
			'server_timezone'    => $server_timezone,
		);

		// Check if timezone string is empty (using manual offset instead).
		if ( empty( $timezone_string ) && 0 !== $gmt_offset ) {
			$issues[] = __( 'Using manual UTC offset instead of timezone string. Timezone strings account for daylight saving time changes automatically.', 'wpshadow' );
		}

		// Check if timezone is set to UTC but server isn't.
		if ( 'UTC' === $timezone_string && 'UTC' !== $server_timezone ) {
			$issues[] = sprintf(
				/* translators: %s: Server timezone name */
				__( 'WordPress timezone is UTC but server timezone is %s. This mismatch could cause scheduled post timing issues.', 'wpshadow' ),
				$server_timezone
			);
		}

		// Check if timezone is completely unset.
		if ( empty( $timezone_string ) && 0 === (float) $gmt_offset ) {
			$issues[] = __( 'No timezone configured. Scheduled posts and events may run at unexpected times.', 'wpshadow' );
		}

		// Check if timezone string differs significantly from server timezone.
		if ( ! empty( $timezone_string ) && 'UTC' !== $timezone_string && $timezone_string !== $server_timezone ) {
			// Get both timezone objects to compare offsets.
			try {
				$wp_tz     = new \DateTimeZone( $timezone_string );
				$server_tz = new \DateTimeZone( $server_timezone );
				$now       = new \DateTime( 'now' );

				$wp_offset     = $wp_tz->getOffset( $now );
				$server_offset = $server_tz->getOffset( $now );

				// If offsets differ, it could cause issues.
				if ( abs( $wp_offset - $server_offset ) > 0 ) {
					$hours_diff = abs( $wp_offset - $server_offset ) / 3600;
					$issues[]   = sprintf(
						/* translators: 1: WordPress timezone, 2: Server timezone, 3: Hours difference */
						__( 'WordPress timezone (%1$s) differs from server timezone (%2$s) by %3$.1f hours. This could affect cron jobs and scheduled tasks.', 'wpshadow' ),
						$timezone_string,
						$server_timezone,
						$hours_diff
					);
				}
			} catch ( \Exception $e ) {
				// Invalid timezone, report it.
				$issues[] = sprintf(
					/* translators: %s: Timezone string */
					__( 'Invalid timezone string configured: %s', 'wpshadow' ),
					$timezone_string
				);
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'                 => self::$slug,
				'title'              => self::$title,
				'description'        => __( 'Timezone configuration issues detected that could affect scheduled posts and events.', 'wpshadow' ),
				'severity'           => 'medium',
				'threat_level'       => 50,
				'site_health_status' => 'recommended',
				'auto_fixable'       => false,
				'kb_link'            => 'https://wpshadow.com/kb/functionality-timezone-accuracy',
				'family'             => self::$family,
				'details'            => array(
					'issues' => $issues,
					'info'   => $details,
				),
			);
		}

		return null;
	}
}
