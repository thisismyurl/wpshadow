<?php
/**
 * Dark Mode Support Not Implemented Diagnostic
 *
 * Checks if dark mode is supported.
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
 * Dark Mode Support Not Implemented Diagnostic Class
 *
 * Detects missing dark mode.
 *
 * @since 1.2601.2352
 */
class Diagnostic_Dark_Mode_Support_Not_Implemented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'dark-mode-support-not-implemented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Dark Mode Support Not Implemented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if dark mode is supported';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for dark mode CSS
		if ( ! has_filter( 'wp_enqueue_scripts', 'load_dark_mode_css' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Dark mode support is not implemented. Add CSS media query for prefers-color-scheme and dark mode styling for modern user preferences.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 10,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/dark-mode-support-not-implemented',
			);
		}

		return null;
	}
}
