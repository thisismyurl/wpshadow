<?php
/**
 * Geographic Performance Distribution Diagnostic
 *
 * Analyzes performance across geographic regions.
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
 * Geographic Performance Distribution Diagnostic Class
 *
 * Monitors performance across geographic regions.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Geographic_Performance_Distribution extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'geographic-performance-distribution';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Geographic Performance Distribution';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Analyzes performance across geographic regions';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'real-user-monitoring';

	/**
	 * Performance variance threshold (percentage)
	 *
	 * @var int
	 */
	private const VARIANCE_THRESHOLD = 30;

	/**
	 * Run the geographic performance diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if geographic variance detected, null otherwise.
	 */
	public static function check() {
		$cache_key = 'wpshadow_geo_performance';
		$cached = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		// Check if CDN is configured (helps with geographic performance).
		$has_cdn = self::check_cdn_configuration();

		if ( ! $has_cdn ) {
			$result = array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'No CDN detected. Deploy a Content Delivery Network (CloudFlare, Bunny, etc.) to improve performance for global users.', 'wpshadow' ),
				'severity'    => 'low',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/setup-content-delivery-network',
			);
		} else {
			$result = null;
		}

		set_transient( $cache_key, $result, 24 * HOUR_IN_SECONDS );

		return $result;
	}

	/**
	 * Check if CDN is configured.
	 *
	 * @since 1.6093.1200
	 * @return bool True if CDN configured.
	 */
	private static function check_cdn_configuration(): bool {
		// Check for Cloudflare.
		$cf_api_key = get_option( 'cloudflare_api_key' );
		if ( $cf_api_key ) {
			return true;
		}

		// Check for Bunny CDN.
		$bunny_token = get_option( 'bunny_cdn_token' );
		if ( $bunny_token ) {
			return true;
		}

		// Check for WP Offload Media (S3-based CDN).
		if ( class_exists( 'AS3CF' ) ) {
			return true;
		}

		// Check for CDN plugins.
		$cdn_plugins = array(
			'cloudflare/cloudflare.php',
			'bunny-cdn/bunny-cdn.php',
			'wp-offload-media/wp-offload-media.php',
			'cdn-enabler/cdn-enabler.php',
		);

		foreach ( $cdn_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				return true;
			}
		}

		return false;
	}
}
