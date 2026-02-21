<?php
/**
 * Date Format Consistency
 *
 * Checks if site date format is consistent and optimal.
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
 * Treatment_Date_Format_Consistency Class
 *
 * Validates date format consistency across site.
 *
 * @since 1.6030.2148
 */
class Treatment_Date_Format_Consistency extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'date-format-consistency';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Date Format Consistency';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Validates date format consistency and readability';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'configuration';

	/**
	 * Run the treatment check.
	 *
	 * Tests date format configuration.
	 *
	 * @since  1.6030.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Date_Format_Consistency' );
	}

	/**
	 * Check if format is readable.
	 *
	 * @since  1.6030.2148
	 * @param  string $format Date/time format string.
	 * @param  bool   $is_time Whether checking time format.
	 * @return bool True if readable.
	 */
	private static function is_readable_format( $format, $is_time = false ) {
		// Empty format is not readable
		if ( empty( $format ) ) {
			return false;
		}

		// Check for human-readable components
		if ( $is_time ) {
			// Time should have hour and minute indicators
			if ( strpos( $format, 'g' ) === false && strpos( $format, 'h' ) === false && strpos( $format, 'H' ) === false ) {
				return false;
			}
			if ( strpos( $format, 'i' ) === false ) {
				return false;
			}
		} else {
			// Date should have year or month/day
			if ( strpos( $format, 'Y' ) === false && strpos( $format, 'y' ) === false ) {
				if ( strpos( $format, 'F' ) === false && strpos( $format, 'n' ) === false ) {
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Check for format consistency.
	 *
	 * @since  1.6030.2148
	 * @return bool True if consistent.
	 */
	private static function has_format_consistency() {
		$date_format = get_option( 'date_format', 'Y-m-d' );
		$time_format = get_option( 'time_format', 'g:i a' );
		$locale = get_locale();

		// Check if both match locale preferences
		if ( strpos( $locale, 'en' ) !== false ) {
			// English typically uses readable formats
			if ( self::is_readable_format( $date_format ) && self::is_readable_format( $time_format, true ) ) {
				return true;
			}
		} else {
			// Other locales should still have readable formats
			if ( self::is_readable_format( $date_format ) && self::is_readable_format( $time_format, true ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if format includes year.
	 *
	 * @since  1.6030.2148
	 * @param  string $format Date format string.
	 * @return bool True if year included.
	 */
	private static function includes_year( $format ) {
		return strpos( $format, 'Y' ) !== false || strpos( $format, 'y' ) !== false;
	}
}
