<?php
/**
 * Timezone and Date Format Inconsistencies Diagnostic
 *
 * Tests for timezone and date format consistency.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Timezone and Date Format Inconsistencies Diagnostic Class
 *
 * Tests for timezone and date format consistency.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Timezone_And_Date_Format_Inconsistencies extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'timezone-and-date-format-inconsistencies';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Timezone and Date Format Inconsistencies';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests for timezone and date format consistency';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check for timezone mismatch.
		$timezone_string = get_option( 'timezone_string' );
		$gmt_offset = get_option( 'gmt_offset' );

		if ( ! empty( $timezone_string ) && ! empty( $gmt_offset ) ) {
			// Both are set - could be inconsistent.
			try {
				$tz = new DateTimeZone( $timezone_string );
				$dt = new DateTime( 'now', $tz );
				$expected_offset = $dt->getOffset() / 3600; // Convert to hours.

				if ( $expected_offset != $gmt_offset ) {
					$issues[] = sprintf(
						/* translators: %s: timezone string, %d: GMT offset, %d: expected offset */
						__( 'Timezone (%s) and GMT offset mismatch (set to %+d, expected %+d)', 'wpshadow' ),
						$timezone_string,
						$gmt_offset,
						$expected_offset
					);
				}
			} catch ( Exception $e ) {
				// Timezone is invalid.
				$issues[] = __( 'Invalid timezone configuration', 'wpshadow' );
			}
		}

		// Check database timestamp consistency.
		global $wpdb;
		$db_now = $wpdb->get_var( "SELECT NOW()" );
		$wp_now = date( 'Y-m-d H:i:s' );

		if ( ! empty( $db_now ) ) {
			$db_time = strtotime( $db_now );
			$wp_time = strtotime( $wp_now );

			if ( abs( $db_time - $wp_time ) > 60 ) { // More than 1 minute difference.
				$issues[] = sprintf(
					/* translators: %d: seconds difference */
					__( 'Database time is %d seconds different from PHP time - may cause scheduling issues', 'wpshadow' ),
					abs( $db_time - $wp_time )
				);
			}
		}

		// Check date format for consistency.
		$date_format = get_option( 'date_format' );
		$time_format = get_option( 'time_format' );

		if ( empty( $date_format ) ) {
			$issues[] = __( 'No date format set', 'wpshadow' );
		}

		if ( empty( $time_format ) ) {
			$issues[] = __( 'No time format set', 'wpshadow' );
		}

		// Check for locale-specific format issues.
		$locale = get_locale();
		if ( $locale !== 'en_US' ) {
			// For non-English locales, verify format is appropriate.
			$issues[] = sprintf(
				/* translators: %s: locale */
				__( 'Using locale %s - verify date format is appropriate for your region', 'wpshadow' ),
				$locale
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 40,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/timezone-and-date-format-inconsistencies?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
