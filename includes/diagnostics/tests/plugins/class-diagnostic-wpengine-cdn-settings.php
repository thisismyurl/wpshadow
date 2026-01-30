<?php
/**
 * Wpengine Cdn Settings Diagnostic
 *
 * Wpengine Cdn Settings needs attention.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.998.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wpengine Cdn Settings Diagnostic Class
 *
 * @since 1.998.0000
 */
class Diagnostic_WpengineCdnSettings extends Diagnostic_Base {

	protected static $slug = 'wpengine-cdn-settings';
	protected static $title = 'Wpengine Cdn Settings';
	protected static $description = 'Wpengine Cdn Settings needs attention';
	protected static $family = 'functionality';

	public static function check() {
		// Check for WP Engine hosting
		if ( ! defined( 'WPE_PLUGIN_VERSION' ) && ! function_exists( 'wpe_param' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: CDN enabled
		$cdn_enabled = defined( 'WPE_CDN_ENABLED' ) && WPE_CDN_ENABLED;
		if ( ! $cdn_enabled ) {
			return null;
		}
		
		// Check 2: Smart CDN configuration
		$smart_cdn = get_option( 'wpe_smart_cdn', true );
		if ( ! $smart_cdn ) {
			$issues[] = __( 'Smart CDN not enabled (missing automatic optimization)', 'wpshadow' );
		}
		
		// Check 3: Cache exclusions
		$excluded_paths = get_option( 'wpe_cdn_exclude_paths', array() );
		if ( count( $excluded_paths ) > 10 ) {
			$issues[] = sprintf( __( '%d CDN path exclusions (reduces CDN benefit)', 'wpshadow' ), count( $excluded_paths ) );
		}
		
		// Check 4: Image optimization
		$image_optimization = get_option( 'wpe_image_optimization', false );
		if ( ! $image_optimization ) {
			$issues[] = __( 'CDN image optimization not enabled (slower image delivery)', 'wpshadow' );
		}
		
		// Check 5: Edge cache TTL
		$edge_ttl = get_option( 'wpe_edge_cache_ttl', 3600 );
		if ( $edge_ttl < 1800 ) {
			$issues[] = sprintf( __( 'Low edge cache TTL: %d seconds (frequent cache misses)', 'wpshadow' ), $edge_ttl );
		}
		
		// Check 6: Automatic cache purge
		$auto_purge = get_option( 'wpe_auto_purge_cdn', true );
		if ( ! $auto_purge ) {
			$issues[] = __( 'Automatic CDN purge disabled (stale content risk)', 'wpshadow' );
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
				/* translators: %s: list of CDN configuration issues */
				__( 'WP Engine CDN settings have %d optimization opportunities: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => true,
			'kb_link'     => 'https://wpshadow.com/kb/wpengine-cdn-settings',
		);
	}
}
