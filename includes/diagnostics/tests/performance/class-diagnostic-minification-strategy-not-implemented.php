<?php
/**
 * Minification Strategy Not Implemented Diagnostic
 *
 * Checks if minification strategy is implemented.
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
 * Minification Strategy Not Implemented Diagnostic Class
 *
 * Detects missing minification strategy.
 *
 * @since 1.2601.2352
 */
class Diagnostic_Minification_Strategy_Not_Implemented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'minification-strategy-not-implemented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Minification Strategy Not Implemented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if minification strategy is implemented';

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
		// Check for CSS and JS minification
		if ( ! has_filter( 'style_loader_tag', 'minify_css' ) && ! has_filter( 'script_loader_tag', 'minify_js' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Minification strategy is not implemented. Minify CSS and JavaScript files to reduce file sizes by 20-40% and improve page load performance.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 60,
				'auto_fixable'  => true,
				'kb_link'       => 'https://wpshadow.com/kb/minification-strategy-not-implemented',
			);
		}

		return null;
	}
}
