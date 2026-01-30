<?php
/**
 * Perfmatters Preload Configuration Diagnostic
 *
 * Perfmatters Preload Configuration not optimized.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.921.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Perfmatters Preload Configuration Diagnostic Class
 *
 * @since 1.921.0000
 */
class Diagnostic_PerfmattersPreloadConfiguration extends Diagnostic_Base {

	protected static $slug = 'perfmatters-preload-configuration';
	protected static $title = 'Perfmatters Preload Configuration';
	protected static $description = 'Perfmatters Preload Configuration not optimized';
	protected static $family = 'performance';

	public static function check() {
		// Check for Perfmatters plugin
		if ( ! function_exists( 'perfmatters_plugin_loaded' ) && ! defined( 'PERFMATTERS_VERSION' ) ) {
			return null;
		}
		
		$issues = array();
		$options = get_option( 'perfmatters_options', array() );
		
		// Check 1: Critical CSS enabled
		$critical_css = isset( $options['assets']['critical_css'] ) ? $options['assets']['critical_css'] : false;
		if ( ! $critical_css ) {
			$issues[] = __( 'Critical CSS not enabled (render-blocking CSS)', 'wpshadow' );
		}
		
		// Check 2: Font preloading
		$preload_fonts = isset( $options['preload']['fonts'] ) ? $options['preload']['fonts'] : array();
		if ( empty( $preload_fonts ) ) {
			$issues[] = __( 'No fonts configured for preloading (FOUT/FOIT)', 'wpshadow' );
		}
		
		// Check 3: DNS prefetch configuration
		$dns_prefetch = isset( $options['preload']['dns_prefetch'] ) ? $options['preload']['dns_prefetch'] : '';
		if ( empty( $dns_prefetch ) ) {
			$issues[] = __( 'DNS prefetch not configured (slower external resource loading)', 'wpshadow' );
		}
		
		// Check 4: Preconnect domains
		$preconnect = isset( $options['preload']['preconnect'] ) ? $options['preload']['preconnect'] : '';
		$external_resources = array( 'google', 'facebook', 'youtube', 'vimeo' );
		$has_external = false;
		
		foreach ( $external_resources as $resource ) {
			if ( strpos( $dns_prefetch, $resource ) !== false ) {
				$has_external = true;
				break;
			}
		}
		
		if ( $has_external && empty( $preconnect ) ) {
			$issues[] = __( 'External domains in DNS prefetch without preconnect', 'wpshadow' );
		}
		
		// Check 5: Excessive preload directives
		if ( count( $preload_fonts ) > 4 ) {
			$issues[] = sprintf( __( '%d fonts preloaded (recommend 2-3 for performance)', 'wpshadow' ), count( $preload_fonts ) );
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 45;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 58;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 52;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of preload issues */
				__( 'Perfmatters preload configuration has %d optimization issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => true,
			'kb_link'     => 'https://wpshadow.com/kb/perfmatters-preload-configuration',
		);
	}
}
