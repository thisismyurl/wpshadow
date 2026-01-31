<?php
/**
 * Lazy Loading For CSS Background Images Not Implemented Diagnostic
 *
 * Checks if CSS background image lazy loading is implemented.
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
 * Lazy Loading For CSS Background Images Not Implemented Diagnostic Class
 *
 * Detects missing CSS background image lazy loading.
 *
 * @since 1.2601.2352
 */
class Diagnostic_Lazy_Loading_For_CSS_Background_Images_Not_Implemented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'lazy-loading-for-css-background-images-not-implemented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Lazy Loading For CSS Background Images Not Implemented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if CSS background image lazy loading is implemented';

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
		// Check for background image lazy loading
		if ( ! has_filter( 'wp_head', 'enable_bg_image_lazy_load' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'CSS background image lazy loading is not implemented. Use JavaScript to defer loading of CSS background images until they are visible in the viewport.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 12,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/lazy-loading-for-css-background-images-not-implemented',
			);
		}

		return null;
	}
}
