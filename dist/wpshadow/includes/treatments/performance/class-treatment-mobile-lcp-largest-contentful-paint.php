<?php
/**
 * Mobile LCP (Largest Contentful Paint) Treatment
 *
 * Measures time to largest visible element on mobile devices.
 *
 * @since 0.6093.1200
 * @package WPShadow\Treatments
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile LCP (Largest Contentful Paint) Treatment Class
 *
 * Measures time to largest visible element on mobile devices, a key Core Web Vitals
 * metric for Google search rankings and user perceived performance.
 *
 * @since 0.6093.1200
 */
class Treatment_Mobile_LCP_Largest_Contentful_Paint extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-lcp-largest-contentful-paint';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Mobile LCP (Largest Contentful Paint)';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Measure time to largest visible element on mobile (Core Web Vitals metric)';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the treatment check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Mobile_LCP_Largest_Contentful_Paint' );
	}
}
