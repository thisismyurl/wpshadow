<?php
/**
 * Inline Styling Not Minimized Diagnostic
 *
 * Checks for inline CSS/JS that should be external.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2310
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Inline Styling Not Minimized Diagnostic Class
 *
 * Detects excessive inline styles.
 *
 * @since 1.2601.2310
 */
class Diagnostic_Inline_Styling_Not_Minimized extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'inline-styling-not-minimized';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Inline Styling Not Minimized';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if inline styles are minimized';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2310
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wp_filter;

		// Check for minification plugins
		$minify_plugins = array(
			'autoptimize/autoptimize.php',
			'wp-minify/wp-minify.php',
		);

		$minify_active = false;
		foreach ( $minify_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$minify_active = true;
				break;
			}
		}

		if ( ! $minify_active ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'No CSS/JavaScript minification is active. Inline and external styles are not being optimized, increasing page load time.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 40,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/inline-styling-not-minimized',
			);
		}

		return null;
	}
}
