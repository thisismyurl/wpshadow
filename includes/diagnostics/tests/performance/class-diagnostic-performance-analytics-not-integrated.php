<?php
/**
 * Performance Analytics Not Integrated Diagnostic
 *
 * Checks if performance analytics are integrated.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2349
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Performance Analytics Not Integrated Diagnostic Class
 *
 * Detects missing performance analytics.
 *
 * @since 1.2601.2349
 */
class Diagnostic_Performance_Analytics_Not_Integrated extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'performance-analytics-not-integrated';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Performance Analytics Not Integrated';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if performance analytics are integrated';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2349
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for analytics plugins
		$analytics_plugins = array(
			'jetpack/jetpack.php',
			'google-analytics-dashboard-for-wp/gadwp.php',
			'site-kit/google-site-kit.php',
		);

		$analytics_active = false;
		foreach ( $analytics_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$analytics_active = true;
				break;
			}
		}

		if ( ! $analytics_active ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Performance analytics are not integrated. Add Google Analytics or similar to track user behavior and page performance.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 20,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/performance-analytics-not-integrated',
			);
		}

		return null;
	}
}
