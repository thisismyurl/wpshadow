<?php
/**
 * Browser Caching Not Optimized Diagnostic
 *
 * Checks browser caching.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6033.2033
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Browser_Caching_Not_Optimized Class
 *
 * Performs diagnostic check for Browser Caching Not Optimized.
 *
 * @since 1.6033.2033
 */
class Diagnostic_Browser_Caching_Not_Optimized extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'browser-caching-not-optimized';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Browser Caching Not Optimized';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks browser caching';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6033.2033
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if (   !has_filter('init',
						'optimize_browser_cache' ) {
						return array(
						'id'   =>   self::$slug,
						'title'   =>   self::$title,
						'description'   =>   __('Browser caching not optimized. Set Cache-Control headers with appropriate max-age for static assets.',
						'severity'   =>   'medium',
						'threat_level'   =>   40,
						'auto_fixable'   =>   true,
						'kb_link'   =>   'https://wpshadow.com/kb/browser-caching-not-optimized'
						);
						);,
						);
						}
						return null;
						}
						return null;
						}
						return null;
	}
}
