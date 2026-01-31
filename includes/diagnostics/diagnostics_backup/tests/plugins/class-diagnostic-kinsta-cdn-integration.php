<?php
/**
 * Kinsta Cdn Integration Diagnostic
 *
 * Kinsta Cdn Integration needs attention.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.995.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Kinsta Cdn Integration Diagnostic Class
 *
 * @since 1.995.0000
 */
class Diagnostic_KinstaCdnIntegration extends Diagnostic_Base {

	protected static $slug = 'kinsta-cdn-integration';
	protected static $title = 'Kinsta Cdn Integration';
	protected static $description = 'Kinsta Cdn Integration needs attention';
	protected static $family = 'functionality';

	public static function check() {
		// Check for Kinsta environment
		if ( ! defined( 'KINSTA_CDN_USEAST1' ) && ! isset( $_SERVER['KINSTA_CACHE_ZONE'] ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: CDN enabled
		$cdn_enabled = get_option( 'kinsta_cdn_enabled', false );
		if ( ! $cdn_enabled ) {
			$issues[] = __( 'Kinsta CDN not enabled (missing performance benefit)', 'wpshadow' );
		}
		
		// Check 2: CDN cache zone configured
		$cache_zone = isset( $_SERVER['KINSTA_CACHE_ZONE'] ) ? sanitize_text_field( wp_unslash( $_SERVER['KINSTA_CACHE_ZONE'] ) ) : '';
		if ( empty( $cache_zone ) ) {
			$issues[] = __( 'Kinsta cache zone not detected', 'wpshadow' );
		}
		
		// Check 3: CDN URL rewriting
		$cdn_url = get_option( 'kinsta_cdn_url', '' );
		if ( empty( $cdn_url ) && $cdn_enabled ) {
			$issues[] = __( 'CDN enabled but URL rewriting not configured', 'wpshadow' );
		}
		
		// Check 4: SSL compatibility
		if ( is_ssl() && ! empty( $cdn_url ) && strpos( $cdn_url, 'https://' ) !== 0 ) {
			$issues[] = __( 'CDN URL not HTTPS (mixed content warnings)', 'wpshadow' );
		}
		
		// Check 5: Cache purge on update
		$auto_purge = get_option( 'kinsta_cdn_auto_purge', false );
		if ( ! $auto_purge ) {
			$issues[] = __( 'Automatic CDN cache purge not enabled', 'wpshadow' );
		}
		
		// Check 6: Subdomain CDN mapping
		if ( is_multisite() ) {
			$wildcard_cdn = get_site_option( 'kinsta_cdn_wildcard', false );
			if ( ! $wildcard_cdn ) {
				$issues[] = __( 'Multisite without wildcard CDN configuration', 'wpshadow' );
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
				__( 'Kinsta CDN integration has %d configuration issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => $threat_level,
			'threat_level' => $threat_level,
			'auto_fixable' => true,
			'kb_link'     => 'https://wpshadow.com/kb/kinsta-cdn-integration',
		);
	}
}
