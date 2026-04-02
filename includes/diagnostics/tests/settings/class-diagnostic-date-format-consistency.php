<?php
/**
 * Date Format Consistency
 *
 * Checks if site date format is consistent and optimal.
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
 * Diagnostic_Date_Format_Consistency Class
 *
 * Validates date format consistency across site.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Date_Format_Consistency extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'date-format-consistency';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Date Format Consistency';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates date format consistency and readability';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'configuration';

	/**
	 * Run the diagnostic check.
	 *
	 * Tests date format configuration.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$date_format = get_option( 'date_format', 'Y-m-d' );
		$time_format = get_option( 'time_format', 'g:i a' );

		// Check 1: Date format is readable
		if ( ! self::is_readable_format( $date_format ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Date format may be difficult to read', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 20,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/date-format-readability',
				'recommendations' => array(
					__( 'Use readable format like "F j, Y" (January 15, 2025)', 'wpshadow' ),
					__( 'Avoid overly technical formats', 'wpshadow' ),
					__( 'Consider locale preferences', 'wpshadow' ),
				),
			);
		}

		// Check 2: Time format is readable
		if ( ! self::is_readable_format( $time_format, true ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Time format may be difficult to read', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 15,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/time-format-readability',
				'recommendations' => array(
					__( 'Use readable format like "g:i a" (12:30 pm)', 'wpshadow' ),
					__( 'Include AM/PM for 12-hour format', 'wpshadow' ),
					__( 'Or use 24-hour format consistently', 'wpshadow' ),
				),
			);
		}

		// Check 3: Format consistency
		if ( ! self::has_format_consistency() ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Date and time formats may not be consistent', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 20,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/date-time-format-consistency',
				'recommendations' => array(
					__( 'Ensure both date and time formats match site language', 'wpshadow' ),
					__( 'Use locale-appropriate formats', 'wpshadow' ),
					__( 'Test with different languages if multilingual', 'wpshadow' ),
				),
			);
		}

		// Check 4: Check if format includes year
		if ( ! self::includes_year( $date_format ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Date format does not include year - may cause confusion', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 15,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/date-format-year',
				'recommendations' => array(
					__( 'Include year (Y or y) in date format', 'wpshadow' ),
					__( 'Especially important for archives and historical content', 'wpshadow' ),
				),
			);
		}

		return null;
	}

	/**
	 * Check if format is readable.
	 *
	 * @since 1.6093.1200
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
	 * @since 1.6093.1200
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
	 * @since 1.6093.1200
	 * @param  string $format Date format string.
	 * @return bool True if year included.
	 */
	private static function includes_year( $format ) {
		return strpos( $format, 'Y' ) !== false || strpos( $format, 'y' ) !== false;
	}
}
