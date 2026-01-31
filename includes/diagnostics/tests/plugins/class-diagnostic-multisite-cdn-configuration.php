<?php
/**
 * Multisite Cdn Configuration Diagnostic
 *
 * Multisite Cdn Configuration misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.975.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Multisite Cdn Configuration Diagnostic Class
 *
 * @since 1.975.0000
 */
class Diagnostic_MultisiteCdnConfiguration extends Diagnostic_Base {

	protected static $slug = 'multisite-cdn-configuration';
	protected static $title = 'Multisite Cdn Configuration';
	protected static $description = 'Multisite Cdn Configuration misconfigured';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! is_multisite() ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: CDN enabled network-wide
		$cdn_enabled = get_site_option( 'multisite_cdn_enabled', false );
		if ( ! $cdn_enabled ) {
			return null; // No CDN configured
		}
		
		// Check 2: CDN URL configured
		$cdn_url = get_site_option( 'multisite_cdn_url', '' );
		if ( empty( $cdn_url ) ) {
			$issues[] = __( 'CDN enabled but URL not configured', 'wpshadow' );
		}
		
		// Check 3: SSL compatibility
		if ( ! empty( $cdn_url ) && strpos( $cdn_url, 'https://' ) !== 0 && is_ssl() ) {
			$issues[] = __( 'CDN URL not HTTPS (mixed content warnings)', 'wpshadow' );
		}
		
		// Check 4: Per-site CDN configuration conflicts
		$sites = get_sites( array( 'number' => 100 ) );
		$conflicting_sites = 0;
		
		foreach ( $sites as $site ) {
			switch_to_blog( $site->blog_id );
			$site_cdn = get_option( 'site_cdn_url', '' );
			restore_current_blog();
			
			if ( ! empty( $site_cdn ) && $site_cdn !== $cdn_url ) {
				$conflicting_sites++;
			}
		}
		
		if ( $conflicting_sites > 0 ) {
			$issues[] = sprintf( __( '%d sites with different CDN URLs (inconsistent config)', 'wpshadow' ), $conflicting_sites );
		}
		
		// Check 5: Cache busting enabled
		$cache_bust = get_site_option( 'multisite_cdn_cache_bust', false );
		if ( ! $cache_bust ) {
			$issues[] = __( 'CDN cache busting not enabled (stale assets possible)', 'wpshadow' );
		}
		
		// Check 6: Subdomain mapping
		global $wpdb;
		$mapped_domains = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->blogs} WHERE domain NOT LIKE CONCAT('%', '{$wpdb->get_blog_prefix( 1 )}', '%')"
		);
		
		if ( $mapped_domains > 0 && ! empty( $cdn_url ) ) {
			$wildcard_cdn = get_site_option( 'multisite_cdn_wildcard_ssl', false );
			if ( ! $wildcard_cdn ) {
				$issues[] = sprintf( __( '%d mapped domains without wildcard SSL for CDN', 'wpshadow' ), $mapped_domains );
			}
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
				/* translators: %s: list of configuration issues */
				__( 'Multisite CDN has %d configuration issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => $threat_level,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/multisite-cdn-configuration',
		);
	}
}
