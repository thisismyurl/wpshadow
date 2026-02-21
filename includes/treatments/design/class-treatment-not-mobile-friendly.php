<?php
/**
 * Not Mobile-Friendly Treatment
 *
 * Detects content and design patterns that are not optimized for mobile devices.
 * Checks viewport meta tag, responsive design elements, and mobile-specific issues.
 *
 * @package    WPShadow
 * @subpackage Treatments\Mobile
 * @since      1.6034.2145
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Not Mobile-Friendly Treatment Class
 *
 * Identifies mobile usability issues that affect user experience on smartphones
 * and tablets. Checks for proper viewport configuration, responsive design
 * implementation, and mobile-specific accessibility concerns.
 *
 * **Why This Matters:**
 * - 60%+ of web traffic is mobile (Google Mobile-First Indexing)
 * - Google rankings penalize non-mobile-friendly sites
 * - Poor mobile UX = 53% of users abandon site
 * - WCAG 2.1 mobile accessibility requirements
 *
 * **What's Checked:**
 * - Viewport meta tag presence and configuration
 * - Responsive CSS media queries
 * - Mobile-unfriendly plugins (Flash, deprecated features)
 * - Touch target sizes
 * - Horizontal scrolling issues
 *
 * @since 1.6034.2145
 */
class Treatment_Not_Mobile_Friendly extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'not-mobile-friendly';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Not Mobile-Friendly';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Detects site configuration and design patterns that are not optimized for mobile devices';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'mobile';

	/**
	 * Run the treatment check
	 *
	 * Checks multiple indicators of mobile-friendliness:
	 * - Viewport meta tag in <head>
	 * - Responsive CSS media queries
	 * - Mobile-unfriendly content (Flash, fixed-width elements)
	 * - Theme support for responsive design
	 *
	 * @since  1.6034.2145
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Not_Mobile_Friendly' );
	}

	/**
	 * Check if site has viewport meta tag
	 *
	 * @since  1.6034.2145
	 * @return bool True if viewport tag exists.
	 */
	private static function has_viewport_meta_tag() {
		// Check if theme adds viewport meta tag via wp_head
		ob_start();
		wp_head();
		$head_content = ob_get_clean();

		return ( stripos( $head_content, 'viewport' ) !== false );
	}
}
