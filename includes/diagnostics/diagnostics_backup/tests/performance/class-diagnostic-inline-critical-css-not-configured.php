<?php
/**
 * Inline Critical CSS Not Configured Diagnostic
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
 * Inline Critical CSS Not Configured Diagnostic Class
 *
 * Detects missing critical CSS inlining.
 *
 * @since 1.2601.2352
 */
class Diagnostic_Inline_Critical_CSS_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'inline-critical-css-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Inline Critical CSS Not Configured';

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
		// Check for critical CSS filter
		if ( ! has_filter( 'wp_print_styles' ) && ! has_action( 'wp_head', 'wp_print_inline_styles' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Critical CSS is not inlined. Inline above-the-fold CSS to improve page rendering time and Core Web Vitals.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 40,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/inline-critical-css-not-configured',
			);
		}

		return null;
	}
}
