<?php
/**
 * Core Web Vitals Baseline Diagnostic
 *
 * Validates that Core Web Vitals (LCP, FID/INP, CLS) are being measured
 * through a real user monitoring baseline.
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
 * Core Web Vitals Baseline Diagnostic Class
 *
 * Checks for measurement tooling that captures Core Web Vitals metrics.
 *
 * @since 1.6035.0900
 */
class Diagnostic_Core_Web_Vitals_Baseline extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'core-web-vitals-baseline';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Core Web Vitals Baseline';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether Core Web Vitals are being measured with a real user baseline';

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
		$rum_plugins = array(
			'web-vitals-reporter/web-vitals-reporter.php' => 'Web Vitals Reporter',
			'cloudflare/cloudflare.php'                  => 'Cloudflare Web Analytics',
			'newrelic-for-php/newrelic.php'               => 'New Relic',
			'jetpack/jetpack.php'                         => 'Jetpack',
		);

		$active_plugin = self::get_first_active_plugin( $rum_plugins );
		$has_web_vitals_script = self::has_script_match(
			array(
				'web-vitals',
				'vitals',
				'pagespeed',
				'gtag/js',
				'google-analytics.com',
				'analytics.google.com',
			)
		);

		if ( ! $active_plugin && ! $has_web_vitals_script ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Core Web Vitals baseline is not detected. Add a RUM solution that measures LCP, INP/FID, and CLS so you can track real user experience.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/core-web-vitals-baseline',
				'meta'         => array(
					'active_plugin' => $active_plugin,
					'recommendation' => __( 'Install a Web Vitals or RUM tool and ensure it reports LCP, INP/FID, and CLS.', 'wpshadow' ),
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
