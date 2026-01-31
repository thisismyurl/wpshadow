<?php
/**
 * Google Cloud Cdn Configuration Diagnostic
 *
 * Google Cloud Cdn Configuration needs attention.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1013.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Google Cloud Cdn Configuration Diagnostic Class
 *
 * @since 1.1013.0000
 */
class Diagnostic_GoogleCloudCdnConfiguration extends Diagnostic_Base {

	protected static $slug = 'google-cloud-cdn-configuration';
	protected static $title = 'Google Cloud Cdn Configuration';
	protected static $description = 'Google Cloud Cdn Configuration needs attention';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'GCP_PLUGIN_VERSION' ) && ! get_option( 'gcp_cdn_enabled' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Verify CDN is enabled
		$cdn_enabled = get_option( 'gcp_cdn_enabled', false );
		if ( ! $cdn_enabled ) {
			$issues[] = __( 'Google Cloud CDN not enabled', 'wpshadow' );
		}

		// Check 2: Check CDN cache configuration
		$cache_ttl = get_option( 'gcp_cdn_cache_ttl', 0 );
		if ( $cache_ttl < 3600 ) {
			$issues[] = __( 'CDN cache TTL too low for optimal performance', 'wpshadow' );
		}

		// Check 3: Verify origin server configuration
		$origin_host = get_option( 'gcp_cdn_origin_host', '' );
		if ( empty( $origin_host ) ) {
			$issues[] = __( 'CDN origin server not configured', 'wpshadow' );
		}

		// Check 4: Check SSL/TLS for CDN endpoints
		$cdn_ssl = get_option( 'gcp_cdn_ssl_enabled', false );
		if ( ! $cdn_ssl ) {
			$issues[] = __( 'SSL/TLS not enabled for CDN endpoints', 'wpshadow' );
		}

		// Check 5: Verify cache invalidation strategy
		$cache_invalidation = get_option( 'gcp_cdn_cache_invalidation_enabled', false );
		if ( ! $cache_invalidation ) {
			$issues[] = __( 'CDN cache invalidation not configured', 'wpshadow' );
		}

		// Check 6: Check CDN URL rewriting
		$url_rewrite = get_option( 'gcp_cdn_url_rewrite', false );
		if ( ! $url_rewrite ) {
			$issues[] = __( 'CDN URL rewriting not enabled', 'wpshadow' );
		}
		return null;
	}
}
