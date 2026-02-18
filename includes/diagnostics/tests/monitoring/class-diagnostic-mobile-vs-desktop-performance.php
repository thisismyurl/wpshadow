<?php
/**
 * Mobile vs Desktop Performance Diagnostic
 *
 * Ensures performance monitoring can compare mobile and desktop metrics.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6035.0900
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile vs Desktop Performance Diagnostic Class
 *
 * Checks if analytics or RUM can segment metrics by device category.
 *
 * @since 1.6035.0900
 */
class Diagnostic_Mobile_Vs_Desktop_Performance extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-vs-desktop-performance';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Mobile vs Desktop Performance';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether performance monitoring is segmented by mobile and desktop users';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'monitoring';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6035.0900
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$segment_plugins = array(
			'google-site-kit/google-site-kit.php' => 'Site Kit by Google (GA4)',
			'google-analytics-for-wordpress/googleanalytics.php' => 'MonsterInsights',
			'matomo/matomo.php' => 'Matomo Analytics',
			'plausible-analytics/plausible-analytics.php' => 'Plausible Analytics',
			'fathom-analytics/fathom-analytics.php' => 'Fathom Analytics',
			'jetpack/jetpack.php' => 'Jetpack',
			'cloudflare/cloudflare.php' => 'Cloudflare Web Analytics',
		);

		$active_plugin = self::get_first_active_plugin( $segment_plugins );
		$has_device_segmenting_script = self::has_script_match(
			array(
				'gtag/js',
				'google-analytics.com',
				'googletagmanager.com',
				'matomo.js',
				'plausible.io',
				'fathom.js',
				'cloudflareinsights.com',
			)
		);

		if ( ! $active_plugin && ! $has_device_segmenting_script ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Mobile vs desktop performance segmentation is not detected. Without device breakdowns, you may miss slow mobile experiences that hurt conversions.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/mobile-vs-desktop-performance',
				'meta'         => array(
					'active_plugin' => $active_plugin,
					'recommendation' => __( 'Enable GA4 or RUM segmentation so you can compare mobile and desktop Web Vitals.', 'wpshadow' ),
				),
			);
		}

		return null;
	}

	/**
	 * Get the first active plugin from a list.
	 *
	 * @since  1.6035.0900
	 * @param  array $plugins Plugin list (file => label).
	 * @return string|null Active plugin label or null.
	 */
	private static function get_first_active_plugin( array $plugins ): ?string {
		foreach ( $plugins as $plugin => $label ) {
			if ( is_plugin_active( $plugin ) ) {
				return $label;
			}
		}

		return null;
	}

	/**
	 * Check for a matching script source.
	 *
	 * @since  1.6035.0900
	 * @param  array $needles List of substrings to match.
	 * @return bool True when any script matches.
	 */
	private static function has_script_match( array $needles ): bool {
		global $wp_scripts;

		if ( ! is_object( $wp_scripts ) || empty( $wp_scripts->registered ) ) {
			return false;
		}

		foreach ( $wp_scripts->registered as $script ) {
			if ( empty( $script->src ) ) {
				continue;
			}

			foreach ( $needles as $needle ) {
				if ( false !== strpos( $script->src, $needle ) ) {
					return true;
				}
			}
		}

		return false;
	}
}
