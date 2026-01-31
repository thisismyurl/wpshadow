<?php
/**
 * Inline Critical CSS Not Optimized Diagnostic
 *
 * Checks if critical CSS is inlined.
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
 * Inline Critical CSS Not Optimized Diagnostic Class
 *
 * Detects missing critical CSS inlining.
 *
 * @since 1.2601.2352
 */
class Diagnostic_Inline_Critical_CSS_Not_Optimized extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'inline-critical-css-not-optimized';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Inline Critical CSS Not Optimized';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if critical CSS is inlined';

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
		// Check for critical CSS optimization
		if ( ! has_filter( 'wp_head', 'inline_critical_css' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Critical CSS is not inlined. Extract and inline the minimum CSS needed to render above-the-fold content to improve First Contentful Paint (FCP) score.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 55,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/inline-critical-css-not-optimized',
			);
		}

		return null;
	}
}
