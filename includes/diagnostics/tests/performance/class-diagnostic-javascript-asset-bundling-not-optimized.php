<?php
/**
 * JavaScript Asset Bundling Not Optimized Diagnostic
 *
 * Checks if JavaScript is optimized.
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
 * JavaScript Asset Bundling Not Optimized Diagnostic Class
 *
 * Detects unoptimized JavaScript.
 *
 * @since 1.2601.2352
 */
class Diagnostic_JavaScript_Asset_Bundling_Not_Optimized extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'javascript-asset-bundling-not-optimized';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'JavaScript Asset Bundling Not Optimized';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if JavaScript is optimized';

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
		// Check for JS optimization
		if ( ! has_filter( 'wp_print_scripts', 'wp_minify_javascript' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'JavaScript asset bundling is not optimized. Minify, concatenate, and defer JavaScript files for faster page loads.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 35,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/javascript-asset-bundling-not-optimized',
			);
		}

		return null;
	}
}
