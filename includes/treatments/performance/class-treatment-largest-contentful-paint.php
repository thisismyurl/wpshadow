<?php
/**
 * Largest Contentful Paint (LCP) Treatment
 *
 * Measures Largest Contentful Paint time for Core Web Vitals.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Largest Contentful Paint Treatment Class
 *
 * Measures factors affecting LCP (Largest Contentful Paint).
 * LCP is the most important Core Web Vital for perceived load speed.
 *
 * @since 1.6093.1200
 */
class Treatment_Largest_Contentful_Paint extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'largest-contentful-paint';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Largest Contentful Paint (LCP)';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Measures Largest Contentful Paint timing (Core Web Vital)';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the treatment check.
	 *
	 * Checks factors affecting LCP:
	 * - Server response time (TTFB)
	 * - Resource load times
	 * - Client-side rendering
	 * - Image optimization
	 *
	 * Thresholds:
	 * - Good: <2.5s
	 * - Needs Improvement: 2.5-4.0s
	 * - Poor: >4.0s
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Largest_Contentful_Paint' );
	}
}
