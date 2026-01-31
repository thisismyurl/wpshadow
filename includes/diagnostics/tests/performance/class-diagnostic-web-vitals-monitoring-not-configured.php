<?php
/**
 * Web Vitals Monitoring Not Configured Diagnostic
 *
 * Checks if Web Vitals monitoring is configured.
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
 * Web Vitals Monitoring Not Configured Diagnostic Class
 *
 * Detects missing Web Vitals monitoring.
 *
 * @since 1.2601.2352
 */
class Diagnostic_Web_Vitals_Monitoring_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'web-vitals-monitoring-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Web Vitals Monitoring Not Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if Web Vitals monitoring is configured';

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
		// Check for Web Vitals tracking script
		if ( ! has_filter( 'wp_head', 'add_web_vitals_script' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Web Vitals monitoring is not configured. Implement Core Web Vitals tracking (LCP, FID, CLS) to monitor real user experience metrics that affect Google rankings.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 65,
				'auto_fixable'  => true,
				'kb_link'       => 'https://wpshadow.com/kb/web-vitals-monitoring-not-configured',
			);
		}

		return null;
	}
}
