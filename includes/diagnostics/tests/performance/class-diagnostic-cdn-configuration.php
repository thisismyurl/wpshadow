<?php
/**
 * CDN Configuration Diagnostic
 *
 * Detects missing or misconfigured CDN setup that prevents geographic optimization benefits.
 *
 * @since 1.6093.1200
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CDN Configuration Diagnostic Class
 *
 * Validates CDN setup for geographic performance optimization.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Cdn_Configuration extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'cdn-configuration';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'CDN Configuration';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if CDN is configured for static asset delivery';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issues found, null otherwise.
	 */
	public static function check() {
		$cdn_configured = false;
		$cdn_url        = '';
		$cdn_plugin     = '';

		// Check common CDN plugins
		$cdn_plugins = array(
			'cdn-enabler/cdn-enabler.php'                    => 'CDN Enabler',
			'w3-total-cache/w3-total-cache.php'              => 'W3 Total Cache',
			'wp-super-cache/wp-cache.php'                    => 'WP Super Cache',
			'wp-cloudflare-super-page-cache/wp-cloudflare-super-page-cache.php' => 'WP Cloudflare Cache',
			'jetpack/jetpack.php'                            => 'Jetpack (with CDN)',
		);

		foreach ( $cdn_plugins as $plugin_path => $plugin_name ) {
			if ( is_plugin_active( $plugin_path ) ) {
				$cdn_configured = true;
				$cdn_plugin     = $plugin_name;
				break;
			}
		}

		// Check for CDN URL configuration via filters
		if ( has_filter( 'wp_resource_hints' ) || has_filter( 'home_url' ) ) {
			// Custom CDN setup
			$cdn_configured = true;
		}

		// Check WP_CONTENT_URL override
		if ( defined( 'WP_CONTENT_URL' ) && WP_CONTENT_URL !== content_url() ) {
			$cdn_configured = true;
			$cdn_url        = WP_CONTENT_URL;
		}

		if ( ! $cdn_configured ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'CDN is not configured. Using a CDN reduces latency and improves TTFB by 40-60%% for global visitors.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 30,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/cdn-configuration',
				'meta'          => array(
					'recommendation'    => 'Consider using: Cloudflare, BunnyCDN, KeyCDN, Amazon CloudFront, or local CDN plugins',
					'impact'            => 'Reduces TTFB by 40-60%, improves LCP by 15-25%, benefits global visitors most',
					'cost'              => 'Cloudflare Free tier or $10-50/month for premium CDNs',
					'setup_complexity'  => 'Easy (10-30 minutes with most services)',
					'performance_gain'  => 'Excellent for geographically distributed visitors',
			),
			);
		}

		return null;
	}
}
