<?php
/**
 * First Contentful Paint (FCP) Treatment
 *
 * Measures First Contentful Paint - when the first text or image is painted.
 * Core Web Vital that measures perceived loading performance.
 *
 * @package    WPShadow
 * @subpackage Treatments\Performance
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * First Contentful Paint Treatment Class
 *
 * Analyzes factors affecting First Contentful Paint including server response time,
 * render-blocking resources, and critical rendering path optimization.
 *
 * **Why This Matters:**
 * - Google Lighthouse Core Web Vital
 * - First impression of site speed
 * - 1-second delay = 7% reduction in conversions
 * - Affects Google rankings
 *
 * **What's Measured:**
 * - Server response time (TTFB)
 * - Render-blocking CSS/JS
 * - Font loading strategy
 * - Critical CSS implementation
 *
 * **Target:** <1.0 seconds, ideal <1.0 second
 *
 * @since 0.6093.1200
 */
class Treatment_First_Contentful_Paint_FCP extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'first-contentful-paint-fcp';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'First Contentful Paint (FCP)';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Analyzes factors affecting First Contentful Paint performance metric';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the treatment check
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if FCP likely poor, null if acceptable.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_First_Contentful_Paint_FCP' );
	}
}
