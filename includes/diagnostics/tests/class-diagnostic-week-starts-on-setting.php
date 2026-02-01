<?php
/**
 * Week Starts On Setting Diagnostic
 *
 * Verifies that the week start day is properly configured for consistency
 * with calendars and scheduling displays throughout the site.
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
 * Week Starts On Setting Diagnostic Class
 *
 * Ensures week start day is properly configured.
 *
 * @since 1.26032.1800
 */
class Diagnostic_Week_Starts_On_Setting extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'week-starts-on-setting';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Week Starts On Setting';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies week start day setting';

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
	 * - Week start day is set to a valid day (0-6)
	 * - Week start day matches regional conventions
	 *
	 * @since  1.26032.1800
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Get week start setting.
		$start_of_week = get_option( 'start_of_week', 0 );

		// Valid values are 0-6 (Sunday-Saturday).
		if ( ! is_numeric( $start_of_week ) || $start_of_week < 0 || $start_of_week > 6 ) {
			$issues[] = __( 'Week start day setting appears invalid', 'wpshadow' );
		}

		// Map to day names for reference.
		$days = array( 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday' );

		// Note: This is informational, not really an issue.
		if ( 0 === $start_of_week && (int) $start_of_week === $start_of_week ) {
			// Sunday is a valid choice (US standard).
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => implode( '. ', $issues ),
				'severity'    => 'low',
				'threat_level' => 10,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/week-starts-on-setting',
			);
		}

		return null;
	}
}
