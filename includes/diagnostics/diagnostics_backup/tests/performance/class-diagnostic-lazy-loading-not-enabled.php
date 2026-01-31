<?php
/**
 * Lazy Loading Not Enabled Diagnostic
 *
 * Checks if lazy loading is configured for media.
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
 * Lazy Loading Not Enabled Diagnostic Class
 *
 * Detects missing lazy loading implementation.
 *
 * @since 1.2601.2310
 */
class Diagnostic_Lazy_Loading_Not_Enabled extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'lazy-loading-not-enabled';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Lazy Loading Not Enabled';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if lazy loading is configured';

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
		// Check for native WordPress lazy loading support (WP 5.5+)
		if ( function_exists( 'wp_lazy_loading_enabled' ) ) {
			if ( wp_lazy_loading_enabled( 'img' ) ) {
				return null; // Native lazy loading is enabled
			}
		}

		// Check for lazy loading plugins
		$lazy_plugins = array(
			'a3-lazy-load/a3-lazy-load.php',
			'lazy-load-for-images/lazy_load.php',
			'rocket-lazy-load/rocket-lazy-load.php',
			'ewww-image-optimizer/ewww-image-optimizer.php',
		);

		$lazy_active = false;
		foreach ( $lazy_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$lazy_active = true;
				break;
			}
		}

		if ( ! $lazy_active ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Lazy loading is not enabled. Images below the fold are still loading on page load, wasting bandwidth and slowing initial page render.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 45,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/lazy-loading-not-enabled',
			);
		}

		return null;
	}
}
