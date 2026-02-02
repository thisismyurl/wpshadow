<?php
/**
 * Time Format Consistency Diagnostic
 *
 * Verifies that the time format setting is properly configured and consistent
 * throughout the WordPress site.
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
 * Time Format Consistency Diagnostic Class
 *
 * Ensures time format is properly configured.
 *
 * @since 1.26032.1800
 */
class Diagnostic_Time_Format_Consistency extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'time-format-consistency';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Time Format Consistency';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies time format is consistent';

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
	 * - Time format is set and not empty
	 * - Time format is valid PHP time format
	 * - Time format is reasonable (12 or 24 hour)
	 *
	 * @since  1.26032.1800
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Get time format.
		$time_format = get_option( 'time_format', 'g:i a' );

		// Check if time format is set.
		if ( empty( $time_format ) ) {
			$issues[] = __( 'Time format is not configured; using default', 'wpshadow' );
		} else {
			// Check length.
			if ( strlen( $time_format ) < 2 ) {
				$issues[] = __( 'Time format appears too short; it may not display properly', 'wpshadow' );
			} elseif ( strlen( $time_format ) > 30 ) {
				$issues[] = __( 'Time format appears too long; consider simplifying', 'wpshadow' );
			}

			// Try formatting.
			$formatted = date( $time_format, current_time( 'timestamp' ) );
			if ( empty( $formatted ) || '0' === $formatted ) {
				$issues[] = __( 'Time format may be invalid; check Settings > General', 'wpshadow' );
			}

			// Check if using reasonable 12/24 hour format.
			$uses_12h = ( false !== strpos( $time_format, 'a' ) || false !== strpos( $time_format, 'A' ) );
			$uses_24h = ( false !== strpos( $time_format, 'H' ) || false !== strpos( $time_format, 'k' ) );

			if ( ! $uses_12h && ! $uses_24h ) {
				$issues[] = __( 'Time format does not specify 12-hour or 24-hour format; may be ambiguous', 'wpshadow' );
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => implode( '. ', $issues ),
				'severity'    => 'low',
				'threat_level' => 15,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/time-format-consistency',
			);
		}

		return null;
	}
}
