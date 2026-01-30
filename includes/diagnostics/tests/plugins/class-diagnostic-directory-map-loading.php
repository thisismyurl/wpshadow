<?php
/**
 * Directory Map Loading Diagnostic
 *
 * Directory maps slowing page load.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.565.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Directory Map Loading Diagnostic Class
 *
 * @since 1.565.0000
 */
class Diagnostic_DirectoryMapLoading extends Diagnostic_Base {

	protected static $slug = 'directory-map-loading';
	protected static $title = 'Directory Map Loading';
	protected static $description = 'Directory maps slowing page load';
	protected static $family = 'performance';

	public static function check() {
		if ( ! function_exists( 'wpbdp' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Maps enabled
		$maps_enabled = get_option( 'wpbdp_maps_enabled', 'yes' );
		if ( 'no' === $maps_enabled ) {
			return null; // Not using maps
		}
		
		// Check 2: Lazy loading
		$lazy_load = get_option( 'wpbdp_maps_lazy_load', 'no' );
		if ( 'no' === $lazy_load ) {
			$issues[] = __( 'No lazy loading (immediate API requests)', 'wpshadow' );
		}
		
		// Check 3: Marker clustering
		$clustering = get_option( 'wpbdp_maps_clustering', 'no' );
		if ( 'no' === $clustering ) {
			$issues[] = __( 'No marker clustering (slow rendering)', 'wpshadow' );
		}
		
		// Check 4: API key configured
		$api_key = get_option( 'wpbdp_maps_api_key', '' );
		if ( empty( $api_key ) ) {
			$issues[] = __( 'No API key (rate limits apply)', 'wpshadow' );
		}
		
		// Check 5: Map caching
		$cache_maps = get_option( 'wpbdp_maps_cache', 'no' );
		if ( 'no' === $cache_maps ) {
			$issues[] = __( 'Map tiles not cached (repeated requests)', 'wpshadow' );
		}
		
		// Check 6: Script loading
		$defer_scripts = get_option( 'wpbdp_maps_defer_scripts', 'no' );
		if ( 'no' === $defer_scripts ) {
			$issues[] = __( 'Scripts not deferred (blocking render)', 'wpshadow' );
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
				/* translators: %s: list of map loading issues */
				__( 'Directory maps have %d loading issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/directory-map-loading',
		);
	}
}
