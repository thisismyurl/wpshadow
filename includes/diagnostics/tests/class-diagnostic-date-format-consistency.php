<?php
/**
 * Date Format Consistency Diagnostic
 *
 * Verifies that the date format setting is properly configured and consistent
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
 * Date Format Consistency Diagnostic Class
 *
 * Ensures date format is properly configured.
 *
 * @since 1.26032.1800
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
	protected static $description = 'Verifies date format is consistent';

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
	 * - Date format is set and not empty
	 * - Date format is valid PHP date format
	 * - Date format is reasonable (not confusing)
	 *
	 * @since  1.26032.1800
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Get date format.
		$date_format = get_option( 'date_format', 'F j, Y' );

		// Check if date format is set.
		if ( empty( $date_format ) ) {
			$issues[] = __( 'Date format is not configured; using default', 'wpshadow' );
		} else {
			// Try to format a date with this format string.
			try {
				$test_date = date_create( '2026-02-01' );
				if ( false === $test_date ) {
					$issues[] = __( 'Unable to test date format validity', 'wpshadow' );
				} else {
					// Check if it's a problematic format.
					if ( strlen( $date_format ) < 2 ) {
						$issues[] = __( 'Date format appears too short; it may not display properly', 'wpshadow' );
					} elseif ( strlen( $date_format ) > 50 ) {
						$issues[] = __( 'Date format appears too long; consider simplifying', 'wpshadow' );
					}

					// Try formatting.
					$formatted = date( $date_format, $test_date->getTimestamp() );
					if ( empty( $formatted ) || '0' === $formatted ) {
						$issues[] = __( 'Date format may be invalid; check Settings > General', 'wpshadow' );
					}
				}
			} catch ( \Exception $e ) {
				$issues[] = __( 'Error validating date format', 'wpshadow' );
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
				'kb_link'     => 'https://wpshadow.com/kb/date-format-consistency',
			);
		}

		return null;
	}
}
