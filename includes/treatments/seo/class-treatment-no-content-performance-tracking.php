<?php
/**
 * No Content Performance Tracking Treatment
 *
 * Detects lack of content analytics tracking, preventing data-driven
 * optimization decisions.
 *
 * @package    WPShadow
 * @subpackage Treatments\Analytics
 * @since      1.6034.2207
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * No Content Performance Tracking Treatment Class
 *
 * Checks if content performance metrics are being tracked to enable
 * data-driven content strategy decisions.
 *
 * **Why This Matters:**
 * - Can't improve what you don't measure
 * - Wasting effort on underperforming content
 * - Missing opportunities to scale winners
 * - No data for content ROI calculations
 * - Flying blind on content strategy
 *
 * **Key Metrics to Track:**
 * - Pageviews per post
 * - Time on page
 * - Bounce rate
 * - Social shares
 * - Conversions/goals
 * - Comments and engagement
 *
 * @since 1.6034.2207
 */
class Treatment_No_Content_Performance_Tracking extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-content-performance-tracking';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'No Content Performance Tracking';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Content performance isn\'t being tracked, preventing data-driven optimization';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'analytics';

	/**
	 * Run the treatment check
	 *
	 * @since  1.6034.2207
	 * @return array|null Finding array if tracking not configured, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_No_Content_Performance_Tracking' );
	}
}
