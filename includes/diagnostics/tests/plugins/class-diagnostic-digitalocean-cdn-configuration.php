<?php
/**
 * Digitalocean Cdn Configuration Diagnostic
 *
 * Digitalocean Cdn Configuration needs attention.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1016.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Digitalocean Cdn Configuration Diagnostic Class
 *
 * @since 1.1016.0000
 */
class Diagnostic_DigitaloceanCdnConfiguration extends Diagnostic_Base {

	protected static $slug = 'digitalocean-cdn-configuration';
	protected static $title = 'Digitalocean Cdn Configuration';
	protected static $description = 'Digitalocean Cdn Configuration needs attention';
	protected static $family = 'functionality';

	public static function check() {
		// Check for DigitalOcean Spaces integration
		$has_do_cdn = defined( 'DO_SPACES_KEY' ) || get_option( 'digitalocean_cdn_enabled', '' ) !== '';
		
		if ( ! $has_do_cdn ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: CDN endpoint
		$cdn_endpoint = get_option( 'do_cdn_endpoint', '' );
		if ( empty( $cdn_endpoint ) ) {
			$issues[] = __( 'No CDN endpoint configured (slow delivery)', 'wpshadow' );
		}
		
		// Check 2: SSL/TLS
		$ssl_enabled = get_option( 'do_cdn_ssl', 'no' );
		if ( 'no' === $ssl_enabled && is_ssl() ) {
			$issues[] = __( 'CDN without SSL on HTTPS site (mixed content)', 'wpshadow' );
		}
		
		// Check 3: Cache control headers
		$cache_control = get_option( 'do_cdn_cache_control', '' );
		if ( empty( $cache_control ) ) {
			$issues[] = __( 'No cache control headers (inefficient caching)', 'wpshadow' );
		}
		
		// Check 4: TTL configuration
		$ttl = get_option( 'do_cdn_ttl', 0 );
		if ( $ttl < 3600 ) {
			$issues[] = __( 'TTL under 1 hour (frequent origin requests)', 'wpshadow' );
		}
		
		// Check 5: CORS configuration
		$cors = get_option( 'do_cdn_cors', 'no' );
		if ( 'no' === $cors ) {
			$issues[] = __( 'CORS not configured (cross-origin errors)', 'wpshadow' );
		}
		
		// Check 6: Purge capability
		$purge_enabled = get_option( 'do_cdn_purge', 'no' );
		if ( 'no' === $purge_enabled ) {
			$issues[] = __( 'No CDN purge capability (stale content)', 'wpshadow' );
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 50;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 62;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 56;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				__( 'DigitalOcean CDN has %d configuration issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/digitalocean-cdn-configuration',
		);
	}
}
