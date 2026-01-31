<?php
/**
 * Lazy Loading For Iframes Not Configured Diagnostic
 *
 * Checks if iframe lazy loading is configured.
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
 * Lazy Loading For Iframes Not Configured Diagnostic Class
 *
 * Detects missing iframe lazy loading.
 *
 * @since 1.2601.2352
 */
class Diagnostic_Lazy_Loading_For_Iframes_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'lazy-loading-for-iframes-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Lazy Loading For Iframes Not Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if iframe lazy loading is configured';

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
		// Check for iframe lazy loading filter
		if ( ! has_filter( 'wp_iframe_tag_add_loading_attr' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Iframe lazy loading is not configured. Add loading="lazy" attribute to iframes to improve performance.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 20,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/lazy-loading-for-iframes-not-configured',
			);
		}

		return null;
	}
}
