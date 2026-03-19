<?php
/**
 * Web Font Loading Strategy Not Optimized Diagnostic
 *
 * Checks if web font loading is optimized.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Web Font Loading Strategy Not Optimized Diagnostic Class
 *
 * Detects unoptimized font loading.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Web_Font_Loading_Strategy_Not_Optimized extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'web-font-loading-strategy-not-optimized';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Web Font Loading Strategy Not Optimized';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if web font loading is optimized';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if font loading is optimized
		if ( ! has_filter( 'wp_head', 'optimize_font_loading' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Web font loading is not optimized. Use font-display: swap and preload critical fonts to improve perceived page load speed.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 20,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/web-font-loading-strategy-not-optimized',
			);
		}

		return null;
	}
}
