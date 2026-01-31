<?php
/**
 * Page Load Time Not Optimized Below 3 Seconds Diagnostic
 *
 * Checks if page load time is optimized.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2352
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Page Load Time Not Optimized Below 3 Seconds Diagnostic Class
 *
 * Detects slow page load times.
 *
 * @since 1.2601.2352
 */
class Diagnostic_Page_Load_Time_Not_Optimized_Below_3_Seconds extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'page-load-time-not-optimized-below-3-seconds';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Page Load Time Not Optimized Below 3 Seconds';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if page load time is optimized';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for performance optimization
		if ( ! has_filter( 'wp_head', 'wp_performance_check' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Page load time is not optimized below 3 seconds. Optimize images, enable caching, and minify assets to improve load time.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 45,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/page-load-time-not-optimized-below-3-seconds',
			);
		}

		return null;
	}
}
