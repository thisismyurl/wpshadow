<?php
/**
 * Infinite Scroll Lazy Loading Not Configured Diagnostic
 *
 * Checks if infinite scroll is configured.
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
 * Infinite Scroll Lazy Loading Not Configured Diagnostic Class
 *
 * Detects missing infinite scroll configuration.
 *
 * @since 1.2601.2352
 */
class Diagnostic_Infinite_Scroll_Lazy_Loading_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'infinite-scroll-lazy-loading-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Infinite Scroll Lazy Loading Not Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if infinite scroll is configured';

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
		// Check for infinite scroll plugin
		if ( ! is_plugin_active( 'jetpack/jetpack.php' ) && ! has_filter( 'the_posts', 'wp_infinite_scroll' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Infinite scroll lazy loading is not configured. Implement infinite scroll to improve user engagement and reduce bounce rate.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 15,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/infinite-scroll-lazy-loading-not-configured',
			);
		}

		return null;
	}
}
