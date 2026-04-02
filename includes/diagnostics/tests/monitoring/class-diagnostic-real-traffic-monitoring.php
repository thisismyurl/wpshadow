<?php
/**
 * Real Traffic Monitoring Diagnostic
 *
 * Validates that real traffic monitoring is configured (analytics or RUM).
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Real Traffic Monitoring Diagnostic Class
 *
 * Detects missing analytics or RUM tracking for real user traffic.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Real_Traffic_Monitoring extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'real-traffic-monitoring';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Real Traffic Monitoring';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if analytics or RUM is configured to capture real user traffic';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'monitoring';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$analytics_plugins = array(
			'google-site-kit/google-site-kit.php' => 'Site Kit by Google',
			'google-analytics-for-wordpress/googleanalytics.php' => 'MonsterInsights',
			'ga-google-analytics/ga-google-analytics.php' => 'GA Google Analytics',
			'matomo/matomo.php' => 'Matomo Analytics',
			'plausible-analytics/plausible-analytics.php' => 'Plausible Analytics',
			'fathom-analytics/fathom-analytics.php' => 'Fathom Analytics',
			'jetpack/jetpack.php' => 'Jetpack',
		);

		$active_plugin = self::get_first_active_plugin( $analytics_plugins );
		$has_tracking_script = self::has_script_match(
			array(
				'gtag/js',
				'google-analytics.com',
				'googletagmanager.com',
				'matomo.js',
				'plausible.io',
				'fathom.js',
				'analytics.js',
				'cloudflareinsights.com',
			)
		);

		if ( ! $active_plugin && ! $has_tracking_script ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Real traffic monitoring is not detected. Enable analytics or RUM to see actual user behavior and performance trends.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/real-traffic-monitoring',
				'meta'         => array(
					'active_plugin' => $active_plugin,
					'recommendation' => __( 'Install analytics (GA4, Matomo) or a RUM provider to track real user visits.', 'wpshadow' ),
				),
			);
		}

		return null;
	}

	/**
	 * Get the first active plugin from a list.
	 *
	 * @since 1.6093.1200
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
	 * @since 1.6093.1200
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
