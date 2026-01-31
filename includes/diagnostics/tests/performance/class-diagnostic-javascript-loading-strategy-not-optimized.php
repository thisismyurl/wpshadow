<?php
/**
 * JavaScript Loading Strategy Not Optimized Diagnostic
 *
 * Checks if JavaScript loading is optimized.
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
 * JavaScript Loading Strategy Not Optimized Diagnostic Class
 *
 * Detects unoptimized JS loading.
 *
 * @since 1.2601.2352
 */
class Diagnostic_JavaScript_Loading_Strategy_Not_Optimized extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'javascript-loading-strategy-not-optimized';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'JavaScript Loading Strategy Not Optimized';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if JavaScript loading is optimized';

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
		// Check if JS optimization is implemented
		if ( ! has_filter( 'wp_enqueue_scripts', 'optimize_js_loading' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'JavaScript loading is not optimized. Use async/defer attributes, code splitting, and defer non-critical JavaScript to improve page speed.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 40,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/javascript-loading-strategy-not-optimized',
			);
		}

		return null;
	}
}
