<?php
/**
 * Google Analytics 4 Conversion Goal Not Set Diagnostic
 *
 * Checks if GA4 conversion goals are set.
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
 * Google Analytics 4 Conversion Goal Not Set Diagnostic Class
 *
 * Detects missing GA4 goals.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Google_Analytics_4_Conversion_Goal_Not_Set extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'google-analytics-4-conversion-goal-not-set';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Google Analytics 4 Conversion Goal Not Set';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if GA4 conversion goals are set';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'admin';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for GA4 conversion goals
		if ( ! get_option( 'ga4_conversion_goals_count' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'GA4 conversion goals are not set. Define key conversion goals (form submissions, purchases, signups) to track site performance.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 10,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/google-analytics-4-conversion-goal-not-set?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
