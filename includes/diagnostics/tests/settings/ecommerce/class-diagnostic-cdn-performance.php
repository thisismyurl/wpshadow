<?php
/**
 * CDN Performance Diagnostic
 *
 * Checks if static assets are served from CDN.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6035.1400
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CDN Performance Diagnostic Class
 *
 * Verifies that a CDN is configured and that static assets are
 * being served from it for optimal performance.
 *
 * @since 1.6035.1400
 */
class Diagnostic_CDN_Performance extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'cdn-performance';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'CDN Performance';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if static assets are served from CDN';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'ecommerce';

	/**
	 * Run the CDN performance diagnostic check.
	 *
	 * @since  1.6035.1400
	 * @return array|null Finding array if CDN issues detected, null otherwise.
	 */
	public static function check() {
		$issues    = array();
		$warnings  = array();
		$stats     = array();

		// Check for CDN plugins.
		$cdn_plugins = array(
			'cloudflare/cloudflare.php',
			'wp-super-cache/wp-cache.php',
			'w3-total-cache/w3-total-cache.php',
			'bunny-cdn/bunnycdn.php',
		);

		$active_cdn_plugin = null;
		foreach ( $cdn_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$active_cdn_plugin = $plugin;
				break;
			}
		}

		$stats['cdn_plugin'] = $active_cdn_plugin ?: 'None';

		if ( ! $active_cdn_plugin ) {
			$warnings[] = __( 'No CDN plugin active - static assets not being delivered via CDN', 'wpshadow' );
		}

		// Check for Cloudflare.
		$cloudflare_enabled = get_option( 'cloudflare_api_key' );
		$stats['cloudflare_enabled'] = ! empty( $cloudflare_enabled );

		if ( empty( $cloudflare_enabled ) ) {
			$warnings[] = __( 'Cloudflare not configured - consider using it for CDN', 'wpshadow' );
		}

		// Check CDN URL configuration.
		$cdn_url = get_option( 'cdn_url' );
		$stats['cdn_url'] = ! empty( $cdn_url ) ? 'Configured' : 'Not configured';

		if ( empty( $cdn_url ) ) {
			$warnings[] = __( 'CDN URL not configured', 'wpshadow' );
		}

		// Check for image optimization CDN.
		$image_cdn_plugins = array(
			'optimole-wp/optimole-wp.php',
			'imagify/imagify.php',
		);

		$has_image_cdn = false;
		foreach ( $image_cdn_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_image_cdn = true;
				break;
			}
		}

		$stats['image_optimization_cdn'] = $has_image_cdn;

		if ( ! $has_image_cdn ) {
			$warnings[] = __( 'No image optimization CDN - images not optimized for delivery', 'wpshadow' );
		}

		// Check for cache purge on updates.
		$auto_purge_cache = get_option( 'cdn_auto_purge_on_update' );
		$stats['auto_cache_purge'] = boolval( $auto_purge_cache );

		if ( ! $auto_purge_cache ) {
			$warnings[] = __( 'Automatic CDN cache purge on updates not enabled', 'wpshadow' );
		}

		// Check CDN coverage.
		$cdn_coverage = get_option( 'cdn_coverage' );
		$stats['cdn_coverage'] = $cdn_coverage ?: 'Not set';

		// Check for CSS/JS minification through CDN.
		$minify_css = get_option( 'cdn_minify_css' );
		$minify_js = get_option( 'cdn_minify_js' );

		$stats['cdn_minify_css'] = boolval( $minify_css );
		$stats['cdn_minify_js'] = boolval( $minify_js );

		if ( ! $minify_css || ! $minify_js ) {
			$warnings[] = __( 'CSS/JS minification through CDN not fully enabled', 'wpshadow' );
		}

		// Check for GZIP compression.
		$gzip_enabled = get_option( 'cdn_gzip_compression' );
		$stats['gzip_compression'] = boolval( $gzip_enabled );

		if ( ! $gzip_enabled ) {
			$warnings[] = __( 'GZIP compression not enabled on CDN', 'wpshadow' );
		}

		// Check for HTTP/2 support.
		$http2_support = get_option( 'cdn_http2_support' );
		$stats['http2_support'] = boolval( $http2_support );

		if ( ! $http2_support ) {
			$warnings[] = __( 'HTTP/2 not enabled on CDN - slower asset delivery', 'wpshadow' );
		}

		// Check for edge caching.
		$edge_cache = get_option( 'cdn_edge_cache_ttl' );
		$stats['edge_cache_ttl'] = ! empty( $edge_cache ) ? intval( $edge_cache ) . ' seconds' : 'Not set';

		if ( empty( $edge_cache ) ) {
			$warnings[] = __( 'Edge caching TTL not configured', 'wpshadow' );
		}

		// Check CDN bandwidth usage.
		$cdn_bandwidth = get_option( 'cdn_bandwidth_used_gb' );
		$stats['cdn_bandwidth_gb'] = ! empty( $cdn_bandwidth ) ? intval( $cdn_bandwidth ) : 'Not tracked';

		// If critical issues found.
		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'CDN performance has critical issues: ', 'wpshadow' ) . implode( ', ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/cdn-performance',
				'context'      => array(
					'stats'    => $stats,
					'issues'   => $issues,
					'warnings' => $warnings,
				),
			);
		}

		// If only warnings.
		if ( ! empty( $warnings ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'CDN performance has recommendations: ', 'wpshadow' ) . implode( ', ', $warnings ),
				'severity'     => 'medium',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/cdn-performance',
				'context'      => array(
					'stats'    => $stats,
					'warnings' => $warnings,
				),
			);
		}

		return null; // CDN is working well.
	}
}
