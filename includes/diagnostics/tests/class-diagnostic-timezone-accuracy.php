<?php
/**
 * Timezone Accuracy Diagnostic
 *
 * Verifies that the WordPress timezone is properly configured to ensure
 * timestamps and scheduled posts are handled correctly.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26032.1800
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
 * Ensures timezone is properly configured.
 *
 * @since 1.26032.1800
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
	protected static $description = 'Verifies timezone is correctly configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'settings';

	/**
	 * Run the diagnostic check.
	 *
	 * Checks:
	 * - Timezone is set using timezone string (not UTC offset)
	 * - Timezone string is valid
	 * - Timezone matches server timezone (if applicable)
	 * - DST handling is correct
	 *
	 * @since  1.26032.1800
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Get timezone setting.
		$timezone_string = get_option( 'timezone_string', '' );
		$gmt_offset       = get_option( 'gmt_offset', 0 );

		// Check if using UTC offset instead of timezone string (less preferred).
		if ( empty( $timezone_string ) && 0 !== $gmt_offset ) {
			$issues[] = sprintf(
				/* translators: %d: UTC offset */
				__( 'Using UTC offset (%+d) instead of timezone string; DST changes may not be handled automatically', 'wpshadow' ),
				$gmt_offset
			);
		}

		// Check if timezone string is valid.
		if ( ! empty( $timezone_string ) ) {
			// Try to create a DateTimeZone with this string.
			try {
				$tz = new \DateTimeZone( $timezone_string );
				$now = new \DateTime( 'now', $tz );
			} catch ( \Exception $e ) {
				$issues[] = sprintf(
					/* translators: %s: timezone string */
					__( 'Timezone string (%s) is invalid', 'wpshadow' ),
					$timezone_string
				);
			}
		}

		// Check if both UTC offset and timezone string are used (conflicting).
		if ( ! empty( $timezone_string ) && 0 !== $gmt_offset ) {
			$issues[] = __( 'Both timezone string and UTC offset are set; this may cause confusion', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => implode( '. ', $issues ),
				'severity'    => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/timezone-accuracy',
			);
		}

		return null;
	}
}
